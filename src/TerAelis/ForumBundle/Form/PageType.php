<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pageTitle')
            ->add('pagePermalink')
            ->add('content', 'textarea', array(
                'attr' => array(
                    'rows' => 25,
                    'style' => 'width: 100%'
                )
            ));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Page'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_pagetype';
    }
}
