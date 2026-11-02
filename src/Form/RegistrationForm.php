<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * Inscription d'un apprenant : une adresse e-mail pour se connecter, un pseudo affiché sur le site et utilisé par le
 * médiateur pour lui attribuer des contenus, et un mot de passe. Pas de nom réel.
 */
final class RegistrationForm extends AbstractType
{
    public const USERNAME_PATTERN = '/^[\p{L}\p{N}._-]{3,40}$/u';
    public const PASSWORD_MIN_LENGTH = 8;

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'constraints' => [
                    new NotBlank(message: 'Indiquez votre adresse e-mail.'),
                    new Email(message: "Cette adresse e-mail n'est pas valide."),
                    new Length(max: 180),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Pseudo',
                'constraints' => [
                    new NotBlank(message: 'Choisissez un pseudo.'),
                    new Regex(pattern: self::USERNAME_PATTERN, message: 'Le pseudo compte de 3 à 40 caractères : lettres, chiffres, point, tiret ou tiret bas, sans espace.'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'invalid_message' => 'Les deux mots de passe ne sont pas identiques.',
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['autocomplete' => 'new-password']],
                'second_options' => ['label' => 'Mot de passe, une deuxième fois', 'attr' => ['autocomplete' => 'new-password']],
                'constraints' => [
                    new NotBlank(message: 'Choisissez un mot de passe.'),
                    new Length(min: self::PASSWORD_MIN_LENGTH, max: 4096, minMessage: 'Le mot de passe compte au moins {{ limit }} caractères.'),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => "J'accepte les conditions d'utilisation",
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: "Cochez la case pour accepter les conditions d'utilisation."),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
