<?php

namespace App\Form;

use App\Entity\LigneOrdonnance;
use App\Entity\Medicament;
use App\Entity\Ordonnance;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LigneOrdonnanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite')
            ->add('posologie')
            ->add('ordonnance', EntityType::class, [
                'class' => Ordonnance::class,
                'choice_label' => 'id',
            ])
            ->add('medicament', EntityType::class, [
                'class' => Medicament::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LigneOrdonnance::class,
        ]);
    }
}
