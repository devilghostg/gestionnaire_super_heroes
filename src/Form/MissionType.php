<?php

namespace App\Form;

use App\Entity\Mission;
use App\Entity\Power;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la mission',
                'attr' => ['placeholder' => 'Ex: Sauver la ville de Paris']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Décrivez la mission en détail...'
                ]
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices' => [
                    'Très facile' => 1,
                    'Facile' => 2,
                    'Moyenne' => 3,
                    'Difficile' => 4,
                    'Très difficile' => 5
                ]
            ])
            ->add('requiredPowers', EntityType::class, [
                'class' => Power::class,
                'choice_label' => 'name',
                'label' => 'Pouvoirs requis',
                'multiple' => true,
                'expanded' => true,
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mission::class,
        ]);
    }
}
