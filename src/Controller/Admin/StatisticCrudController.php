<?php

namespace App\Controller\Admin;

use App\Entity\Statistic;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StatisticCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Statistique')
            ->setEntityLabelInPlural('Statistiques');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Statistic::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('restaurant', 'Restaurant')
                ->formatValue(static function ($value, $entity) {
                    return $value->getName();
                }),
            TextField::new('statistic_type', 'Type')
                ->formatValue(static function ($value) {
                    return 'NB_VISITES' === $value ? 'Nombre de Visites' :
                        ('NB_COMMANDES' === $value ? 'Nombre de Commandes' :
                            "Chiffre d'affaire Quotidien");
                }),
            DateTimeField::new('date', 'Date de Création'),
            NumberField::new('value', 'Valeur'),
        ];
    }
}
