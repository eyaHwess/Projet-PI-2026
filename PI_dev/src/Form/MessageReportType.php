<?php

namespace App\Form;

use App\Entity\MessageReport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessageReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reason', ChoiceType::class, [
                'label' => 'Raison du signalement',
                'choices' => [
                    'Contenu inapproprié' => 'inappropriate',
                    'Spam' => 'spam',
                    'Harcèlement' => 'harassment',
                    'Contenu offensant' => 'offensive',
                    'Fausses informations' => 'misinformation',
                    'Autre' => 'other',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une raison']),
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez le problème en détail...'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MessageReport::class,
        ]);
    }
}
