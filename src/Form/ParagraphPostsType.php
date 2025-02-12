<?php

namespace App\Form;

use App\Entity\ParagraphPosts;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

use Symfony\Component\Form\FormEvents;

class ParagraphPostsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('chatGptButton', ButtonType::class, [
            'attr' => [
                'class' => 'button my-4 ',
                'type' => 'button',
                'data-controller' => 'gpt',
                'data-action' => 'gpt#handleChatGptFill',
            ],
            'label' => 'Remplir le paragraphe avec ChatGPT',
        ])
        // ->add('imgPostParagh', FileType::class, [
        //         'label' => 'Image du paragraphe',
        //         'required' => false,
        //         'empty_data' => null,
        //         'data_class' => null,
        //         'attr' => [
        //             'placeholder' => 'max 5Mo',
        //             'class' => 'block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
        //         ],
        //         'constraints' => [
        //             new File([
        //                 'maxSize' => '5M',
        //                 'mimeTypes' => [
        //                     'image/jpeg',
        //                     'image/webp',
        //                     'image/png',
        //                 ],
        //                 'mimeTypesMessage' => 'Veuillez uploader une image valide', 
        //             ])
        //         ],
        //     ],)
        // ->add('altImg', TextType::class, [
        //         'label' => false,
        //         'required' => false,
        //         'attr' => [
        //             'class' => 'altImg hidden block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
        //             'placeholder' => 'Texte alternatif de l\'image (max 165 caractères)',
        //             'maxlength' => '165',
        //         ]
        //     ])
        ->add('subtitle', TextType::class, [
            'label' => 'Titre intermédiaire pour organiser le contenu',
            'required' => true,
            'attr' => [
                'class' => 'block p-2.5 w-full text-lg text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                'placeholder' => 'Sous-titre du paragraphe (max 170 caractères)',
                'maxlength' => '170',
                ]
                ])
            ->add('paragraph', TextareaType::class, [
                'label' => 'Contenu du paragraphe (texte de l\'article)',
                'attr' => [
                    'class' => 'py-4 ckeditor block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Paragraphe de l\'article (max 5000 caractères)',
                    'maxlength' => '5000',
                    'rows' => '4',
                    
                    ]
            ])

                
                
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParagraphPosts::class,
        ]);
    }
}
