<?php
namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('date')
            ->add('url')
            ->add('pole', 'choice', array(
                'choices' => array(
                    'interpole' => 'Interpôle',
                    'litterature' => 'Littéraire',
                    'graphisme' => 'Graphique',
                    'rolisme' => 'Rôliste',
                )
            ))
        ;

        if($options['can_moderate']) {
            $builder->add('priority', 'checkbox', array(
                'required' => false,
            ));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Event',
            'can_moderate' => false,
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_eventtype';
    }
}
