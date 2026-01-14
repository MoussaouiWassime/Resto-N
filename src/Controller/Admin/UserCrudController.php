<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Utilisateurs')
            ->setEntityLabelInSingular('Utilisateur');
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstName', 'Prénom'),
            TextField::new('lastName', 'Nom de famille'),
            ArrayField::new('roles', 'Rôle')
                ->formatValue(function ($roles, $entity) {
                    if (in_array('ROLE_ADMIN', $roles)) {
                        return '<i class="fa-solid fa-user-gear"></i>';
                    } elseif (in_array('ROLE_USER', $roles)) {
                        return '<i class="fa-solid fa-user"></i>';
                    } else {
                        return '';
                    }
                }),
            EmailField::new('email', 'Email'),
            TextField::new('phone', 'Téléphone'),
            TextField::new('password', 'Mot de Passe')
                ->onlyOnForms()
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'required' => false,
                    'mapped' => false,
                    'empty_data' => '',
                    'attr' => [
                        'autocomplete' => false,
                    ],
                ]),
        ];
    }

    public function setUserPassword(mixed $password, object $entityInstance): void
    {
        if (!empty($password)) {
            $entityInstance->setPassword($this->hasher->hashPassword($entityInstance, $password));
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $context = $this->getContext();
        $name = $context->getEntity()
            ->getName();
        $request = $context->getRequest()
            ->request
            ->all();
        $password = $request[$name]['password'];

        $this->setUserPassword($password, $entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $context = $this->getContext();
        $name = $context->getEntity()
            ->getName();
        $request = $context->getRequest()
            ->request
            ->all();
        $password = $request[$name]['password'];

        $this->setUserPassword($password, $entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }
}
