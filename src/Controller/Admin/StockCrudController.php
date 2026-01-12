<?php

namespace App\Controller\Admin;

use App\Entity\Stock;
use Doctrine\ORM\EntityRepository;
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

    public static function getEntityFqcn(): string
    {
        return Stock::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
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
            AssociationField::new('product', 'Produit')
                ->setFormTypeOptions([
                    'choice_label' => 'product_name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('p')
                            ->orderBy('p.productName');
                    },
                ])
                ->formatValue(static function ($product, $entity) {
                    return $product->getProductName();
                }),
            IntegerField::new('quantity', 'Quantité')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                        'value' => 0,
                    ],
                ]),
        ];
    }

}
