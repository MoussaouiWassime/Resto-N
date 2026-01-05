<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Restaurant;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderType', ChoiceType::class, [
                'label' => 'Type de commande',
                'choices' => [
                    'Sur place' => 'S',
                    'A emporter' => 'E',
                    'Livraison' => 'L',
                ],
            ])
            ->add('deliveryAddress', TextType::class, [
                'label' => 'Adresse (si livraison)',
                'required' => false,
            ])
            ->add('deliveryPostalCode', TextType::class, [
                'label' => 'Code Postal',
                'required' => false,
            ])
            ->add('deliveryCity', TextType::class, [
                'label' => 'Ville',
                'required' => false,
            ])
            ->add('orderItems', CollectionType::class, [
                'entry_type' => OrderItemType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'label' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
