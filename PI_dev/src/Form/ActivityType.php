<?php

namespace App\Form;

use App\Entity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500',
                    'placeholder' => 'Ex: Courir 30 minutes'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
            ->add('startTime', DateTimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'heure de début est obligatoire.'])
                ]
            ])
            ->add('duration', TimeType::class, [
                'label' => 'Durée',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500'
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La durée est obligatoire.'])
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'pending',
                    'En cours' => 'in_progress',
                    'Terminé' => 'completed',
                    'Ignoré' => 'skipped',
                    'Annulé' => 'cancelled'
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500'
                ]
            ])
            ->add('hasReminder', CheckboxType::class, [
                'label' => 'Activer le rappel',
                'required' => false,
                'attr' => [
                    'class' => 'w-4 h-4 text-orange-500 border-gray-300 rounded focus:ring-orange-500'
                ]
            ])
            ->add('reminderAt', DateTimeType::class, [
                'label' => 'Date/heure de rappel',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500'
                ]
            ]);

        // Ajouter la validation conditionnelle pour le rappel
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $activity = $event->getData();
            $form = $event->getForm();

            if ($activity instanceof Activity && $activity->isHasReminder() && !$activity->getReminderAt()) {
                $form->get('reminderAt')->addError(
                    new \Symfony\Component\Form\FormError('La date/heure de rappel est obligatoire si le rappel est activé.')
                );
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}