<?php

namespace App\Form;

use App\Entity\Stock;
use App\Enum\StockUnit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['min' => 0],
            ])
            ->add('measureUnit', EnumType::class, [
                'class' => StockUnit::class,
                'label' => 'Unité',
                'choice_label' => function (StockUnit $unit) {
                    return match ($unit) {
                        StockUnit::PIECE => 'Pièce(s)',
                        StockUnit::KG => 'Kilogramme (kg)',
                        StockUnit::GRAM => 'Gramme (g)',
                        StockUnit::LITER => 'Litre (L)',
                        StockUnit::CENTILITER => 'Centilitre (cL)',
                        StockUnit::BOTTLE => 'Bouteille',
                        StockUnit::PORTION => 'Portion',
                    };
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
