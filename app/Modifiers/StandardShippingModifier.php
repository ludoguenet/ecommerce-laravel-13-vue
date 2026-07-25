<?php

namespace App\Modifiers;

use Closure;
use Lunar\Base\ShippingModifier;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Contracts\Cart;
use Lunar\Models\TaxClass;

class StandardShippingModifier extends ShippingModifier
{
    public function handle(Cart $cart, Closure $next)
    {
        $taxClass = TaxClass::getDefault();

        ShippingManifest::clearOptions();

        ShippingManifest::addOptions(collect([
            new ShippingOption(
                name: 'Livraison standard',
                description: '3 à 5 jours ouvrés',
                identifier: 'STANDARD',
                price: new Price(499, $cart->currency, 1),
                taxClass: $taxClass,
            ),
            new ShippingOption(
                name: 'Livraison express',
                description: '1 à 2 jours ouvrés',
                identifier: 'EXPRESS',
                price: new Price(999, $cart->currency, 1),
                taxClass: $taxClass,
            ),
            new ShippingOption(
                name: 'Retrait en magasin',
                description: 'Gratuit, disponible sous 24h',
                identifier: 'PICKUP',
                price: new Price(0, $cart->currency, 1),
                taxClass: $taxClass,
                collect: true,
            ),
        ]));

        return $next($cart);
    }
}
