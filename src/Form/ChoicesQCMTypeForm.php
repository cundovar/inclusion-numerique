<?php

namespace App\Form;

use App\Entity\ChoicesQCM;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoicesQCMTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Choix',
                'required' => true
            ])
            ->add('isCorrect', CheckboxType::class, [
                'label' => 'Réponse correcte ?',
                'required' => false
            ])
            ->add('explication', TextareaType::class, [
                'label' => 'Explication',
                'required' => true,
                'attr' => ['rows' => 3]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChoicesQCM::class,
        ]);
    }
}
