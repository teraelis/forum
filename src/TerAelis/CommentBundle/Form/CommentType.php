<?php

namespace TerAelis\CommentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body')
            ->add('previsualiser', 'submit', array(
                'attr' => array('class' => 'btn main')
            ))
            ->add('publier', 'submit', array(
                'attr' => array('class' => 'btn main')
            ));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\CommentBundle\Entity\Comment'
        ));
    }

    public function getName()
    {
        return 'teraelis_commentbundle_commenttype';
    }
}
