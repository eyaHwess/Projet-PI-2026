<?php

namespace App\Form;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('password', PasswordType::class)
            ->add('phoneNumber')
            ->add('age')
           ->add('role', ChoiceType::class, [
    'mapped' => false,
    'choices' => [
        'Utilisateur' => 'ROLE_USER',
        'Coach' => 'ROLE_COACH',
    ],
    'multiple' => false,   // IMPORTANT
    'expanded' => false,   // dropdown select
])

            ->add('speciality', TextType::class, [
                'required' => false,
            ])
            ->add('availability', TextType::class, [
                'required' => false,
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
