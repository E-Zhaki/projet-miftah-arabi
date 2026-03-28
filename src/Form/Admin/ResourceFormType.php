<?php

namespace App\Form\Admin;

use App\Entity\Lesson;
use App\Entity\Resource;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Vidéo' => 'video',
                    'PDF' => 'pdf',
                    'Audio' => 'audio',
                ],
                'placeholder' => 'Choisir un type',
            ])
            ->add('url', TextType::class)
            ->add('lesson', EntityType::class, [
                'class' => Lesson::class,
                'choice_label' => 'title',
                'placeholder' => 'Sélectionner une leçon',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resource::class,
        ]);
    }
}