<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Lunar\Facades\CartSession;

class StoreAddressesRequest extends FormRequest
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

        $shippingRequired = $cart && $cart->isShippable()
            ? 'required_unless:ship_to_billing,true'
            : 'nullable';

        return [
            'ship_to_billing' => ['sometimes', 'boolean'],

            'billing.country_id' => ['required', 'integer', 'exists:lunar_countries,id'],
            'billing.first_name' => ['required', 'string', 'max:255'],
            'billing.last_name' => ['required', 'string', 'max:255'],
            'billing.company_name' => ['nullable', 'string', 'max:255'],
            'billing.line_one' => ['required', 'string', 'max:255'],
            'billing.line_two' => ['nullable', 'string', 'max:255'],
            'billing.line_three' => ['nullable', 'string', 'max:255'],
            'billing.city' => ['required', 'string', 'max:255'],
            'billing.state' => ['nullable', 'string', 'max:255'],
            'billing.postcode' => ['required', 'string', 'max:20'],
            'billing.contact_email' => ['required', 'email', 'max:255'],
            'billing.contact_phone' => ['nullable', 'string', 'max:50'],

            'shipping.country_id' => [$shippingRequired, 'integer', 'exists:lunar_countries,id'],
            'shipping.first_name' => [$shippingRequired, 'string', 'max:255'],
            'shipping.last_name' => [$shippingRequired, 'string', 'max:255'],
            'shipping.company_name' => ['nullable', 'string', 'max:255'],
            'shipping.line_one' => [$shippingRequired, 'string', 'max:255'],
            'shipping.line_two' => ['nullable', 'string', 'max:255'],
            'shipping.line_three' => ['nullable', 'string', 'max:255'],
            'shipping.city' => [$shippingRequired, 'string', 'max:255'],
            'shipping.state' => ['nullable', 'string', 'max:255'],
            'shipping.postcode' => [$shippingRequired, 'string', 'max:20'],
            'shipping.delivery_instructions' => ['nullable', 'string', 'max:1000'],
            'shipping.contact_email' => ['nullable', 'email', 'max:255'],
            'shipping.contact_phone' => ['nullable', 'string', 'max:50'],
        ];
    }
}
