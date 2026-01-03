<?php

namespace App\Factory;

use App\Entity\Reservation;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Reservation>
 */
final class ReservationFactory extends PersistentProxyObjectFactory
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
        return Reservation::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'reservationDate' => self::faker()->dateTimeBetween('2025-12-01', '2025-12-31'),
            'restaurant' => RestaurantFactory::new(),
            'status' => self::faker()->randomElement(['E', 'C', 'A']), // E = En attente, C = confirmé, A = annulé
            'user' => UserFactory::new(),
            'numberOfPeople' => self::faker()->numberBetween(1, 10),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(fn(Reservation $reservation) => $reservation->setRestaurantTable(
                RestaurantTableFactory::new([
                    'restaurant' => $reservation->getRestaurant(),
                    'capacity' => self::faker()->numberBetween($reservation->getNumberOfPeople() ?? 2, 10),
                ])->create()->_real()
            ))
        ;
    }
}
