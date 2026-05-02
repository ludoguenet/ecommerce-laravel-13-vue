<?php

namespace App\Services;

use Illuminate\Support\Collection as LaravelCollection;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;

class CatalogMenuService
{
    public function getMenu(string $handle = 'main', int $depth = 3): LaravelCollection
    {
        $group = CollectionGroup::where('handle', $handle)->first();

        if (! $group) {
            return collect();
        }

        $query = Collection::where('collection_group_id', $group->id)
            ->whereIsRoot()
            ->defaultOrder()
            ->with($this->buildEagerLoads($depth));

        return $query->get();
    }

    protected function buildEagerLoads(int $depth): array
    {
        $eagerLoads = ['defaultUrl'];
        $prefix = '';

        for ($i = 1; $i < $depth; $i++) {
            $prefix .= ($i === 1) ? 'children' : '.children';
            $eagerLoads[$prefix] = fn ($query) => $query->defaultOrder();
            $eagerLoads["{$prefix}.defaultUrl"] = fn ($query) => $query;
        }

        return $eagerLoads;
    }
}
