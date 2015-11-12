<?php

namespace TerAelis\TChatBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserListType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\UserBundle\Entity\User',
            'csrf_protection' => false
        ));
    }

    public function getName()
    {
        return 'teraelis_tchatbundle_salontype';
    }
}
