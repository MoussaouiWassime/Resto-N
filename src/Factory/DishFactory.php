<?php

namespace App\Factory;

use App\Entity\Dish;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Dish>
 */
final class DishFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Dish::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            // E = Entree, P = Plat, D = Dessert, B = Boisson
            'category' => self::faker()->randomElement(['E', 'P', 'D', 'B']),
            'name' => mb_convert_case(self::faker()->words(3, true), MB_CASE_TITLE),
            'price' => self::faker()->numberBetween(500, 3500),
            'photo' => 'https://placehold.co/100x100',
            'restaurant' => RestaurantFactory::random(),
            'description' => self::faker()->sentence(5),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Dish $dish): void {})
        ;
    }
}
