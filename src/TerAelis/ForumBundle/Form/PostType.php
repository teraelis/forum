<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use TerAelis\ForumBundle\Entity\BaliseRepository;
use TerAelis\ForumBundle\Form\FormulaireDonneesType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idCategorie = $builder->getData()->getMainCategorie()->getId();
        $balise_array = array(
            'class' => 'TerAelisForumBundle:Balise',
            'query_builder' => function(BaliseRepository $er) use ($idCategorie) {
                return $er->getCategorie($idCategorie);
            },
            'expanded' => false,
            'multiple' => false,
        );
        if ($builder->getData()->getMainCategorie()->getBaliseObligatoire()) {
            $balise_array['required'] = true;
        } else {
            $balise_array['required'] = false;
        }

        $builder
            ->add('title')
            ->add('subTitle')
            ->add('tags', 'collection', array(
                'type'         => new TagType(),
                'allow_add'    => true,
                'allow_delete' => true))
            ->add('formulaireDonnees', 'collection', array(
                'type'         => new FormulaireDonneesType(),
                'label' => false
            ))
            ->add('balise', 'entity', $balise_array)
            ->add('typeSujet', 'entity', array(
                'class' => 'TerAelisForumBundle:TypeSujet',
                'expanded' => false,
                'multiple' => false,
                'required' => false
            ))
            ->add('sondage', new SondageType())
            ->add('previsualiser', 'submit', array(
                                                   'attr' => array('class' => 'btn main')
            ))
            ->add('publier', 'submit', array(
                                        'attr' => array('class' => 'btn main')
            ));

        if($options['has_date_publication']) {
            $builder->add('date_publication', 'datetime', array(
                'date_widget' => 'choice',
                'time_widget' => 'choice',
                'input' => 'datetime',
                'date_format' => 'yyyy-MM-dd',
                'model_timezone' => 'Europe/Paris',
                'view_timezone' => 'Europe/Paris',
                'required' => false
            ));
        }

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TerAelis\ForumBundle\Entity\Post',
            'cascade_validation' => true,
            'has_date_publication' => true,
        ));
    }

    public function getName()
    {
        return 'teraelis_forumbundle_posttype';
    }
}
