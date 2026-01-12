<?php

namespace App\Controller\Admin;

use App\Entity\Restaurant;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class RestaurantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Restaurant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom du Restaurant'),
            TextEditorField::new('description', 'Description'),
            TextField::new('address', 'Adresse'),
            TextField::new('postal_code', 'Code Postal'),
            TimeField::new('opening_time', "Horaire d'Ouverture")
                ->setFormat('hh:mm'),
            TimeField::new('closing_time', 'Horaire de Fermeture')
                ->setFormat('hh:mm'),
            ImageField::new('image', 'Logo')
                ->setUploadDir('public/uploads/logos'),
            BooleanField::new('dark_kitchen', 'Dark Kitchen ?')->renderAsSwitch(false),
        ];
    }

}
