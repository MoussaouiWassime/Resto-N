<?php

namespace App\Factory;

use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    private \Transliterator $transliterator;

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        $this->transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
    }

    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $firstName = self::faker()->firstName();
        $lastName = self::faker()->lastName();

        $email = $this->normalizeName($firstName)
            .'.'
            .$this->normalizeName($lastName)
            .'@'
            .self::faker()->freeEmailDomain();

        $phone = substr(self::faker()->phoneNumber(), 0, 20);

        return [
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'password' => self::faker()->text(128),
            'phone' => $phone,
        ];
    }

    protected function normalizeName(string $name): string
    {
        return mb_strtolower(
            $this->transliterator->transliterate(
                preg_replace('/ /', '-', $name)
            )
        );
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(UserFixtures $user): void {})
        ;
    }
}
