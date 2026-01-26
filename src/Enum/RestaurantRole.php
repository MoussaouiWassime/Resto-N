<?php


namespace App\Enum;

enum RestaurantRole: string
{
    case OWNER = 'P'; // Proprio
    case SERVER = 'S'; // Serveur
}
