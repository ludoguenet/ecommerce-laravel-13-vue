<?php

namespace App\Http\Controllers;

use Inertia\Response;
use Lunar\Models\Collection;
use Lunar\Models\Url;

class CollectionController extends Controller
{
    public function show(string $slug): Response
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

        return inertia('Collections/Show', [
            'collection' => $collection,
            'activeCollectionIds' => $activeCollectionIds,
        ]);
    }
}
