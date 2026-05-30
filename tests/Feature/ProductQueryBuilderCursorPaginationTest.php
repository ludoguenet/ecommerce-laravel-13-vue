<?php

use App\Builders\ProductQueryBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Collection;

beforeEach(function () {
    config([
        'database.default' => 'mysql',
        'database.connections.mysql.database' => 'laravel_coffee',
    ]);
    DB::purge('mysql');
});

it('paginates products sorted by price_desc without error', function () {
    $collection = Collection::first();

    $page = ProductQueryBuilder::fromCollection($collection)
        ->sort('price_desc')
        ->paginate();

    expect($page)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($page->count())->toBeGreaterThan(0);
});

it('paginates products sorted by price_asc without error', function () {
    $collection = Collection::first();

    $page = ProductQueryBuilder::fromCollection($collection)
        ->sort('price_asc')
        ->paginate();

    expect($page)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($page->count())->toBeGreaterThan(0);
    expect($page->first()->getRelation('variants')->first()->prices->first()->price)
        ->toBeLessThanOrEqual($page->last()->getRelation('variants')->first()->prices->first()->price);
});

it('paginates a second page for price_desc without error', function () {
    $collection = Collection::first();

    request()->merge(['page' => 2]);

    $page2 = ProductQueryBuilder::fromCollection($collection)
        ->sort('price_desc')
        ->paginate();

    expect($page2)->toBeInstanceOf(LengthAwarePaginator::class);
    expect($page2->currentPage())->toBe(2);
});
