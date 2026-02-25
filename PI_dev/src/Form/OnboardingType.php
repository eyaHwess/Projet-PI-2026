<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OnboardingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('goals', TextareaType::class, [
                'label' => 'Vos objectifs',
                'required' => true,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ex: Améliorer ma productivité, mieux gérer mon stress, prendre du temps pour moi...',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez décrire vos objectifs.']),
                    new Assert\Length(['max' => 2000]),
                ],
            ])
            ->add('challenges', TextareaType::class, [
                'label' => 'Vos défis actuels',
                'required' => true,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ex: Manque de temps, difficulté à rester constant, procrastination...',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez décrire vos défis.']),
                    new Assert\Length(['max' => 2000]),
                ],
            ])
            ->add('motivationStyle', ChoiceType::class, [
                'label' => 'Style de motivation',
                'choices' => [
                    'Discipline' => 'discipline',
                    'Inspiration' => 'inspiration',
                    'Flexibilité' => 'flexibility',
                    'Structure' => 'structure',
                ],
                'placeholder' => 'Choisir...',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un style.']),
                ],
            ])
            ->add('planningStyle', ChoiceType::class, [
                'label' => 'Style de planification',
                'choices' => [
                    'Strict' => 'strict',
                    'Souple' => 'loose',
                    'Spontané' => 'spontaneous',
                    'Planifié' => 'scheduled',
                ],
                'placeholder' => 'Choisir...',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez sélectionner un style.']),
                ],
            ])
            ->add('interests', TextareaType::class, [
                'label' => 'Centres d\'intérêt',
                'required' => true,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ex: Sport, lecture, développement personnel, cuisine...',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez indiquer vos centres d\'intérêt.']),
                    new Assert\Length(['max' => 1500]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
        ]);
    }
}
