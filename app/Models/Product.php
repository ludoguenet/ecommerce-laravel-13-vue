<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends \Lunar\Models\Product
{
    protected function smallImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('images', 'small'));
    }

    protected function mediumImagesUrls(): Attribute
    {
        return Attribute::get(fn () => $this->getMedia('images')->map(fn ($media) => $media->getUrl('medium'))->all());
    }
}
