<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Réservations')
            ->setEntityLabelInSingular('Réservation');
    }

    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Client')
                ->formatValue(static function ($user, $entity) {
                    return $user->getLastName().' '.$user->getFirstName();
                })
                ->setFormTypeOptions([
                    'choice_label' => static function ($user, $entity) {
                        return $user->getLastName().' '.$user->getFirstName();
                    },
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.lastName');
                    },
                ]),
            AssociationField::new('restaurant', 'Restaurant')
                ->formatValue(static function ($restaurant, $entity) {
                    return $restaurant->getName();
                })
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name');
                    },
                ]),
            DateTimeField::new('reservation_date', 'Date de la Réservation'),
            IntegerField::new('number_of_people', 'Nombre de personnes')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                    ],
                ]),
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'Confirmé' => 'C',
                    'En Attente' => 'E',
                    'Annulé' => 'A',
                ])
                ->formatValue(static function ($status, $entity) {
                    if ('C' === $status) {
                        return 'Confirmé';
                    } elseif ('E' === $status) {
                        return 'En Attente';
                    } else {
                        return 'Annulé';
                    }
                }),
            AssociationField::new('restaurantTable', 'Table')
                ->formatValue(static function ($restaurant_table, $entity) {
                    return $restaurant_table->getId();
                })
                ->setFormTypeOptions([
                    'choice_label' => 'id',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('rt')
                            ->orderBy('rt.id');
                    },
                ]),

        ];
    }

}
