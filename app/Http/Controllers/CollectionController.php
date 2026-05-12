<?php

namespace App\Http\Controllers;

use App\Actions\GetCollectionWithProducts;
use App\Builders\ProductQueryBuilder;
use App\Enums\ProductSort;
use App\Http\Requests\ShowCollectionRequest;
use Inertia\Response;
use Lunar\Models\Collection;
use Lunar\Models\Url;

class CollectionController extends Controller
{
    public function __construct(private GetCollectionWithProducts $action) {}

    public function show(ShowCollectionRequest $request, string $slug): Response
    {
        // $url = Url::where('slug', $slug)
        //     ->where('element_type', (new Collection)->getMorphClass())
        //     ->firstOrFail();

        // $collection = Collection::where('id', $url->element_id)
        //     ->with(['ancestors', 'media'])
        //     ->firstOrFail();

        // $activeCollectionIds = $collection->ancestors
        //     ->pluck('id')
        //     ->push($collection->id)
        //     ->all();

        // ProductQueryBuilder::forCollection($collection)
        //     ->sort(ProductSort::tryFrom($request->input('sort', '')) ?? ProductSort::Default)
        //     ->inStockOnly($request->boolean('in_stock'))
        //     ->get();

        // $query = $collection->products()
        //     ->where('status', 'published')
        //     ->with([
        //         'variants.prices.currency',
        //         'media',
        //         'brand',
        //     ]);

        // $sort = $request->input('sort', 'default');
        // $query = match ($sort) {
        //     'default', null => $query->reorder(),
        //     'stock_desc' => $query->reorder()
        //         ->join('lunar_product_variants', 'lunar_product_variants.product_id', '=', 'lunar_products.id')
        //         ->orderByDesc('lunar_product_variants.stock'),
        //     'stock_asc' => $query->reorder()
        //         ->join('lunar_product_variants', 'lunar_product_variants.product_id', '=', 'lunar_products.id')
        //         ->orderBy('lunar_product_variants.stock'),
        // };

        // if ($request->boolean('in_stock')) {
        //     $query->whereHas('variants', function ($variantQuery) {
        //         $variantQuery->where(function ($q) {
        //             $q->where('purchasable', 'always')
        //                 ->orWhere('stock', '>', 0);
        //         });
        //     });
        // }

        // $products = $query->get();

        return inertia('Collections/Show', [
            ...$this->action->execute($request, $slug),
            'filters' => [
                'sort' => $request->input('sort'),
                'in_stock' => $request->boolean('in_stock'),
            ],
        ]);
        // return inertia('Collections/Show', [
        //     'products' => $products,
        //     'collection' => $collection,
        //     'activeCollectionIds' => $activeCollectionIds,
        // ]);
    }
}
