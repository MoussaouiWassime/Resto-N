<?php

namespace App\Enum;

enum DishCategory: string
{
    case ENTREE = 'E';
    case PLAT = 'P';
    case DESSERT = 'D';
    case BOISSON = 'B';

    public function getLabel(): string
    {
        return match ($this) {
            self::ENTREE => 'Entrée',
            self::PLAT => 'Plat',
            self::DESSERT => 'Dessert',
            self::BOISSON => 'Boisson',
        };
    }
}
