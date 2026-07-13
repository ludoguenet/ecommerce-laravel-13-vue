<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreAddressesRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Lunar\Facades\CartSession;
use Lunar\Models\Country;

class CheckoutController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        $cart = CartSession::current();

        if (! $cart || $cart->lines->isEmpty()) {
            return redirect()->route('cart.show');
        }

        return Inertia::render('Checkout/Show', [
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'billing' => $cart->billingAddress,
            'shipping' => $cart->shippingAddress,
            'isShippable' => $cart->isShippable(),
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
}
