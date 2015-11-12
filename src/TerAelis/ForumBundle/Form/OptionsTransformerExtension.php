<?php

namespace TerAelis\ForumBundle\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OptionsTransformerExtension extends AbstractTypeExtension
{
    /**
     * Retourne le nom du type de champ qui est étendu
     *
     * @return string Le nom du type qui est étendu
     */
    public function getExtendedType()
    {
        return 'entity';
    }

    /**
     * Ajoute l'option image_path
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(
            array(
                'options_transformer'
            )
        );
    }


    /**
     * Passe l'url de l'image à la vue
     *
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (array_key_exists('options_transformer', $options)) {
            print_r($view->vars);
        }
    }
}