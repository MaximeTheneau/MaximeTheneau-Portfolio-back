<?php

namespace App\Form;

use App\Entity\Posts;
use App\Entity\Skill;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('icon')
            ->add('description')
            ->add('posts', EntityType::class, [
                'class' => Posts::class,
                'choice_label' => 'title',
                'multiple' => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('p')
                                ->join('p.category', 'c')
                                ->where('c.slug = :slug')
                                ->setParameter('slug', 'Creations'); 
                    },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Skill::class,
        ]);
    }
}
