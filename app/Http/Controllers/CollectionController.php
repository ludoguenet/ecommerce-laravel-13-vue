<?php

namespace App\Http\Controllers;

use App\Builders\ProductQueryBuilder;
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

        $sort = $request->input('sort', 'default');
        $inStock = $request->boolean('in_stock', false);

        $products = ProductQueryBuilder::fromCollection($collection)
            ->sort($sort)
            ->inStock($inStock)
            ->pricesBetween(
                $request->input('min_price'),
                $request->input('max_price'),
            )
            ->get();

        return inertia('Collections/Show', [
            'products' => $products,
            'collection' => $collection,
            'activeCollectionIds' => $activeCollectionIds,
            'slug' => $slug,
            'sort' => $sort,
            'in_stock' => $inStock,
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
        ]);
    }
}
