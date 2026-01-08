<?php

namespace App\Form;

use App\Entity\Restaurant;
use App\Entity\RestaurantCategory;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class RestaurantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'empty_data' => '',
            ])
            ->add('description', TextType::class, [
                'empty_data' => '',
            ])
            ->add('address', TextType::class, [
                'empty_data' => '',
            ])
            ->add('postalCode', TextType::class, [
                'empty_data' => '',
            ])
            ->add('city', TextType::class, [
                'empty_data' => '',
            ])
            ->add('openingTime', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('closingTime', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('image', FileType::class, [
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
            ->add('darkKitchen', CheckboxType::class, [
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'placeholder' => 'Catégorie ?',
                'class' => RestaurantCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
            ])
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Restaurant::class,
        ]);
    }
}
