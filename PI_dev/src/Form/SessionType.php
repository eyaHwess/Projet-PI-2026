<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
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
