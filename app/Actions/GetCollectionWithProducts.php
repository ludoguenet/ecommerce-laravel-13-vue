<?php

namespace App\Actions;

use App\Builders\ProductQueryBuilder;
use App\Enums\ProductSort;
use App\Http\Requests\ShowCollectionRequest;
use Lunar\Models\Collection;
use Lunar\Models\Url;

class GetCollectionWithProducts
{
    public function execute(ShowCollectionRequest $request, string $slug): array
    {
        $collection = $this->fromSlug($slug);
        $activeCollectionIds = $this->resolveActiveIds($collection);
        $products = ProductQueryBuilder::forCollection($collection)
            ->sort(ProductSort::tryFrom($request->input('sort', '')) ?? ProductSort::Default)
            ->inStockOnly($request->boolean('in_stock'))
            ->get();

        $filters = [
            'sort' => $request->input('sort'),
            'in_stock' => $request->boolean('in_stock'),
        ];

        return compact('products', 'activeCollectionIds', 'collection', 'slug', 'filters');
    }

    private function fromSlug(string $slug): Collection
    {
        $url = Url::where('slug', $slug)
            ->where('element_type', (new Collection)->getMorphClass())
            ->firstOrFail();

        $collection = Collection::where('id', $url->element_id)
            ->with(['ancestors', 'media'])
            ->firstOrFail();

        return $collection;
    }

    private function resolveActiveIds(Collection $collection): array
    {
        $activeCollectionIds = $collection->ancestors
            ->pluck('id')
            ->push($collection->id)
            ->all();

        return $activeCollectionIds;
    }
}
