<?php

namespace App\Form;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\Secretaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RendezVousType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateRdv', DateType::class, [
                'label' => 'Date du rendez-vous',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'en attente',
                    'Confirmé' => 'confirmé',
                    'Annulé' => 'annulé',
                    'Terminé' => 'terminé'
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('patient', EntityType::class, [
                'class' => Patient::class,
                'choice_label' => function(Patient $patient) {
                    return $patient->getNom() . ' ' . $patient->getPrenom();
                },
                'label' => 'Patient',
                'attr' => ['class' => 'form-control']
            ])
            ->add('medecin', EntityType::class, [
                'class' => Medecin::class,
                'choice_label' => function(Medecin $medecin) {
                    return 'Dr. ' . $medecin->getNom() . ' ' . $medecin->getPrenom();
                },
                'label' => 'Médecin',
                'attr' => ['class' => 'form-control']
            ])
            ->add('secretaire', EntityType::class, [
                'class' => Secretaire::class,
                'choice_label' => function(Secretaire $secretaire) {
                    return $secretaire->getNom() . ' ' . $secretaire->getPrenom();
                },
                'label' => 'Secrétaire',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RendezVous::class,
        ]);
    }
}