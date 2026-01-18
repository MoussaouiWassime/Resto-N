<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Réservations')
            ->setEntityLabelInSingular('Réservation');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('user', 'Client')
                ->formatValue(static function ($user, $entity) {
                    return $user->getLastName().' '.$user->getFirstName();
                }),
            AssociationField::new('restaurant', 'Restaurant')
                ->formatValue(static function ($restaurant, $entity) {
                    return $restaurant->getName();
                }),
            DateTimeField::new('reservation_date', 'Réservé le'),
            IntegerField::new('number_of_people', 'Nombre de personnes'),
            TextField::new('status', 'Statut')
                ->formatValue(static function ($status, $entity) {
                    return 'C' === $status ? 'Confirmé' :
                        ('E' === $status ? 'En Attente' :
                            'Annulé');
                }),
            AssociationField::new('restaurantTable', 'Table')
                ->formatValue(static function ($restaurant_table, $entity) {
                    return $restaurant_table->getId();
                }),
        ];
    }
}
