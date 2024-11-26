<?php
namespace App\Form;

use App\Entity\Team;
use App\Entity\Power;
use App\Entity\SuperHero;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class SuperHeroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom du Super-Héros',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('alias', null, [
                'label' => 'Alias',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('power', EntityType::class, [ // Correction ici
                'class' => Power::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisissez un pouvoir',
                'required' => false,
                'label' => 'Pouvoir',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('weakness', null, [
                'label' => 'Faiblesse',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('is_active', CheckboxType::class, [
                'label' => 'Est Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
            ])
            ->add('teams', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name', 
                'multiple' => true,       
                'expanded' => true,       
                'label' => 'Équipes',
                'attr' => [
                    'class' => 'd-flex flex-wrap gap-2',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SuperHero::class,
        ]);
    }
}