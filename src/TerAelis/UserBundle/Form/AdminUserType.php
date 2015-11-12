<?php

namespace TerAelis\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', 'text', array('required' => false))
            ->add('new_password', 'text', array('required' => false))
            ->add('new_password_confirm', 'text', array('required' => false));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $form = $event->getForm();

            $newPassword = $form->get('new_password')->getData();
            $newPasswordConfirm = $form->get('new_password_confirm')->getData();
            if ((!empty($newPassword) && empty($newPasswordConfirm))
                || (empty($newPassword) && !empty($newPasswordConfirm))
                || strcmp($newPassword, $newPasswordConfirm) !== 0) {
                $form->addError(new FormError('Les mots de passe ne sont pas identiques.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getName()
    {
        return 'teraelis_userbundle_admin_user';
    }
}
