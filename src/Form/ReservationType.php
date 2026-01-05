<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\RestaurantTable;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $reservation = $builder->getData();
        $restaurant = $reservation ? $reservation->getRestaurant() : null;

        $builder
            ->add('reservationDate', DateTimeType::class, [
                'label' => 'Date et heure de réservation',
                'widget' => 'single_text',
            ])
            ->add('numberOfPeople', IntegerType::class, [
                'label' => 'Nombre de personnes',
                'attr' => [
                    'min' => 1,
                    'max' => 10,
                ],
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Confirmée' => 'C',
                    'Annulée' => 'A',
                    'Terminée' => 'T',
                ],
                'empty_data' => 'C',
            ])
            ->add('restaurantTable', EntityType::class, [
                'class' => RestaurantTable::class,
                'label' => 'Table attribuée',
                'required' => false,
                'placeholder' => 'Choisir une table',
                'choice_label' => function (RestaurantTable $t) {
                    return 'Table n°'.$t->getNumber().' ('.$t->getCapacity().' pers.)';
                },
                'query_builder' => function (EntityRepository $er) use ($restaurant) {
                    if (!$restaurant) {
                        return $er->createQueryBuilder('t');
                    }

                    return $er->createQueryBuilder('t')
                        ->where('t.restaurant = :restaurant')
                        ->setParameter('restaurant', $restaurant)
                        ->orderBy('t.number', 'ASC');
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
