<?php

namespace App\Form;

use App\Entity\Chatroom;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Vich\UploaderBundle\Form\Type\VichFileType;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Type message...',
                ]
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'image_uri' => false,
                'attr' => [
                    'accept' => 'image/*'
                ]
            ])
            ->add('file', VichFileType::class, [
                'label' => 'File',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'attr' => [
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.txt,.mp3,.mp4,.webm,.wav'
                ]
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Attachment',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                            'video/mp4',
                            'video/webm',
                            'video/quicktime',
                            'audio/webm',
                            'audio/mpeg',
                            'audio/mp3',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid file (Image, Video, Audio, PDF, Word, Excel, or Text)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Message::class,
        ]);
    }
}
