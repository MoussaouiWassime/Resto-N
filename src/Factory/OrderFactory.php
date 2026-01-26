<?php

namespace App\Factory;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Enum\OrderMode;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Order>
 */
final class OrderFactory extends PersistentProxyObjectFactory
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
        return Order::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $type = self::faker()->randomElement(OrderMode::cases());
        $address = (OrderMode::DELIVERY === $type) ? self::faker()->streetAddress() : null;
        $city = (OrderMode::DELIVERY === $type) ? self::faker()->city() : null;
        $postalCode = (OrderMode::DELIVERY === $type) ? substr(self::faker()->postcode(), 0, 5) : null;

        return [
            'orderDate' => self::faker()->dateTimeBetween('2025-12-01', '2025-12-31'),
            'orderType' => $type,
            'restaurant' => RestaurantFactory::random(),
            'status' => self::faker()->randomElement(OrderStatus::cases()),
            'user' => UserFactory::random(),
            'deliveryAddress' => $address,
            'deliveryCity' => $city,
            'deliveryPostalCode' => $postalCode,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Order $order): void {})
        ;
    }
}
