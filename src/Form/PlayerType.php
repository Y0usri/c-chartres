<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Category;
use App\Entity\Level;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PlayerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label'=>'Prénom','constraints'=>[new Assert\NotBlank()]])
            ->add('lastName', TextType::class, ['label'=>'Nom','constraints'=>[new Assert\NotBlank()]])
            ->add('birthDate', DateType::class, [ 'widget'=>'single_text', 'label'=>'Date de naissance'])
            ->add('category', EntityType::class, ['class'=>Category::class,'choice_label'=>'name'])
            ->add('level', EntityType::class, ['class'=>Level::class,'choice_label'=>'name'])
            ->add('photoFile', FileType::class, [ 'mapped'=>false, 'required'=>false, 'label'=>'Photo (image)', 'constraints'=>[ new Assert\Image(maxSize: '2M') ]]);
    }
    public function configureOptions(OptionsResolver $resolver): void { $resolver->setDefaults(['data_class'=> Player::class]); }
}
