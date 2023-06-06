<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class,
            [
                'choices' => [
                    'user' => 'ROLE_USER',
                    'admin' => 'ROLE_ADMIN',
                    'manager' => 'ROLE_MANAGER',
                ],
                "multiple" => true,
                // radio buttons or checkboxes
                "expanded" => true
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)
            {
                $formulaire = $event->getForm();

                /** @var User $userEntity */
                $userEntity = $event->getData();

                if ($userEntity->getId() !== null)
                {   
                    $formulaire->add('password', PasswordType::class, [
                        'mapped' => false,
                        'attr' => [
                            'placeholder' => 'Laissez vide si inchangé'
                        ],
                        'constraints' => [
                            new NotBlank(),
                            // new Regex(
                            //     "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
                            //     "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, un chiffre et un caractère spécial"
                            // ),
                        ]
                    ]);
                } else {
                    
                    $formulaire->add('password', PasswordType::class, 
                        [
                            'empty_data' => '',
                            'constraints' => [
                                new NotBlank(),
                                // new Regex(
                                //     "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
                                //     "Le mot de passe doit contenir au minimum 8 caractères, une majuscule, un chiffre et un caractère spécial"
                                // ),
                            ]
                        ]);
                }

                

            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
