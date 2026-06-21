<?php

namespace App\Http\Middleware;

use App\Services\CatalogMenuService;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'catalogMenu' => fn () => app()->make(CatalogMenuService::class)->getMenu(),
            'cartItemsCount' => function () {
                $cart = CartSession::current();

                return $cart ? $cart->lines()->sum('quantity') : 0;
            },
            'cartLines' => function () {
                if (! $cart = CartSession::current()) {
                    return [];
                }

                $lines = $cart->lines()->get();

                $variants = ProductVariant::with(['product.defaultUrl', 'prices.currency'])
                    ->whereIn('id', $lines->pluck('purchasable_id'))
                    ->get()
                    ->keyBy('id');

                return $lines->map(function ($line) use ($variants) {
                    $variant = $variants[$line->purchasable_id] ?? null;

                    return [
                        'id' => $line->id,
                        'quantity' => $line->quantity,
                        'name' => $variant?->product->attribute_data['name']?->getValue()->get('en') ?? '',
                        'price' => $variant?->prices->first()?->price->value,
                        'currency' => $variant?->prices->first()?->currency->code,
                        'slug' => $variant?->product->defaultUrl?->slug,
                    ];
                });
            },
        ];
    }
}
