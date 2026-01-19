<?php

namespace App\Form;

use App\Entity\Activity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'activité',
                'required' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône (classe Font Awesome)',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: fas fa-palette']
            ])
            ->add('ageRange', TextType::class, [
                'label' => 'Tranche d\'âge',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 6-12 ans']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'required' => false,
                'currency' => 'DZD',
                'attr' => ['class' => 'form-control']
            ])
            ->add('numberOfClasses', IntegerType::class, [
                'label' => 'Nombre de classes',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: 1h30']
            ])
            ->add('image', TextType::class, [
                'label' => 'Image (chemin)',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Activée',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Activity::class,
        ]);
    }
}
