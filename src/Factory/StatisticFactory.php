<?php

namespace App\Factory;

use App\Entity\Statistic;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Statistic>
 */
final class StatisticFactory extends PersistentProxyObjectFactory
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
        return Statistic::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'restaurant' => RestaurantFactory::random(),
            'statisticType' => self::faker()->randomElement(['CA_JOURNALIER', 'NB_COMMANDES', 'NB_VISITES']),
            'value' => self::faker()->randomNumber(5, false),
            'date' => self::faker()
                ->dateTimeBetween('2025-12-01', '2025-12-31')
                ->setTime(0, 0, 0),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Statistic $statistic): void {})
        ;
    }
}
