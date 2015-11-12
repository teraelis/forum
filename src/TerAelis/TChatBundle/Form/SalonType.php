<?php

namespace TerAelis\TChatBundle\Form;

use FOS\UserBundle\Form\Type\UsernameFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('users', 'collection', array(
                'type' => $options['userType'],
                'allow_add'    => true,
                'allow_delete' => true
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\TChatBundle\Entity\Salon',
            'csrf_protection' => true,
            'userType' => new UserListType(),
        ));
    }

    public function getName()
    {
        return 'teraelis_tchatbundle_salontype';
    }
}
