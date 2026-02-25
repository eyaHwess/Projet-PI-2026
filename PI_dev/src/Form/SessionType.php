<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scheduledAt', DateTimeType::class, [
                'label' => 'Date et heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'required' => true,
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix (€)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50.00',
                    'min' => 0,
                    'step' => 0.01,
                ],
                'required' => true,
                'html5' => true,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (en minutes)',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 60',
                    'min' => 15,
                    'max' => 240,
                ],
                'required' => true,
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité',
                'choices' => [
                    'Haute' => Session::PRIORITY_HIGH,
                    'Moyenne' => Session::PRIORITY_MEDIUM,
                    'Faible' => Session::PRIORITY_LOW,
                ],
                'attr' => ['class' => 'form-select'],
                'required' => false,
            ])
            ->add('objective', TextareaType::class, [
                'label' => 'Objectif de la session',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex : Clarifier les objectifs du client, travail sur la confiance en soi...',
                    'rows' => 3,
                ],
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En planification' => Session::STATUS_SCHEDULING,
                    'Proposé par l\'utilisateur' => Session::STATUS_PROPOSED_BY_USER,
                    'Proposé par le coach' => Session::STATUS_PROPOSED_BY_COACH,
                    'Confirmée' => Session::STATUS_CONFIRMED,
                    'Terminée' => Session::STATUS_COMPLETED,
                    'Annulée' => Session::STATUS_CANCELLED,
                ],
                'attr' => ['class' => 'form-select'],
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
