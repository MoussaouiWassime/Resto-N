<?php

namespace App\Controller\Admin;

use App\Entity\Dish;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DishCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Plat')
            ->setEntityLabelInPlural('Plats');
    }

    public static function getEntityFqcn(): string
    {
        return Dish::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du Plat'),
            TextEditorField::new('description', 'Description du Plat'),
            AssociationField::new('restaurant', 'Restaurant associé')
                ->setFormTypeOptions([
                    'choice_label' => 'name',
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                            ->orderBy('r.name', 'ASC');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return $value ? $value->getName() : 'Aucun';
                }),
            MoneyField::new('price', 'Prix')
                ->setCurrency('EUR')
                ->setStoredAsCents(),
            ImageField::new('photo', 'Image du plat')
                ->setUploadDir('public/uploads/plats'),
            ChoiceField::new('category', 'Catégorie')
                ->formatValue(static function ($value, $entity) {
                    if ('B' == $value) {
                        return 'Boisson';
                    } elseif ('E' === $value) {
                        return 'Entrée';
                    } elseif ('P' === $value) {
                        return 'Plat Principal';
                    } else {
                        return 'Dessert';
                    }
                })
                ->setChoices([
                    'Entrée' => 'E',
                    'Plat' => 'P',
                    'Dessert' => 'D',
                    'Boisson' => 'B',
                ]),
        ];
    }
}
