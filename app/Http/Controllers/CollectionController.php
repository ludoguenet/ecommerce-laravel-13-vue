<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;
use Lunar\Models\Collection;
use Lunar\Models\Url;

class CollectionController extends Controller
{
    public function show(Request $request, string $slug): Response
    {
        $url = Url::where('slug', $slug)
            ->where('element_type', (new Collection)->getMorphClass())
            ->firstOrFail();

        $collection = Collection::where('id', $url->element_id)
            ->with(['ancestors', 'media'])
            ->firstOrFail();

        $activeCollectionIds = $collection->ancestors
            ->pluck('id')
            ->push($collection->id)
            ->all();

        $query = $collection->products()
            ->where('status', 'published')
            ->with([
                'media',
                'brand',
            ]);

        $sort = $request->input('sort', 'default');
        $inStock = $request->boolean('in_stock', false);

        match ($sort) {
             'default', null => $query->reorder(),
             'stock_asc' => $query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->orderBy('lunar_product_variants.stock'),
             'stock_desc' => $query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->orderByDesc('lunar_product_variants.stock'),
        };


        if ($inStock) {
            $query->whereHas('variants',
                fn ($variantQuery) => $variantQuery->where(fn ($q) => $q->where('stock', '>', 0)->orWhere('purchasable', 'always'))
            );
        }


        $products = $query->get();


        return inertia('Collections/Show', [
            'products' => $products,
            'collection' => $collection,
            'activeCollectionIds' => $activeCollectionIds,
            'slug' => $slug,
            'sort' => $sort,
            'in_stock' => $inStock,
        ]);
    }
}
