<?php

namespace App\Form;

use App\Entity\PrivateChatroom;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PrivateChatroomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du sous-groupe',
                'attr' => [
                    'placeholder' => 'Ex: Équipe Marketing',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le nom est requis']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('members', EntityType::class, [
                'class' => User::class,
                'choice_label' => function(User $user) {
                    return $user->getFirstName() . ' ' . $user->getLastName();
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Membres du sous-groupe',
                'choices' => $options['available_members'],
                'attr' => [
                    'class' => 'members-checkbox-list'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $options): void
    {
        $options->setDefaults([
            'data_class' => PrivateChatroom::class,
            'available_members' => [],
        ]);
    }
}
