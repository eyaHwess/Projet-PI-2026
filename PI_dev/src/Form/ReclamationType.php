<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Enum\ReclamationTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Description détaillée',
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Décrivez votre problème en détail...'
                ]
            ])
            ->add('type', EnumType::class, [
                'class' => ReclamationTypeEnum::class,
                'choice_label' => fn($choice) => $choice->value,
                'label' => 'Type de réclamation'
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (optionnelle)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF, WebP)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
