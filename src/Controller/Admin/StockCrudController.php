<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class StockCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Stocks')
            ->setEntityLabelInSingular('Stock');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Stock::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('restaurant', 'Restaurant')
                ->formatValue(static function ($restaurant, $entity) {
                    return $restaurant->getName();
                }),
            AssociationField::new('product', 'Produit')
                ->formatValue(static function ($product, $entity) {
                    return $product->getProductName();
                }),
            IntegerField::new('quantity', 'Quantité'),
        ];
    }
}
