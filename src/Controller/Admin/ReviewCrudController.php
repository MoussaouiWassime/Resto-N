<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class ReviewCrudController extends AbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Avis')
            ->setEntityLabelInPlural('Avis');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
            ->disable(Action::EDIT);
    }

    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
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
            AssociationField::new('user', 'De')
                ->setFormTypeOptions([
                    'choice_label' => static function ($value, $entity) {
                        return $value->getLastName().' '.$value->getFirstName();
                    },
                    'query_builder' => static function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                            ->orderBy('u.lastName', 'ASC');
                    },
                ])
                ->formatValue(static function ($value, $entity) {
                    return $value->getLastName().' '.$value->getFirstName();
                }),
            DateTimeField::new('created_at', 'Créé le')
                ->setFormat('dd/MM/yyyy à HH:mm:ss'),
            IntegerField::new('rating', 'Note')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => 0,
                        'max' => 5,
                    ],
                ])
                ->formatValue(static function ($value) {
                    return $value.'/5';
                }),
            TextareaField::new('comment', 'Commentaire'),
        ];
    }
}
