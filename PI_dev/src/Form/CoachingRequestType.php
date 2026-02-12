<?php

namespace App\Form;

use App\Entity\CoachingRequest;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CoachingRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'Votre message',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez vos besoins et objectifs de coaching...',
                    'class' => 'form-control'
                ],
                'help' => 'Minimum 10 caractères, maximum 1000 caractères'
            ])
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
