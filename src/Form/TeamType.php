<?php
// src/Form/TeamType.php
namespace App\Form;

use App\Entity\Team;
use App\Entity\SuperHero;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'équipe',
                'attr' => [
                    'placeholder' => 'Entrez le nom de l\'équipe',
                    'class' => 'form-control'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez l\'équipe',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('superHeroes', EntityType::class, [
                'class' => SuperHero::class,
                'choice_label' => function(SuperHero $hero) {
                    // Récupérer tous les pouvoirs (incluant le pouvoir principal et les pouvoirs additionnels)
                    $powers = $hero->getPowers()->toArray();
                    if ($hero->getPower() !== null) {
                        $powers[] = $hero->getPower();
                    }
                    
                    // Filtrer les doublons et les valeurs nulles
                    $powers = array_filter(array_unique($powers, SORT_REGULAR));
                    
                    $powerNames = array_map(function($power) {
                        return $power->getName();
                    }, $powers);
                    
                    $powerText = !empty($powerNames) ? implode(', ', $powerNames) : 'Aucun pouvoir';
                    return $hero->getName() . '|||' . $powerText;
                },
                'multiple' => true,
                'expanded' => true,
                'label' => 'Super-héros',
                'required' => false,
                'by_reference' => false,
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'hero-checkbox'];
                },
                'label_attr' => [
                    'class' => 'hero-label'
                ],
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