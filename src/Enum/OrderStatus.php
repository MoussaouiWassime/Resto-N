<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'E';    // En cours
    case PREPARING = 'P';  // En préparation
    case DELIVERING = 'L'; // En livraison / Livré
    case CANCELED = 'A';   // Annulé (si besoin)
}
