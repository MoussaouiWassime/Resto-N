<?php

namespace App\Factory;

use App\Entity\RestaurantTable;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<RestaurantTable>
 */
final class RestaurantTableFactory extends PersistentProxyObjectFactory
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
        return RestaurantTable::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'capacity' => self::faker()->numberBetween(2, 8),
            'number' => (string) self::faker()->numberBetween(1, 20),
            'restaurant' => RestaurantFactory::new(['darkKitchen' => false]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(RestaurantTable $restaurantTable): void {})
        ;
    }
}
