<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class OrderCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Commandes')
            ->setEntityLabelInSingular('Commande');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('user', 'Client')
                ->formatValue(function ($user, $entity) {
                    return $user->getLastName().' '.$user->getFirstName();
                }),
            AssociationField::new('restaurant', 'Restaurant')
                ->formatValue(static function ($restaurant, $entity) {
                    return $restaurant->getName();
                }),
            DateTimeField::new('order_date', 'Date de la Commande'),
            TextField::new('order_type', 'Type de Commande')
                ->formatValue(static function ($order_type, $entity) {
                    return 'L' === $order_type ? 'Livraison' :
                        ('E' === $order_type ? 'à Emporter' :
                            'Sur Place');
                }),
            TextField::new('status', 'Statut de la commande')
                ->formatValue(static function ($status, $entity) {
                    return 'L' === $status ? 'Livré' :
                        ('E' === $status ? 'En Cours' :
                            'à Préparer');
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
