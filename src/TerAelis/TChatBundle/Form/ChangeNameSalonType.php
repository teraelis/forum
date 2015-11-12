<?php

namespace TerAelis\TChatBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangeNameSalonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\TChatBundle\Entity\Salon',
            'csrf_protection' => true
        ));
    }

    public function getName()
    {
        return 'teraelis_tchatbundle_changenamesalontype';
    }
}
