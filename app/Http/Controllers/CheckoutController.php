<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreAddressesRequest;
use App\Http\Requests\Checkout\StoreShippingOptionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Country;

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
}
