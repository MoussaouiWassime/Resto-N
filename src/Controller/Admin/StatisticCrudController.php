<?php

namespace App\Controller\Admin;

use App\Entity\Statistic;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class StatisticCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Statistique')
            ->setEntityLabelInPlural('Statistiques');
    }

    public static function getEntityFqcn(): string
    {
        return Statistic::class;
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
            ChoiceField::new('statistic_type', 'Type')
                ->setChoices([
                    'Nombre de Visites' => 'NB_VISITES',
                    'Nombre de Commandes' => 'NB_COMMANDES',
                    "Chiffre d'affaire Quotidien" => 'CA_JOURNALIER',
                ])
                ->formatValue(static function ($value) {
                    return 'NB_VISITES' === $value ? 'Nombre de Visites' :
                        ('NB_COMMANDES' === $value ? 'Nombre de Commandes' : "Chiffre d'affaire Quotidien");
                }),
            DateTimeField::new('date', 'Date de Création'),
            NumberField::new('value', 'Valeur'),
        ];
    }
}
