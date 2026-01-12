<?php

namespace App\Controller\Admin;

use App\Entity\RestaurantTable;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class RestaurantTableCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Table de Restaurant')
            ->setEntityLabelInPlural('Tables de Restaurant');
    }

    public static function getEntityFqcn(): string
    {
        return RestaurantTable::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('restaurant', 'Restaurant')
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
            IntegerField::new('number', 'Numéro')
            ->setFormTypeOptions([
                'attr' => [
                    'min' => 1,
                ],
            ]),
            IntegerField::new('capacity', 'Capacité')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 1,
                    ],
                ]),
        ];
    }
}
