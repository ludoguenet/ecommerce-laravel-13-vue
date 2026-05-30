<?php

use Database\Seeders\CoffeeProductSeeder;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Brand;
use Lunar\Models\ProductVariant;
use Lunar\Models\Tag;

beforeEach(function () {
    config([
        'database.default' => 'mysql',
        'database.connections.mysql.database' => 'laravel_coffee',
    ]);
    DB::purge('mysql');
    $this->seed(CoffeeProductSeeder::class);
});

it('seeds all expected coffee SKUs', function () {
    $skus = [
        'ARB-GUA-001', 'ARB-BRA-001', 'ARB-PAN-001', 'ARB-PER-001', 'ARB-MEX-001',
        'ARB-HON-001', 'ARB-RWA-001', 'ARB-TAN-001', 'ARB-YEM-001', 'ARB-ETH-002',
        'ARB-EQU-001', 'ARB-JAM-001', 'ROB-VIE-001', 'ROB-OUG-001', 'ROB-IND-001',
        'ROB-CAM-001', 'BLD-ESP-001', 'BLD-MOR-001', 'BLD-DRK-001', 'BLD-BRK-001',
        'BLD-BAR-001', 'BLD-ROM-001', 'GRN-PRM-001', 'GRN-BIO-001', 'MLD-ESP-001',
        'MLD-ETH-001', 'MLD-FLT-001', 'MLD-COL-001', 'MLD-PST-001', 'MLD-KEN-001',
        'CAP-ARB-001', 'CAP-COL-001', 'DOS-ESP-001', 'DOS-KEN-001',
        'CAF-MLD-001', 'CAF-DEC-001',
    ];

    foreach ($skus as $sku) {
        expect(ProductVariant::where('sku', $sku)->exists())->toBeTrue("SKU {$sku} not found");
    }
});

it('seeds all products as published', function () {
    foreach (['ARB-GUA-001', 'BLD-ESP-001', 'CAP-ARB-001', 'MLD-PST-001'] as $sku) {
        $variant = ProductVariant::where('sku', $sku)->first();
        expect($variant->product->status)->toBe('published');
    }
});

it('creates the 3 expected brands', function () {
    expect(Brand::where('name', 'Arborealis Roasters')->exists())->toBeTrue();
    expect(Brand::where('name', 'Café du Monde')->exists())->toBeTrue();
    expect(Brand::where('name', 'Black Peak Roasters')->exists())->toBeTrue();
});

it('creates the expected tags', function () {
    foreach (['ARABICA', 'BLEND', 'ROBUSTA', 'MOULU', 'CAPSULE', 'FLORAL', 'FRUITÉ', 'ÉPICÉ'] as $tag) {
        expect(Tag::where('value', $tag)->exists())->toBeTrue("Tag {$tag} not found");
    }
});

it('assigns each seeded product to a collection', function () {
    foreach (['ARB-GUA-001', 'ROB-VIE-001', 'BLD-ESP-001', 'MLD-ESP-001', 'CAP-ARB-001', 'DOS-ESP-001'] as $sku) {
        $variant = ProductVariant::where('sku', $sku)->first();
        expect($variant->product->collections()->count())->toBeGreaterThanOrEqual(1, "Product {$sku} has no collection");
    }
});

it('creates a price for each seeded variant', function () {
    foreach (['ARB-GUA-001', 'ROB-VIE-001', 'BLD-ESP-001', 'MLD-ESP-001', 'CAP-ARB-001'] as $sku) {
        $variant = ProductVariant::where('sku', $sku)->first();
        expect($variant->prices()->count())->toBeGreaterThanOrEqual(1, "Variant {$sku} has no price");
    }
});

it('is idempotent and does not create duplicates when run twice', function () {
    $countBefore = ProductVariant::count();
    $this->seed(CoffeeProductSeeder::class);
    expect(ProductVariant::count())->toBe($countBefore);
});
