<?php

namespace App\Form;

use App\Entity\Recipes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifyRecipeType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, $this->getConfiguration("Titre", "Intitulé de la recette"))
            ->add('time', IntegerType::class, $this->getConfiguration("Durée (minutes)", "Durée depréparation de la recette"))
            ->add('level', ChoiceType::class, [
                'label'=>"Niveau de difficulté de la recette",
                "required"=>true,
                'placeholder'=>"Choisis le niveau de difficulté",
                'choices'=>[
                    "Facile"=>"Facile",
                    "Moyen"=>"Moyen",
                    "Difficile"=>"Difficile"
                ]
            ])
            ->add('budget', ChoiceType::class, [
                'label'=>"Budget de la recette",
                "required"=>true,
                'placeholder'=>"Choisis le coût de la recette",
                'choices'=>[
                    "Faible"=>"Faible",
                    "Moyen"=>"Moyen",
                    "Coûteux"=>"Coûteux"
                ]
            ])            
            ->add('portions',IntegerType::class, $this->getConfiguration("Portions", "Nombre de portions de la recette"))
            ->add('image', FileType::class,[
                "label"=> "Image de la recette(jpg, jpeg, png)",
                'data_class'=>null,
                "required"=>false,
            ])
            ->add('ingredient', TextareaType::class, $this->getConfiguration("Ingrédients", "Ingédients de la recette"))
            ->add('steps', TextareaType::class, $this->getConfiguration("Étapes", "Étapes de la recette"))
            ->add('category', ChoiceType::class,[
                'label'=>"Catégorie de la recette",
                "required"=>true,
                'placeholder'=>"Choisissez la catégorie de la recette",
                'choices'=>[
                    "Café"=>"Café",
                    "Thé"=>"Thé",
                    "Limonade"=>"Limonade",
                    "Mocktail"=>"Mocktail",
                    "Cocktail"=>"Cocktail",
                    "Mix-fruité"=>"Mix-fruité"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipes::class, 
        ]);
    }
}
