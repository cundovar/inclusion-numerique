<?php

namespace App\Form;

use App\Entity\PageBlock;
use App\Entity\PageContent;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\TextEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentBlockTypeForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content')
            ->add('code')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Text' => 'text',
                    'Code' => 'code',
                    
                 
                ],
                'placeholder'=>'choisi un type',
                'label'=>'type de bloc',
                'attr' => ['class' => 'js-block-type']
            ]);
            // EventListener dynamique
        $formModifier = function (FormInterface $form, ?string $type) {
            if ($type === 'text') {
                $form->add('content', TextEditorType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'js-block-type']
                ]);
            }

            if ($type === 'code') {
                $form->add('code', TextareaType::class, [
                    'label' => 'Code source',
                    'required' => false,
                    'attr' => ['class' => 'js-block-type']
                ]);
            }
        };

        // Événement PRE_SET_DATA (édition)
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $type = $data?->getType();
            $formModifier($event->getForm(), $type);
        });

        // Événement PRE_SUBMIT (soumission AJAX)
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $type = $data['type'] ?? null;
            $formModifier($event->getForm(), $type);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageBlock::class,
        ]);
    }
}


    

