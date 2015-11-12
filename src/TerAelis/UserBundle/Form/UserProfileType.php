<?php

namespace TerAelis\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('skype', 'text', array(
                'label' => "Skype",
                "required" => false
            ))
            ->add('facebook', 'url', array(
                'label' => "Facebook",
                "required" => false
            ))
            ->add('twitter', 'url', array(
                'label' => "Twitter",
                "required" => false
            ))
            ->add('deviantArt', 'url', array(
                'label' => "DeviantArt",
                "required" => false
            ))
            ->add('site', 'url', array(
                'label' => "Site personnel",
                "required" => false
            ))
            ->add('allowMail', 'checkbox', array(
                'label' => "Autoriser l'envoie de mail à votre adresse",
                "required" => false
            ))
            ->add('showMail', 'checkbox', array(
                'label' => "Afficher votre adresse mail publiquement",
                "required" => false
            ))
            ->add('visible', 'checkbox', array(
                'label' => "Afficher votre présence en ligne",
                "required" => false
            ))
            ->add('pole', 'choice', array(
                'choices'   => array('litterature' => 'Pôle littéraire', 'graphisme' => 'Pôle graphique', 'rolisme' => "Pole roliste"),
                'label'     => "Pole de prédilection"
            ))
            ->add('file', 'file', array(
                'label' => "Avatar",
                "required" => false
            ))
            ->add('signature')
            ->add('biographie')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'teraelis_userbundle_usertype';
    }
}
