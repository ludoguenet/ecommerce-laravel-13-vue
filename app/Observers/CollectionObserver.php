<?php

namespace App\Observers;

use App\Services\CatalogMenuService;
use Lunar\Models\Collection;

class CollectionObserver
{
    public function __construct(protected CatalogMenuService $service) {}

    public function saved(Collection $collection)
    {
        $this->service->clearCache();
    }

    public function deleted(Collection $collection)
    {
        $this->service->clearCache();
    }
}
