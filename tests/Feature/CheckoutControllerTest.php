<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\MessageBag;
use Inertia\Testing\AssertableInertia;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\TaxClass;
use Lunar\Stripe\Facades\Stripe;
use Stripe\PaymentIntent;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutMiddleware(HandleInertiaRequests::class);
});

function mockCart(bool $isEmpty = false, bool $isShippable = true, mixed $billingAddress = null, mixed $shippingAddress = null, mixed $shippingOption = null): Cart
{
    $lines = Mockery::mock();
    $lines->shouldReceive('isEmpty')->andReturn($isEmpty);

    $cart = Mockery::mock(Cart::class);
    $cart->shouldReceive('getAttribute')->with('lines')->andReturn($lines);
    $cart->shouldReceive('isShippable')->andReturn($isShippable);
    $cart->shouldReceive('getAttribute')->with('billingAddress')->andReturn($billingAddress);
    $cart->shouldReceive('getAttribute')->with('shippingAddress')->andReturn($shippingAddress);
    $cart->shouldReceive('getShippingOption')->andReturn($shippingOption);

    return $cart;
}

function mockShippingOption(string $identifier, string $formattedPrice = '4,99 €', bool $collect = false): ShippingOption
{
    $price = Mockery::mock(Price::class);
    $price->shouldReceive('formatted')->andReturn($formattedPrice);

    return new ShippingOption(
        name: 'Livraison standard',
        description: null,
        identifier: $identifier,
        price: $price,
        taxClass: Mockery::mock(TaxClass::class),
        collect: $collect,
    );
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
            ->where('shippingOptions', [])
            ->where('selectedShippingOption', null)
        );
});

it('does not fetch shipping options on show when no shipping address exists', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: true));

    ShippingManifest::shouldReceive('getOptions')->never();

    $this->get(route('checkout.show'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Show')
            ->where('shippingOptions', [])
        );
});

it('includes shipping options and the selected option on show when a shipping address exists', function () {
    $option = mockShippingOption('STANDARD', '4,99 €');

    $cart = mockCart(isShippable: true, shippingAddress: (object) ['shipping_option' => 'STANDARD'], shippingOption: $option);

    ShippingManifest::shouldReceive('getOptions')->once()->with($cart)->andReturn(collect([$option]));

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->get(route('checkout.show'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Show')
            ->has('shippingOptions', 1)
            ->where('shippingOptions.0.identifier', 'STANDARD')
            ->where('shippingOptions.0.price', '4,99 €')
            ->where('selectedShippingOption', 'STANDARD')
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

it('fails validation when shipping_option is missing', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: true));
    ShippingManifest::shouldReceive('getOptions')->andReturn(collect());

    $this->post(route('checkout.shipping'), [])
        ->assertSessionHasErrors(['shipping_option']);
});

it('fails validation when shipping_option is not an available identifier', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: true));
    ShippingManifest::shouldReceive('getOptions')->andReturn(collect([mockShippingOption('STANDARD')]));

    $this->post(route('checkout.shipping'), ['shipping_option' => 'UNKNOWN'])
        ->assertSessionHasErrors(['shipping_option']);
});

it('redirects to cart from saveShipping when cart is empty', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isEmpty: true, isShippable: true));
    ShippingManifest::shouldReceive('getOptions')->andReturn(collect([mockShippingOption('STANDARD')]));

    $this->post(route('checkout.shipping'), ['shipping_option' => 'STANDARD'])
        ->assertRedirect(route('cart.show'));
});

it('sets the shipping option on the cart on success', function () {
    $option = mockShippingOption('STANDARD');

    $cart = mockCart(isShippable: true);
    $cart->shouldReceive('setShippingOption')->once()->with($option);

    CartSession::shouldReceive('current')->andReturn($cart);
    ShippingManifest::shouldReceive('getOptions')->andReturn(collect([$option]));
    ShippingManifest::shouldReceive('getOption')->once()->with($cart, 'STANDARD')->andReturn($option);

    $this->post(route('checkout.shipping'), ['shipping_option' => 'STANDARD'])
        ->assertRedirect(route('checkout.show'));
});

it('returns an error when the resolved shipping option is no longer available', function () {
    $cart = mockCart(isShippable: true);
    $cart->shouldReceive('setShippingOption')->never();

    CartSession::shouldReceive('current')->andReturn($cart);
    ShippingManifest::shouldReceive('getOptions')->andReturn(collect([mockShippingOption('STANDARD')]));
    ShippingManifest::shouldReceive('getOption')->once()->with($cart, 'STANDARD')->andReturn(null);

    $this->post(route('checkout.shipping'), ['shipping_option' => 'STANDARD'])
        ->assertSessionHasErrors(['shipping_option']);
});

