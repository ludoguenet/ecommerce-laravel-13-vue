<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends \Lunar\Models\Product
{
    protected $appends = ['small_image_url', 'medium_images_urls'];

    protected function smallImageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->getFirstMediaUrl('images', 'small'));
    }

    protected function mediumImagesUrls(): Attribute
    {
        return Attribute::get(fn () => $this->getMedia('images')->map(fn ($media) => $media->getUrl('medium'))->all());
    }
}
