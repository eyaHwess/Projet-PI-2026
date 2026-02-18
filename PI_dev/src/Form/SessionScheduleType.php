<?php

namespace App\Form;

use App\Entity\Session;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SessionScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('proposedTimeByUser', DateTimeType::class, [
                'label' => 'Date et heure proposée',
                'required' => $options['is_user_proposal'] ?? true,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'min' => (new \DateTime())->format('Y-m-d\TH:i'),
                ],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Veuillez choisir une date et heure.']),
                    new Assert\GreaterThan('now', message: 'La date doit être dans le futur.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'is_user_proposal' => true,
        ]);
    }
}
