<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Lunar\Models\Product;
use Lunar\Models\Url;

class ProductController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $url = Url::where('slug', $slug)
            ->where('element_type', (new Product)->getMorphClass())
            ->firstOrFail();

        $product = Product::where('id', $url->element_id)
            ->where('status', 'published')
            ->with([
                'variants.prices.currency',
                'variants.values.option',
                'variants.images',
                'media',
                'brand',
                'productOptions.values',
            ])
            ->firstOrFail();

        return inertia('Products/Show', [
            'product' => $product,
        ]);
    }
}
