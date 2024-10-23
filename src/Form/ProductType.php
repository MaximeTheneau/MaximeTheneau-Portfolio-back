<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('productOptions', CollectionType::class, [
                'entry_type' => ProductOptionType::class,
                'allow_add' => true,
                'allow_delete' => true, 
                'by_reference' => false,
                'prototype' => true, 
                'prototype_name' => '__name__', 
                'attr' => ['class' => 'product-options-collection'],
                ])
            ->add('url')
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
