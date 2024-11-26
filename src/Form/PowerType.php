<?php
// src/Form/PowerType.php

namespace App\Form;

use App\Entity\Power;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;  // Importation de TextareaType pour un champ de texte multi-lignes
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PowerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du pouvoir',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du pouvoir',
                'attr' => ['class' => 'form-control', 'rows' => 4]  // Utilisation de Textarea pour la description avec un nombre de lignes
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Power::class,
        ]);
    }
}