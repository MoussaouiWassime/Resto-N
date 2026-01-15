<?php

namespace App\Form;

use App\Entity\Dish;
use App\Entity\Restaurant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
                'attr' => ['maxlength' => 50],
            ])
            ->add('description', TextType::class, [
                'empty_data' => '',
                'attr' => ['maxlength' => 100],
            ])
            ->add('price', IntegerType::class, [
                'attr' => ['min' => 0],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo du plat',
                'mapped' => false,
                'required' => false,
                'data' => null,
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        extensions: [
                            'jpeg',
                            'jpg',
                            'png',
                        ],
                        extensionsMessage: 'Veuillez insérer une image JPG.',
                    ),
                ],
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Entree' => 'E',
                    'Plat' => 'P',
                    'Boisson' => 'B',
                    'Dessert' => 'D',
                ],
            ])
            ->add('restaurant', EntityType::class, [
                'class' => Restaurant::class,
                'choice_label' => 'name',
                'disabled' => true,
                'attr' => ['style' => 'display:none'],
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dish::class,
        ]);
    }
}
