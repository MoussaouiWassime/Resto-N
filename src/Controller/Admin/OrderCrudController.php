<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Commandes')
            ->setEntityLabelInSingular('Commande');
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Client')
                ->setFormTypeOptions([
                    'choice_label' => function (User $user) {
                        return $user->getLastName().' '.$user->getFirstName();
                    },
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.lastName');
                    },
                ])
                ->formatValue(function ($user, $entity) {
                    return $user->getLastName().' '.$user->getFirstName();
                }),
            AssociationField::new('restaurant', 'Restaurant')
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name');
                    },
                ])
                ->formatValue(static function ($restaurant, $entity) {
                    return $restaurant->getName();
                }),
            DateTimeField::new('order_date', 'Date de la Commande'),
            ChoiceField::new('order_type', 'Type de Commande')
                ->setChoices([
                    'Livraison' => 'L',
                    'à Emporter' => 'E',
                    'Sur Place' => 'S',
                ])
                ->formatValue(static function ($order_type, $entity) {
                    if ('L' === $order_type) {
                        return 'Livraison';
                    } elseif ('E' === $order_type) {
                        return 'à Emporter';
                    } else {
                        return 'Sur Place';
                    }
                }),
            ChoiceField::new('status', 'Statut de la commande')
                ->setChoices([
                    'Livré' => 'L',
                    'En Cours' => 'E',
                    'à Préparer' => 'P',
                ])
                ->formatValue(static function ($status, $entity) {
                    if ('L' === $status) {
                        return 'Livré';
                    } elseif ('E' === $status) {
                        return 'En Cours';
                    } else {
                        return 'à Préparer';
                    }
                }),
            TextField::new('delivery_address', 'Adresse de Livraison')
                ->formatValue(static function ($address, $entity) {
                    return $address ?: '/';
                }),
            TextField::new('delivery_city', 'Ville de Livraison')
                ->formatValue(static function ($city, $entity) {
                    return $city ?: '/';
                }),
            TextField::new('delivery_postal_code', 'Code Postal de Livraison')
                ->formatValue(static function ($postalCode, $entity) {
                    return $postalCode ?: '/';
                }),
        ];
    }

}
