<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends \Lunar\Models\Product
{
    protected $appends = ['small_image_url'];

    protected function smallImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('images', 'small'));
    }
}
