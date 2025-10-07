<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', IntegerType::class, [
                'label' => 'Note (1 à 5)',
                'attr' => [
                    'min' => 1,
                    'max' => 5,
                    'step' => 1,
                    'placeholder' => 'Entre 1 et 5'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La note est obligatoire.'),
                    new Assert\Range(
                        min: 1,
                        max: 5,
                        notInRangeMessage: 'La note doit être comprise entre {{ min }} et {{ max }}.'
                    )
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => [
                    'minlength' => 2,
                    'placeholder' => 'Votre commentaire (min 2 caractères)'
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le commentaire ne peut pas être vide.'),
                    new Assert\Length(min: 2, minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.')
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'review_item',
        ]);
    }
}
