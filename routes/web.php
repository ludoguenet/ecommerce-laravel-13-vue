<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Utils\Arr;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

// Route::get('/test/customers', function () {
//     // $customer = Customer::first();
//     // $user = User::firstWhere('email', 'nahaz@mailinator.com');
//     // $customer->users()->attach($user);

//     // $customerGroup = CustomerGroup::find(2);

//     // $customer->customerGroups()->sync($customerGroup);

// })->name('test.customers');

Route::get('collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('add-to-cart/{variant}', [CartController::class, 'store'])->name('add-to-cart');
Route::get('cart', [CartController::class, 'show'])->name('cart.show');
Route::patch('cart/lines', [CartController::class, 'updateLine'])->name('cart.lines.update');
Route::delete('cart/lines/{line}', [CartController::class, 'removeLine'])->name('cart.lines.remove');

Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('checkout/addresses', [CheckoutController::class, 'saveAddresses'])->name('checkout.addresses');
Route::post('checkout/shipping', [CheckoutController::class, 'saveShipping'])->name('checkout.shipping');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::get('test', function () {
    $product = Product::first();
    $taxClass = TaxClass::first();

    $options = ProductOption::with('values')->get();

    $setTuples = $options->mapWithKeys(
        fn ($o) => [$o->handle => $o->values->pluck('id')->toArray()]
    )->toArray();

    $combinations = Arr::permutate($setTuples);

    $product->variants()->whereDoesntHave('values')->delete();

    foreach ($combinations as $combo) {
        $ids = array_values($combo);

        $variant = new ProductVariant([
            'product_id' => $product->id,
            'tax_class_id' => $taxClass->id,
            'sku' => implode('-', $ids),
        ]);
        $variant->save();
        $variant->values()->attach($ids);
    }

    $optionIds = $options->pluck('id')->mapWithKeys(
        fn ($id, $index) => [$id => ['position' => $index + 1]]
    )->toArray();

    $product->productOptions()->sync($optionIds);

    dd($product->variants()->with('values')->get());
});

require __DIR__.'/settings.php';
