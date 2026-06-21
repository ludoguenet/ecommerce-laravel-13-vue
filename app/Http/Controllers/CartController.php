<?php

namespace App\Http\Controllers;

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
}
