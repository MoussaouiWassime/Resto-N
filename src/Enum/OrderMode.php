<?php

namespace App\Enum;

enum OrderMode: string
{
    case DELIVERY = 'L'; // Livraison
    case ON_SITE = 'S';  // Sur place
    case TAKEAWAY = 'E'; // Emporté
}
