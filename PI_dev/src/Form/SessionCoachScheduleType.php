<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SessionCoachScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('proposedTimeByCoach', DateTimeType::class, [
                'label' => 'Date et heure proposée',
                'required' => true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir une date et heure.']),
                    new Assert\GreaterThan('now', message: 'La date doit être dans le futur.'),
                ],
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée (minutes)',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 15,
                    'max' => 240,
                    'placeholder' => 'Ex: 60',
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La durée est obligatoire.']),
                    new Assert\Range(min: 15, max: 240, notInRangeMessage: 'La durée doit être entre 15 et 240 minutes.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
