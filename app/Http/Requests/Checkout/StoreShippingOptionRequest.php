<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Lunar\Facades\CartSession;
use Lunar\Facades\ShippingManifest;

class StoreShippingOptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $cart = CartSession::current();

        $identifiers = $cart
            ? ShippingManifest::getOptions($cart)->pluck('identifier')
            : collect();

        return [
            'shipping_option' => ['required', 'string', Rule::in($identifiers)],
        ];
    }
}
