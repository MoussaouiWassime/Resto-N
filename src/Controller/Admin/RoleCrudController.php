<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class RoleCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Rôles')
            ->setEntityLabelInSingular('Rôle');
    }

    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('restaurant', 'Restaurants')
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name', 'ASC');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return $value->getName();
                }),
            AssociationField::new('user', 'Utilisateurs')
                ->setFormTypeOptions([
                    'choice_label' => static function ($value, $entity) {
                        return $value->getLastName().' '.$value->getFirstName();
                    },
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.lastName', 'ASC');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return $value->getLastName().' '.$value->getFirstName();
                }),
            ChoiceField::new('role', 'Rôle')
                ->setChoices([
                    'Propriétaire' => 'P',
                    'Serveur' => 'S',
                ])
                ->formatValue(static function ($value) {
                    return 'P' === $value ? 'Propriétaire' : 'Serveur';
                }),
        ];
    }
}
