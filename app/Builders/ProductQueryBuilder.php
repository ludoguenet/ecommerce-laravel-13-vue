<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Collection;

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

    public function get(): EloquentCollection
    {
        return $this->query->get();
    }
}