function mockAddress(array $attributes = []): object
{
    return new class($attributes)
    {
        public function __construct(array $attributes)
        {
            foreach ($attributes as $key => $value) {
                $this->$key = $value;
            }
        }

        public function loadMissing(...$args): static
        {
            return $this;
        }
    };
}

it('redirects to cart when cart is empty on payment', function () {
    CartSession::shouldReceive('current')->andReturnNull();

    $this->get(route('checkout.payment'))->assertRedirect(route('cart.show'));
});

it('redirects to cart when cart has no lines on payment', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isEmpty: true));

    $this->get(route('checkout.payment'))->assertRedirect(route('cart.show'));
});

it('redirects to checkout show when billing address is missing on payment', function () {
    CartSession::shouldReceive('current')->andReturn(mockCart(isShippable: false));

    $this->get(route('checkout.payment'))
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['checkout']);
});

it('redirects to checkout show when a shippable cart has no shipping option on payment', function () {
    $cart = mockCart(isShippable: true, billingAddress: mockAddress());

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->get(route('checkout.payment'))
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['checkout']);
});

it('renders the payment page with a client secret when the cart is ready', function () {
    $billing = mockAddress(['first_name' => 'Jane']);
    $cart = mockCart(isShippable: false, billingAddress: $billing);

    CartSession::shouldReceive('current')->andReturn($cart);
    CartSession::shouldReceive('createOrder')->once()->with(false);
    Stripe::shouldReceive('fetchOrCreateIntent')->once()->with($cart)
        ->andReturn(PaymentIntent::constructFrom(['client_secret' => 'secret_123']));

    $this->get(route('checkout.payment'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Payment')
            ->where('clientSecret', 'secret_123')
        );
});

it('redirects to checkout show with an error when the cart fails order validation on payment', function () {
    $billing = mockAddress(['first_name' => 'Jane']);
    $cart = mockCart(isShippable: false, billingAddress: $billing);

    CartSession::shouldReceive('current')->andReturn($cart);
    CartSession::shouldReceive('createOrder')->once()->with(false)
        ->andThrow(new CartException(new MessageBag(['Le panier ne peut pas être transformé en commande.'])));

    $this->get(route('checkout.payment'))
        ->assertRedirect(route('checkout.show'))
        ->assertSessionHasErrors(['checkout' => 'Le panier ne peut pas être transformé en commande.']);
});

it('redirects to cart from callback when cart is empty', function () {
    CartSession::shouldReceive('current')->andReturnNull();

    $this->get(route('checkout.callback', ['payment_intent' => 'pi_123']))
        ->assertRedirect(route('cart.show'));
});

it('forgets the cart and redirects to the confirmation page when payment succeeds', function () {
    $cart = mockCart();

    CartSession::shouldReceive('current')->andReturn($cart);
    CartSession::shouldReceive('forget')->once();

    $driver = Mockery::mock();
    $driver->shouldReceive('cart')->once()->with($cart)->andReturnSelf();
    $driver->shouldReceive('withData')->once()->with(['payment_intent' => 'pi_123'])->andReturnSelf();
    $driver->shouldReceive('authorize')->once()->andReturn(new PaymentAuthorize(success: true, orderId: 42));

    Payments::shouldReceive('driver')->once()->with('card')->andReturn($driver);

    $this->get(route('checkout.callback', ['payment_intent' => 'pi_123']))
        ->assertRedirect(route('checkout.complete', ['order' => 42]));
});

it('redirects back to the payment page with an error when authorization fails', function () {
    $cart = mockCart();

    CartSession::shouldReceive('current')->andReturn($cart);
    CartSession::shouldReceive('forget')->never();

    $driver = Mockery::mock();
    $driver->shouldReceive('cart')->once()->with($cart)->andReturnSelf();
    $driver->shouldReceive('withData')->once()->andReturnSelf();
    $driver->shouldReceive('authorize')->once()->andReturn(new PaymentAuthorize(success: false, message: 'Card declined.'));

    Payments::shouldReceive('driver')->once()->with('card')->andReturn($driver);

    $this->get(route('checkout.callback', ['payment_intent' => 'pi_123']))
        ->assertRedirect(route('checkout.payment'))
        ->assertSessionHasErrors(['payment' => 'Card declined.']);
});

it('aborts with a 404 when the order is still a draft', function () {
    Currency::factory()->create(['default' => true]);

    $order = Order::factory()->create(['placed_at' => null]);

    $this->get(route('checkout.complete', ['order' => $order]))->assertNotFound();
});

it('renders the confirmation page for a placed order', function () {
    Currency::factory()->create(['default' => true]);

    $order = Order::factory()->create(['placed_at' => now()]);

    $this->get(route('checkout.complete', ['order' => $order]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Checkout/Complete')
            ->where('order.reference', $order->reference)
        );
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
