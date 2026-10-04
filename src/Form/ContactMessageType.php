<?php

namespace App\Form;

use App\Model\ContactMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'contact.your_name',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'contact.your_email',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('subject', TextType::class, [
                'label' => 'contact.subject',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'contact.your_message',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'contact.send',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            // Honeypot: real visitors never see or fill this field, bots usually do.
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
            ])
            // Lets the controller reject submissions sent faster than a human could type.
            ->add('formRenderedAt', HiddenType::class, [
                'mapped' => false,
                'data' => (string) time(),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactMessage::class,
            'translation_domain' => 'messages',
        ]);
    }
}
