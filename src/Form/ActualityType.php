<?php

namespace App\Form;

use App\Entity\Actuality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ActualityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titleFr', TextType::class, [
                'label' => 'Titre (Français)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleEn', TextType::class, [
                'label' => 'Title (English)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('titleAr', TextType::class, [
                'label' => 'العنوان (العربية)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'dir' => 'rtl']
            ])
            ->add('descriptionFr', TextareaType::class, [
                'label' => 'Description (Français)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 6]
            ])
            ->add('descriptionEn', TextareaType::class, [
                'label' => 'Description (English)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 6]
            ])
            ->add('descriptionAr', TextareaType::class, [
                'label' => 'الوصف (العربية)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 6, 'dir' => 'rtl']
            ])
            ->add('images', FileType::class, [
                'label' => 'Images (plusieurs fichiers possibles)',
                'required' => false,
                'multiple' => true,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'help' => 'Vous pouvez sélectionner plusieurs images (JPG, PNG, GIF, WEBP)',
                'constraints' => [
                    new All([
                        new File([
                            'maxSize' => '10M',
                            'mimeTypes' => [
                                'image/jpeg',
                                'image/png',
                                'image/gif',
                                'image/webp'
                            ],
                            'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, GIF, WEBP)',
                        ])
                    ])
                ]
            ])
            ->add('video', FileType::class, [
                'label' => 'Vidéo',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'video/*'
                ],
                'help' => 'Format accepté : MP4, AVI, MOV (max 100MB)',
                'constraints' => [
                    new File([
                        'maxSize' => '100M',
                        'mimeTypes' => [
                            'video/mp4',
                            'video/avi',
                            'video/quicktime',
                            'video/x-msvideo'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une vidéo valide (MP4, AVI, MOV)',
                    ])
                ]
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publiée',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Actuality::class,
        ]);
    }
}
