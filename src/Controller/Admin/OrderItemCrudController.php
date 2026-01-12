<?php

namespace App\Controller\Admin;

use App\Entity\OrderItem;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class OrderItemCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Liste des Plats de la Commande')
            ->setEntityLabelInPlural('Listes des Plats de la Commande');
    }

    public static function getEntityFqcn(): string
    {
        return OrderItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('order', 'Commande')
                ->setFormTypeOptions([
                    'choice_label' => 'id',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('o')
                            ->orderBy('o.id');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return 'N°'.$value->getId();
                }),
            AssociationField::new('dish', 'Plat')
                ->formatValue(static function ($value, $entity) {
                    return $value->getName();
                })
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('d')
                            ->orderBy('d.name');
                    },
                ]),
            IntegerField::new('quantity', 'Quantité')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                    ],
                ]),
        ];
    }
}
