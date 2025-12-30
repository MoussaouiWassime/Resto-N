<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Restaurant;
use App\Entity\RestaurantTable;
use App\Entity\User;
use Doctrine\DBAL\Types\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reservationDate', DateTimeType::class, [
                'label' => 'Date et heure de réservation',
                'widget' => 'single_text',
            ])
            ->add('numberOfPeople', IntegerType::class, [
                'label' => 'Nombre de personnes',
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
