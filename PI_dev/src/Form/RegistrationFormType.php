<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank(['message' => 'Le prénom est obligatoire.'])],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank(['message' => 'Le nom est obligatoire.'])],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'constraints' => [new NotBlank(['message' => "L'email est obligatoire."])],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Le mot de passe est obligatoire.']),
                    new Length(['min' => 8, 'minMessage' => 'Le mot de passe doit faire au moins {{ limit }} caractères.']),
                ],
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('age', IntegerType::class, [
                'label' => 'Âge',
                'required' => false,
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF ou WebP)',
                        'maxSizeMessage' => 'La taille de l\'image ne doit pas dépasser 2 Mo',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Type de compte',
                'mapped' => false,
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Coach' => 'ROLE_COACH',
                ],
                'placeholder' => 'Choisir...',
                'data' => 'ROLE_USER',
            ])
            ->add('speciality', TextType::class, [
                'label' => 'Spécialité',
                'required' => false,
            ])
            ->add('availability', TextType::class, [
                'label' => 'Disponibilité',
                'required' => false,
            ])
            ->add('pricePerSession', NumberType::class, [
                'label' => 'Prix par session (€)',
                'required' => false,
                'attr' => [
                    'min' => 0,
                    'step' => 0.01,
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 500,
                    'placeholder' => 'Présentez-vous en quelques mots...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
