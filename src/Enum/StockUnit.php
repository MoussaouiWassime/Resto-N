<?php

namespace App\Enum;

enum StockUnit: string
{
    case PIECE = 'pcs';
    case KG = 'kg';
    case GRAM = 'g';
    case LITER = 'L';
    case CENTILITER = 'cL';
    case BOTTLE = 'btl';
    case PORTION = 'part';
}
