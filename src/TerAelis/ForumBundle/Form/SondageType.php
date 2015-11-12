<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SondageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('question', 'text', array(
                'required'  => false
            ))
            ->add('choix', 'collection', array(
                'type'          => new ChoixType(),
                'allow_add'     => true,
                'allow_delete'  => true
            ));
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Sondage'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_sondagetype';
    }
}
