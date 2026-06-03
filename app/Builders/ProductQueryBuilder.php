<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Pagination\LengthAwarePaginator;
use Lunar\Models\Collection;
use Lunar\Models\ProductVariant;

class ProductQueryBuilder
{
    /**
     * Create a new class instance.
     */
    public function __construct(private Builder|BelongsToMany $query)
    {
        //
    }

    public static function fromCollection(Collection $collection): static
    {
        $query = $collection->products()
            ->where('status', 'published')
            ->with([
                'media',
                'brand',
                'tags',
                'variants.prices.currency',
                'defaultUrl',
            ]);

        return new static($query);
    }

    public function sort(string $sort): static
    {
        match ($sort) {
            'default', null => $this->query->reorder(),
            'stock_asc' => $this->query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->orderBy('lunar_product_variants.stock'),
            'stock_desc' => $this->query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->orderByDesc('lunar_product_variants.stock'),
            'price_asc' => $this->query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->join('lunar_prices', 'lunar_product_variants.id', '=', 'lunar_prices.priceable_id')
                ->where('lunar_prices.priceable_type', (new ProductVariant)->getMorphClass())
                ->orderBy('lunar_prices.price'),
            'price_desc' => $this->query->join('lunar_product_variants', 'lunar_products.id', '=', 'lunar_product_variants.product_id')
                ->join('lunar_prices', 'lunar_product_variants.id', '=', 'lunar_prices.priceable_id')
                ->where('lunar_prices.priceable_type', (new ProductVariant)->getMorphClass())
                ->orderByDesc('lunar_prices.price'),
        };

        return $this;
    }

    public function inStock(bool $inStock): static
    {

        if ($inStock) {
            $this->query->whereHas('variants',
                fn ($variantQuery) => $variantQuery->where(fn ($q) => $q->where('stock', '>', 0)->orWhere('purchasable', 'always'))
            );
        }

        return $this;
    }

    public function pricesBetween(?int $minPrice, ?int $maxPrice): static
    {
        if ($minPrice !== null) {
            $this->query->whereHas('variants.prices', function ($priceQuery) use ($minPrice) {
                $priceQuery->where('price', '>=', $minPrice * 100);
            });
        }

        if ($maxPrice !== null) {
            $this->query->whereHas('variants.prices', function ($priceQuery) use ($maxPrice) {
                $priceQuery->where('price', '<=', $maxPrice * 100);
            });
        }

        return $this;
    }

    public function get(): EloquentCollection
    {
        return $this->query->get();
    }

    public function paginate(?int $perPage = 20): LengthAwarePaginator
    {
        return $this->query->paginate($perPage);
    }
}
