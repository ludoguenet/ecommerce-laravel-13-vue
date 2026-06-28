<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    public function store(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = CartSession::current();

        $cart->add($variant, $request->quantity);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Produit ajouté au panier !']);

        return redirect()->back();
    }

    public function updateLine(Request $request): RedirectResponse
    {
        $request->validate([
            'line_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = CartSession::current();

        $cart->updateLine(
            cartLineId: $request->line_id,
            quantity: $request->quantity,
        );

        return redirect()->route('cart.show');
    }

    public function removeLine(int $line): RedirectResponse
    {
        $cart = CartSession::current();

        $cart->remove($line);

        if ($cart->lines->isEmpty()) {
            return redirect()->route('home');
        }

        return redirect()->route('cart.show');
    }

    public function show()
    {
        $cart = CartSession::current();

        abort_if(! $cart || $cart->lines->isEmpty(), 404);

        return Inertia::render('Cart/Show');
    }
}
