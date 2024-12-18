<?php

namespace App\Form;

use App\Entity\Posts;
use App\Entity\Category;
use App\Entity\Subcategory;
use App\Form\ListPostsType;
use App\Form\RelatedPostType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PostsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', EntityType::class, [
                'label' => false,
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
            ]
            )
            ->add('subcategory', EntityType::class, [
                'label' => "Sous-catégorie de l'article",
                'class' => Subcategory::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => false,
                'expanded' => true,
            ]
                )
            ->add('heading', TextType::class, [
                    'label' => 'Titre qui apparaîtra sur Google (obligatoire)',
                    'required' => true,
                    'attr' => [
                        'class' => 'block p-2.5 w-full text-lg text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                        'placeholder' => 'Titre de l\'article* (max 65 caractères)',
                        'id' => 'post_contents',
                        'maxlength' => '65',
                        'minlength' => '35',
                        ]
                ])
            ->add('title', TextType::class, [
                'label' => 'Titre affiché en haut de l\'article ',
                'required' => true,
                'attr' => [
                    'class' => 'block p-2.5 w-full text-lg text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Titre de l\'article* (max 70 caractères)',
                    'maxlength' => '70',
                    
                    ]
            ])
            ->add('metaDescription', TextType::class, [
                'label' => 'Résumé de l\'article affiché sur Google (obligatoire)',
                'required' => true,
                'attr' => [
                    'class' => 'block p-2.5 w-full text-lg text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Titre de l\'article* (max 70 caractères)',
                    'id' => 'post_contents',
                    'maxlength' => '135',
                    ]
            ])
            ->add('contents', TextareaType::class, [
                'label' => 'Texte d\'introduction visible en haut de l\'article (obligatoire)',
                'required' => false,
                'attr' => [
                    'class' => ' block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'placeholder' => 'Paragraphe de l\'article* (max 5000 caractères) ',
                    'maxlength' => '5000',
                    'rows' => '4',
                    ]
            ])
            ->add('imgPost', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'data_class' => null,
                'mapped' => true,
                'attr' => [
                    'class' => 'block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
                    'id' => 'image',
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp'
                        ],
                        'maxSize' => '5M',
                        'mimeTypesMessage' => 'Veuillez uploader une image valide', 
                    ])
                ],
            ],)
            // ->add('video', FileType::class, [
            //     'label' => 'Vidéo',
            //     'required' => false,
            //     'data_class' => null,
            //     'mapped' => true,
            //     'attr' => [
            //         'class' => 'block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500',
            //         'id' => 'image',
            //     ],
            //     'constraints' => [
            //         new File([
            //             'mimeTypes' => [
            //                 'video/mp4',
            //                 'video/ogg',
            //                 'video/webm',
            //             ],
            //             'maxSize' => '10M',
            //             'mimeTypesMessage' => 'Veuillez uploader une vidéo valide(ogg, mp4, webm)', 
            //         ])
            //     ],
            // ],)
            ->add('altImg', TextType::class, [
                'label' => 'Description de l\'image principale de l\'article (obligatoire)',
                'required' => false,
                'attr' => [
                    'class' => ' block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark',
                    'placeholder' => 'Texte alternatif de l\'image (max 165 caractères)',
                    'maxlength' => '165',
                ]
            ])
            ->add('listPosts', CollectionType::class, [
                'entry_type' => ListPostsType::class,
                'required' => false,
                'label' => false,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
            ->add('links', TextType::class, [
                'label' => 'Lien',
                'required' => false,
                'attr' => [
                    'class' => 'add__link block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 list-input',
                    'placeholder' => 'ex: https://www.exemple.fr',
                    'maxlength' => '500',
                ]
                ])
            ->add('textLinks', TextType::class, [
                    'label' => 'Texte du lien',
                    'required' => false,
                    'attr' => [
                        'class' => 'block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 list-input',
                        'placeholder' => 'max 255 caractères',
                        'maxlength' => '255',
                    ]
                    ])
                ->add('paragraphPosts', CollectionType::class, [
                    'entry_type' => ParagraphPostsType::class,
                    'label' => false,
                    'required' => false,
                    'entry_options' => ['label' => false],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'prototype' => true,
                ])
                ->add('github', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 list-input',
                        'placeholder' => 'ex: https://www.exemple.fr',
                        'maxlength' => '500',
                    ]
                    ])
                ->add('website', TextType::class, [
                    'label' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'block p-2.5 w-full  text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 list-input',
                        'placeholder' => 'ex: https://www.exemple.fr',
                        'maxlength' => '500',
                    ]
                    ])
                    ->add('isHomeImage', CheckboxType::class, [
                        'required' => false,
                        'label' => 'Définir comme image d\'accueil',
                    ])
                ->add('relatedPosts', EntityType::class, [
                        'class' => Posts::class,
                        'required' => false,
                        'choice_label' => 'title',
                        'expanded' => true,
                        'multiple' => true,
                        'by_reference' => false,
                    ])
                //    ->add('postId', HiddenType::class, [
                //         'mapped' => false, 
                //     ]) 
                ;

                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                    $form = $event->getForm();
                    $listPosts = $event->getData()->getParagraphPosts();
        
                    
                });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
