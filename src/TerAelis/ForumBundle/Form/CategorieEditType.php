<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategorieEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description', 'text', array(
                'required' => false
            ))
            ->add('parent', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Categorie',
                    'property' => 'title',
                    'multiple' => false,
                    'required' => false)
            )
            ->add('writable', 'checkbox', array(
                'label'     => 'Autoriser la création de sujets ?',
                'required'  => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Categorie'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_categorieedittype';
    }
}
