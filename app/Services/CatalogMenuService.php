<?php

namespace App\Services;

use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\Facades\Cache;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;

class CatalogMenuService
{
    public function getMenu(string $handle = 'main', int $depth = 3): LaravelCollection
    {
        return Cache::remember("catalog-menu:{$handle}", now()->addHour(), fn () => $this->buildMenu($handle, $depth));
    }

    protected function buildMenu(string $handle, int $depth): LaravelCollection
    {
        $group = CollectionGroup::where('handle', $handle)->first();

        if (! $group) {
            return collect();
        }

        return Collection::where('collection_group_id', $group->id)
            ->whereIsRoot()
            ->defaultOrder()
            ->with($this->buildEagerLoads($depth))
            ->get();
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

    public function clearCache(string $handle = 'main'): void
    {
        Cache::forget("catalog-menu:{$handle}");
    }
}
