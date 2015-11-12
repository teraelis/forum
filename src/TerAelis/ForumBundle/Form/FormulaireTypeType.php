<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FormulaireTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('description', 'text', array(
                    'required'  => false,
            ))
            ->add('is_title_visible', 'checkbox', array(
                'required' => false,
            ))
            ->add('default', 'text', array("required" => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\FormulaireType'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_formulairetypetype';
    }
}
