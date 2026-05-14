<?php

namespace App\Builders;

use App\Enums\ProductSort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Collection;

class ProductQueryBuilder
{
    public function __construct(private Builder|BelongsToMany $query) {}

    public static function forCollection(Collection $collection): static
    {
        $query = $collection->products()
            ->where('status', 'published')
            ->with([
                'variants.prices.currency',
                'media',
                'brand',
                'tags',
            ]);

        return new static($query);
    }

    public function sort(ProductSort $sort): static
    {
        match ($sort) {
            ProductSort::Default => $this->query->reorder(),
            ProductSort::StockDesc => $this->query->reorder()
                ->join('lunar_product_variants', 'lunar_product_variants.product_id', '=', 'lunar_products.id')
                ->orderByDesc('lunar_product_variants.stock'),
            ProductSort::StockAsc => $this->query->reorder()
                ->join('lunar_product_variants', 'lunar_product_variants.product_id', '=', 'lunar_products.id')
                ->orderBy('lunar_product_variants.stock'),
        };

        return $this;
    }

    public function inStockOnly(bool $inStock): static
    {
        if ($inStock) {
            $this->query->whereHas('variants', function ($variantQuery) {
                $variantQuery->where(function ($q) {
                    $q->where('purchasable', 'always')
                        ->orWhere('stock', '>', 0);
                });
            });
        }

        return $this;
    }

    public function get(): EloquentCollection
    {
        return $this->query->get();
    }
}
