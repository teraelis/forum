<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use TerAelis\ForumBundle\Entity\CategorieRepository;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description', 'text', array(
                    'required' => false
            ))
            ->add('parent', 'entity', array(
                    'class'    => 'TerAelisForumBundle:Categorie',
                    'query_builder' => function(CategorieRepository $cr) {
                        return $cr->findAll();
                    },
                    'property' => 'title',
                    'multiple' => false,
                    'required' => false)
            )
            ->add('writable', 'checkbox', array(
                'label'     => 'Autoriser la création de sujets ?',
                'required'  => false,
                'attr'     => array('checked'   => 'checked')
            ))
            ->add('formulaire', 'collection', array(
                'type'         => new FormulaireTypeType(),
                'allow_add'    => true,
                'allow_delete' => true,
            ))
            ->add('balise', 'collection', array(
                'type'         => new BaliseType(),
                'allow_add'    => true,
                'allow_delete' => true,
            ))
            ->add('baliseObligatoire', 'checkbox', array(
                'label' => "Obligation d'utiliser une balise ?",
                'required' => false
            ))
            ->add('file', 'file', array(
                'label' => "Image",
                "required" => false
            ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Categorie',
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_categorietype';
    }
}
