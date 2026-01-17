<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DishCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Plat')
            ->setEntityLabelInPlural('Plats');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Dish::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name', 'Nom du Plat'),
            TextField::new('description', 'Description du Plat'),
            AssociationField::new('restaurant', 'Restaurant associé')
                ->formatValue(static function ($value, $entity) {
                    return $value ? $value->getName() : 'Aucun';
                }),
            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(),
            ImageField::new('photo', 'Image du plat')
                ->setUploadDir('public/images/plats'),
            TextField::new('category', 'Catégorie')
                ->formatValue(static function ($value, $entity) {
                    return 'B' === $value ? 'Boisson' :
                        ('E' === $value ? 'Entrée' :
                            ('P' === $value ? 'Plat Principal' :
                                'Dessert'));
                }),
        ];
    }
}
