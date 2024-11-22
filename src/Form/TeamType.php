<?php
// src/Form/TeamType.php
namespace App\Form;

use App\Entity\Team;
use App\Entity\SuperHero;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('superHeroes', EntityType::class, [
                'class' => SuperHero::class,
                'choice_label' => 'name',
                'multiple' => true,  // Permet de sélectionner plusieurs héros
                'expanded' => true,  // Affiche les héros sous forme de cases à cocher
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}