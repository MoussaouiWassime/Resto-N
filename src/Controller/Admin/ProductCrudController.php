<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Produits')
            ->setEntityLabelInSingular('Produit');
    }

    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('product_name', 'Nom'),
            AssociationField::new('category', 'Type')
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.name');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return $value->getName();
                }),
        ];
    }

}
