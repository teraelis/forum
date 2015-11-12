<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('choix', 'entity', array(
                'type'   => 'choice',
                'class'    => 'TerAelisForumBundle:Choix',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->join('c.sondage', 's')
                        ->join('s.post', 'p')
                        ->where('p.id =');
                },
                'property' => 'name',
                'multiple' => false,
                'required' => true))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Vote'
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_votetype';
    }
}
