<?php

namespace App\Enums;

enum ProductSort: string
{
    case Recommended = 'recommended';
    case Newest = 'newest';
    case PriceLowToHigh = 'price-asc';
    case PriceHighToLow = 'price-desc';
    case NameAToZ = 'name-asc';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => 'Recommended',
            self::Newest => 'Newest first',
            self::PriceLowToHigh => 'Price: low to high',
            self::PriceHighToLow => 'Price: high to low',
            self::NameAToZ => 'Name: A to Z',
        };
    }
}
