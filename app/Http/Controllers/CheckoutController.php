<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreAddressesRequest;
use App\Http\Requests\Checkout\StoreShippingOptionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Country;
use Lunar\Models\Order;
use Lunar\Stripe\Facades\Stripe;

/**
 * Episode outline: Stripe payment + order creation.
 *
 * Suggested recording order, one file per step:
 *
 * 1. composer.json / .env.example — composer require lunarphp/stripe, publish
 *    config/lunar/stripe.php, add STRIPE_SECRET / STRIPE_PUBLIC / LUNAR_STRIPE_WEBHOOK_SECRET
 *    and VITE_STRIPE_PUBLIC (Vite only exposes VITE_-prefixed vars to the frontend).
 * 2. config/services.php — expose the Stripe keys via the services array.
 * 3. config/lunar/payments.php — register the "card" type with the "stripe" driver.
 * 4. app/Providers/AppServiceProvider.php (boot()) — Payments::extend('stripe', ...)
 *    wires the driver into Lunar's payment manager.
 * 5. routes/web.php — three new routes: checkout/payment (GET, show the form),
 *    checkout/callback (GET, Stripe redirects here), checkout/complete/{order}.
 * 6. CheckoutController::payment() below — the key teaching moment:
 *      a. guard clauses (empty cart, missing billing/shipping option)
 *      b. CartSession::createOrder(false) creates the DRAFT order up front
 *         (see the "Creating the Order" section of Lunar's checkout guide) —
 *         explain this is what actually reserves stock/records intent, not
 *         the payment step. Idempotent: revisiting this page won't duplicate.
 *      c. Stripe::fetchOrCreateIntent($cart) creates/reuses the PaymentIntent.
 * 7. resources/js/pages/Checkout/Payment.vue — mount the Stripe Payment Element,
 *    call stripe.confirmPayment(). Two gotchas worth calling out on camera:
 *      - return_url must be an ABSOLUTE url (wrap the Wayfinder path with
 *        `new URL(path, window.location.origin)`), Stripe rejects relative ones.
 *      - fields.billingDetails: 'never' requires every billing_details field
 *        (phone, email, address...) to be passed explicitly, even as '' — not
 *        undefined, or confirmPayment() throws an IntegrationError.
 * 8. CheckoutController::callback() below — Payments::driver('card')->authorize()
 *    finalizes the draft order created in step 6, then CartSession::forget().
 * 9. CheckoutController::complete() below — loads the order + addresses,
 *    formats everything into plain arrays for Inertia (never leak Eloquent
 *    models straight to the frontend).
 * 10. resources/js/pages/Checkout/Complete.vue — the confirmation page design.
 * 11. tests/Feature/CheckoutControllerTest.php — Mockery for CartSession/
 *     Payments, Stripe::fake()-style doubles via PaymentIntent::constructFrom(),
 *     and a reminder to run `npm run build` before testing new Inertia pages
 *     (the manifest must know about them or full-page test requests 500).
 */
class CheckoutController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $shippingOptions = $cart->shippingAddress
            ? ShippingManifest::getOptions($cart)->map(fn ($option) => [
                'identifier' => $option->identifier,
                'name' => $option->name,
                'description' => $option->description,
                'price' => $option->price->formatted(),
                'collect' => $option->collect,
            ])
            : collect();

        return Inertia::render('Checkout/Show', [
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'billing' => $cart->billingAddress,
            'shipping' => $cart->shippingAddress,
            'isShippable' => $cart->isShippable(),
            'shippingOptions' => $shippingOptions,
            'selectedShippingOption' => $cart->getShippingOption()?->identifier,
        ]);
    }

    public function saveAddresses(StoreAddressesRequest $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $cart->setBillingAddress($request->validated('billing'));

        $cart->setShippingAddress(
            $request->boolean('ship_to_billing')
                ? $request->validated('billing')
                : $request->validated('shipping')
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Adresses enregistrées !']);

        return redirect()->route('checkout.show');
    }

    public function saveShipping(StoreShippingOptionRequest $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        $shippingOption = ShippingManifest::getOption($cart, $request->validated('shipping_option'));

        if (! $shippingOption) {
            return redirect()->back()
                ->withErrors(['shipping_option' => "Le mode de livraison sélectionné n'est plus disponible."]);
        }

        $cart->setShippingOption($shippingOption);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Mode de livraison enregistré !']);

        return redirect()->route('checkout.show');
    }

    public function payment(): Response|RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        if (! $cart->billingAddress || ($cart->isShippable() && ! $cart->getShippingOption())) {
            return redirect()->route('checkout.show')
                ->withErrors(['checkout' => 'Merci de renseigner vos informations de livraison avant de payer.']);
        }

        try {
            CartSession::createOrder(false);
        } catch (CartException $e) {
            return redirect()->route('checkout.show')
                ->withErrors(['checkout' => $e->getMessage()]);
        }

        $intent = Stripe::fetchOrCreateIntent($cart);

        return Inertia::render('Checkout/Payment', [
            'clientSecret' => $intent->client_secret,
            'billing' => $cart->billingAddress->loadMissing('country'),
        ]);
    }

    public function callback(Request $request): RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart) {
            return redirect()->route('cart.show');
        }

        $payment = Payments::driver('card')
            ->cart($cart)
            ->withData(['payment_intent' => $request->query('payment_intent')])
            ->authorize();

        if (! $payment->success) {
            return redirect()->route('checkout.payment')
                ->withErrors(['payment' => $payment->message ?? "Le paiement n'a pas pu être traité."]);
        }

        CartSession::forget();

        return redirect()->route('checkout.complete', ['order' => $payment->orderId]);
    }

    public function complete(Order $order): Response
    {
        if ($order->isDraft()) {
            abort(404);
        }

        $order->load(['productLines', 'shippingLines', 'billingAddress.country', 'shippingAddress.country']);

        $formatAddress = fn ($address) => $address ? [
            'name' => trim("{$address->first_name} {$address->last_name}"),
            'lineOne' => $address->line_one,
            'lineTwo' => $address->line_two,
            'city' => $address->city,
            'postcode' => $address->postcode,
            'country' => $address->country?->name,
        ] : null;

        return Inertia::render('Checkout/Complete', [
            'order' => [
                'reference' => $order->reference,
                'placedAt' => $order->placed_at?->locale('fr')->translatedFormat('d F Y à H:i'),
                'contactEmail' => $order->billingAddress?->contact_email,
                'lines' => $order->productLines->map(fn ($line) => [
                    'description' => $line->description,
                    'quantity' => $line->quantity,
                    'total' => $line->total->formatted(),
                ]),
                'shippingLines' => $order->shippingLines->map(fn ($line) => [
                    'description' => $line->description,
                    'total' => $line->total->formatted(),
                ]),
                'subTotal' => $order->sub_total->formatted(),
                'discountTotal' => $order->discount_total->value > 0 ? $order->discount_total->formatted() : null,
                'shippingTotal' => $order->shipping_total->value > 0 ? $order->shipping_total->formatted() : null,
                'taxTotal' => $order->tax_total->formatted(),
                'total' => $order->total->formatted(),
                'billingAddress' => $formatAddress($order->billingAddress),
                'shippingAddress' => $formatAddress($order->shippingAddress),
            ],
        ]);
    }
}
