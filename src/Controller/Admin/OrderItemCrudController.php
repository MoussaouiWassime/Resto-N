<?php

namespace App\Controller\Admin;

use App\Entity\OrderItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
            ->setEntityLabelInSingular('Contenu de la Commande')
            ->setEntityLabelInPlural('Contenu des Commande');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return OrderItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('order', 'Commande')
                ->formatValue(static function ($value, $entity) {
                    return 'N°'.$value->getId();
                }),
            AssociationField::new('dish', 'Plat')
                ->formatValue(static function ($value, $entity) {
                    return $value->getName();
                }),
            IntegerField::new('quantity', 'Quantité'),
        ];
    }
}
