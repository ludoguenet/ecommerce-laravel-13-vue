<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;

uses(RefreshDatabase::class);

it('returns the standard shipping options for a cart via the registered shipping modifier', function () {
    $currency = Currency::factory()->create();
    $channel = Channel::factory()->create();

    TaxClass::factory()->create(['default' => true]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
    ]);

    $options = ShippingManifest::getOptions($cart);

    expect($options->pluck('identifier')->all())->toEqual(['STANDARD', 'EXPRESS', 'PICKUP']);
    expect($options->firstWhere('identifier', 'PICKUP')->collect)->toBeTrue();
});
