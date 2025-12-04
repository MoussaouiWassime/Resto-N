<?php

namespace App\Factory;

use App\Entity\Restaurant;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Restaurant>
 */
final class RestaurantFactory extends PersistentProxyObjectFactory
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
        return Restaurant::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $isOpen24h = self::faker()->boolean(20);

        return [
            'name' => self::faker()->company(),
            'description' => self::faker()->text(100),
            'address' => self::faker()->streetAddress(),
            'postalCode' => substr(self::faker()->postcode(), 0, 5),
            'city' => self::faker()->city(),
            'openingTime' => $isOpen24h ? null : self::faker()->dateTimeBetween('08:00', '11:00'),
            'closingTime' => $isOpen24h ? null : self::faker()->dateTimeBetween('22:00', '23:59'),
            'darkKitchen' => self::faker()->boolean(20),
            'image' => self::faker()->imageUrl(100, 100),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Restaurant $restaurant): void {})
        ;
    }
}
