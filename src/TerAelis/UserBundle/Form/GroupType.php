<?php

namespace TerAelis\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('roles', 'collection', array(
                'type'         => 'text',
                'allow_add'    => true,
                'allow_delete' => true))
            ->add('invisible', 'checkbox', array('required' => false))
            ->add('couleur')
            ->add('colorLitte')
            ->add('colorGfx')
            ->add('colorRp')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\UserBundle\Entity\Group'
        ));
    }

    public function getName()
    {
        return 'teraelis_userbundle_grouptype';
    }
}
