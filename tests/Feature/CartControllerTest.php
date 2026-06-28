<?php

use App\Http\Middleware\HandleInertiaRequests;
use Lunar\Facades\CartSession;
use Lunar\Models\Cart;

it('updates the quantity of a cart line', function () {
    $cart = Mockery::mock(Cart::class);
    $cart->shouldReceive('updateLine')->once()->with(5, 3);

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->withoutMiddleware(HandleInertiaRequests::class)
        ->patch(route('cart.lines.update'), [
            'line_id' => 5,
            'quantity' => 3,
        ])->assertRedirect(route('cart.show'));
});

it('returns a validation error when quantity is below 1', function () {
    CartSession::shouldReceive('current')->andReturnNull();

    $this->patch(route('cart.lines.update'), [
        'line_id' => 1,
        'quantity' => 0,
    ])->assertSessionHasErrors('quantity');
});

it('requires line_id to be present', function () {
    CartSession::shouldReceive('current')->andReturnNull();

    $this->patch(route('cart.lines.update'), [
        'quantity' => 2,
    ])->assertSessionHasErrors('line_id');
});

it('removes a cart line and redirects to cart', function () {
    $lines = Mockery::mock();
    $lines->shouldReceive('isEmpty')->andReturn(false);

    $cart = Mockery::mock(Cart::class);
    $cart->shouldReceive('remove')->once()->with(5);
    $cart->shouldReceive('getAttribute')->with('lines')->andReturn($lines);

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->withoutMiddleware(HandleInertiaRequests::class)
        ->delete(route('cart.lines.remove', ['line' => 5]))
        ->assertRedirect(route('cart.show'));
});

it('redirects to home when removing the last cart line', function () {
    $lines = Mockery::mock();
    $lines->shouldReceive('isEmpty')->andReturn(true);

    $cart = Mockery::mock(Cart::class);
    $cart->shouldReceive('remove')->once()->with(5);
    $cart->shouldReceive('getAttribute')->with('lines')->andReturn($lines);

    CartSession::shouldReceive('current')->andReturn($cart);

    $this->withoutMiddleware(HandleInertiaRequests::class)
        ->delete(route('cart.lines.remove', ['line' => 5]))
        ->assertRedirect(route('home'));
});
