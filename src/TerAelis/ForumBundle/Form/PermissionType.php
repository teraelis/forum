<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PermissionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('voirCategorie', 'checkbox', array(
                'required' => false
            ))
            ->add('voirSujet', 'checkbox', array(
                'required' => false
            ))
            ->add('creerSujet', 'checkbox', array(
                'required' => false
            ))
            ->add('creerSpecial', 'checkbox', array(
                'required' => false
            ))
            ->add('repondreSujet', 'checkbox', array(
                'required' => false
            ))
            ->add('editerMessage', 'checkbox', array(
                'required' => false
            ))
            ->add('supprimerMessage', 'checkbox', array(
                'required' => false
            ))
            ->add('creerSondage', 'checkbox', array(
                'required' => false
            ))
            ->add('voter', 'checkbox', array(
                'required' => false
            ))
            ->add('moderer', 'checkbox', array(
                'required' => false
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Permission'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_permissiontype';
    }
}
