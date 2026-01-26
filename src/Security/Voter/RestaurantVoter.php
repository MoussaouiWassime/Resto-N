<?php

namespace App\Security\Voter;

use App\Entity\Restaurant;
use App\Enum\RestaurantRole;
use App\Repository\RoleRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class RestaurantVoter extends Voter
{
    public const MANAGE = 'RESTAURANT_MANAGE';
    public const STAFF = 'RESTAURANT_STAFF';

    public function __construct(
        private RoleRepository $roleRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::MANAGE, self::STAFF])
            && $subject instanceof Restaurant;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Restaurant $restaurant */
        $restaurant = $subject;

        $roleEntity = $this->roleRepository->findOneBy([
            'user' => $user,
            'restaurant' => $restaurant,
        ]);

        if (!$roleEntity) {
            return false;
        }

        $userRoleValue = $roleEntity->getRole();

        switch ($attribute) {
            case self::MANAGE:
                if ($userRoleValue === RestaurantRole::OWNER->value) {
                    return true;
                }
                break;

            case self::STAFF:
                $allowedRoles = [
                    RestaurantRole::OWNER->value,
                    RestaurantRole::SERVER->value,
                ];

                if (in_array($userRoleValue, $allowedRoles)) {
                    return true;
                }
                break;
        }

        return false;
    }
}
