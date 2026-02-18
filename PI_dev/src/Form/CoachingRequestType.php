<?php

namespace App\Form;

use App\Entity\CoachingRequest;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoachingRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('coach', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName() .
                        ($user->getSpeciality() ? ' - ' . $user->getSpeciality() : '');
                },
                'label' => 'Choisir un coach',
                'placeholder' => 'Sélectionnez un coach',
                'attr' => ['class' => 'form-select'],
                'choices' => $options['coaches'],
            ])
            ->add('goal', ChoiceType::class, [
                'label' => 'Objectif principal',
                'required' => false,
                'placeholder' => 'Sélectionnez votre objectif',
                'choices' => [
                    'Perte de poids' => 'Perte de poids',
                    'Prise de masse musculaire' => 'Prise de masse',
                    'Remise en forme générale' => 'Remise en forme',
                    'Préparation sportive' => 'Préparation sportive',
                    'Bien-être et santé' => 'Bien-être',
                    'Autre' => 'Autre',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('level', ChoiceType::class, [
                'label' => 'Niveau actuel',
                'required' => false,
                'placeholder' => 'Sélectionnez votre niveau',
                'choices' => [
                    'Débutant' => 'Débutant',
                    'Intermédiaire' => 'Intermédiaire',
                    'Avancé' => 'Avancé',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('frequency', ChoiceType::class, [
                'label' => 'Fréquence souhaitée',
                'required' => false,
                'placeholder' => 'Sélectionnez la fréquence',
                'choices' => [
                    '1 fois par semaine' => '1 fois/semaine',
                    '2 fois par semaine' => '2 fois/semaine',
                    '3 fois par semaine' => '3 fois/semaine',
                    '4+ fois par semaine' => '4+ fois/semaine',
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('budget', NumberType::class, [
                'label' => 'Budget par séance (€)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50',
                    'min' => 0,
                    'step' => 5
                ]
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message personnalisé',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez vos besoins, vos attentes et toute information utile pour le coach...',
                    'class' => 'form-control'
                ],
                'help' => 'Minimum 10 caractères, maximum 1000 caractères'
            ])
            ->add('priority', ChoiceType::class, [
                'label' => 'Priorité de la demande',
                'choices' => [
                    '🟢 Normal (réponse sous 48h)' => CoachingRequest::PRIORITY_NORMAL,
                    '🟠 Moyen (réponse sous 36h)' => CoachingRequest::PRIORITY_MEDIUM,
                    '🔴 Urgent (réponse sous 24h)' => CoachingRequest::PRIORITY_URGENT,
                ],
                'expanded' => true,
                'data' => CoachingRequest::PRIORITY_NORMAL,
                'attr' => ['class' => 'priority-radio-group'],
                'help' => 'La priorité peut être détectée automatiquement selon votre message'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CoachingRequest::class,
            'coaches' => [],
        ]);
        $resolver->setAllowedTypes('coaches', 'array');
    }
}
