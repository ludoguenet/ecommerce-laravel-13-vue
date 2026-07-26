<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;
use Lunar\Models\Country;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
});

function mockCart(bool $isEmpty = false, bool $isShippable = true, mixed $billingAddress = null, mixed $shippingAddress = null): Cart
{
    $lines = Mockery::mock();
    $lines->shouldReceive('isEmpty')->andReturn($isEmpty);

    $cart = Mockery::mock(Cart::class);
    $cart->shouldReceive('getAttribute')->with('lines')->andReturn($lines);
    $cart->shouldReceive('isShippable')->andReturn($isShippable);
    $cart->shouldReceive('getAttribute')->with('billingAddress')->andReturn($billingAddress);
    $cart->shouldReceive('getAttribute')->with('shippingAddress')->andReturn($shippingAddress);

    return $cart;
}

it('redirects to cart when cart is empty on show', function () {
    CartSession::shouldReceive('current')->andReturnNull();

    $this->get(route('checkout.show'))->assertRedirect(route('cart.show'));
});

it('redirects to cart when cart has no lines on show', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isEmpty: true));

    $this->get(route('checkout.show'))->assertRedirect(route('cart.show'));
});

it('renders the checkout page with countries and shippability when cart has items', function () {
    Country::factory()->count(3)->create();

    CartSession::shouldReceive('current')->andReturn(mockCart(isEmpty: false, isShippable: true));

    $this->get(route('checkout.show'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Show')
            ->has('countries', 3)
            ->where('billing', null)
            ->where('shipping', null)
            ->where('isShippable', true)
        );
});

it('fails validation when required billing fields are missing', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart());

    $this->post(route('checkout.addresses'), [])
        ->assertSessionHasErrors([
            'billing.country_id',
            'billing.first_name',
            'billing.last_name',
            'billing.line_one',
            'billing.city',
            'billing.postcode',
            'billing.contact_email',
        ]);
});

it('does not require shipping fields when ship_to_billing is checked', function () {
    $country = Country::factory()->create();

    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: true));

    $this->post(route('checkout.addresses'), [
        'ship_to_billing' => true,
        'billing' => validBillingPayload($country->id),
    ])->assertSessionHasNoErrors();
});

it('requires shipping fields when ship_to_billing is unchecked and cart is shippable', function () {
    $country = Country::factory()->create();

    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: true));

    $this->post(route('checkout.addresses'), [
        'billing' => validBillingPayload($country->id),
    ])->assertSessionHasErrors([
        'shipping.country_id',
        'shipping.first_name',
        'shipping.last_name',
        'shipping.line_one',
        'shipping.city',
        'shipping.postcode',
    ]);
});

it('does not require shipping fields when cart is not shippable', function () {
    $country = Country::factory()->create();

    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: false));

    $this->post(route('checkout.addresses'), [
        'billing' => validBillingPayload($country->id),
    ])->assertSessionHasNoErrors();
});

it('sets both billing and shipping addresses on the cart on success', function () {
    $country = Country::factory()->create();

    $billing = validBillingPayload($country->id);
    $shipping = validShippingPayload($country->id);

    $cart = mockCart(isShippable: true);
    $cart->shouldReceive('setBillingAddress')->once()->with(Mockery::on(
        fn ($value) => $value['first_name'] === $billing['first_name']
    ));
    $cart->shouldReceive('setShippingAddress')->once()->with(Mockery::on(
        fn ($value) => $value['first_name'] === $shipping['first_name']
    ));

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->post(route('checkout.addresses'), [
        'billing' => $billing,
        'shipping' => $shipping,
    ])->assertRedirect(route('checkout.show'));
});

it('copies billing into shipping when ship_to_billing is true', function () {
    $country = Country::factory()->create();

    $billing = validBillingPayload($country->id);
    $shipping = validShippingPayload($country->id);

    $cart = mockCart(isShippable: true);
    $cart->shouldReceive('setBillingAddress')->once()->with(Mockery::on(
        fn ($value) => $value['first_name'] === $billing['first_name']
    ));
    $cart->shouldReceive('setShippingAddress')->once()->with(Mockery::on(
        fn ($value) => $value['first_name'] === $billing['first_name']
    ));

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->post(route('checkout.addresses'), [
        'ship_to_billing' => true,
        'billing' => $billing,
        'shipping' => $shipping,
    ])->assertRedirect(route('checkout.show'));
});

it('redirects to cart from saveAddresses when cart is empty', function () {
    $country = Country::factory()->create();

    CartSession::shouldReceive('current')->andReturn(mockCart(isEmpty: true, isShippable: false));

    $this->post(route('checkout.addresses'), [
        'billing' => validBillingPayload($country->id),
    ])->assertRedirect(route('cart.show'));
});

function validBillingPayload(int $countryId): array
{
    return [
        'country_id' => $countryId,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'line_one' => '1 rue de la Paix',
        'city' => 'Paris',
        'postcode' => '75000',
        'contact_email' => 'jane@example.com',
    ];
}

function validShippingPayload(int $countryId): array
{
    return [
        'country_id' => $countryId,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'line_one' => '2 rue de la Paix',
        'city' => 'Paris',
        'postcode' => '75000',
    ];
}
