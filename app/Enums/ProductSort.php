<?php

namespace App\Enums;

enum ProductSort: string
{
    case Default = 'default';
    case StockDesc = 'stock_desc';
    case StockAsc = 'stock_asc';
}
