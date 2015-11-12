<?php

namespace TerAelis\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use TerAelis\UserBundle\Entity\User;
use TerAelis\UserBundle\Form\AdminUserType;

class AdminUserController extends Controller
{
    public function indexAction(Request $request) {
        $form = $this->createFormBuilder()
            ->add('user', $this->get('fos_user.username_form_type'))
            ->add('chercher', 'submit')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->get('user')->getData();
            if(!empty($user)) {
                $username = $user->getUsername();

                return $this->redirect(
                    $this->generateUrl(
                        'admin_users_change',
                        array(
                            'username' => $username,
                        )
                    )
                );
            } else {
                $form->addError(new FormError('Nom d\'utilisateur introuvable'));
            }
        }

        return $this->render(
            '@TerAelisUser/AdminUser/index.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function changeAction(Request $request, $username)
    {
        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository('TerAelisUserBundle:User')
            ->findByName($username);

        if (empty($user)) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        /**
         * @var User
         */
        $user = $user[0];

        $form = $this->createFormBuilder()
            ->add('username', 'text', array('required' => false))
            ->add('motDePasse', $this->get('fos_user.resetting.form.type'), array('required' => false, 'attr' => array('autocomplete' => 'off')))
            ->add('email', 'email', array('required' => false))
            ->setData(array(
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ))
            ->add('Modifier', 'submit')
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $newUsername = $form->get('username')->getData();
            if(!empty($newUsername)) {
                $user->setUsername($newUsername);
            } else {
                $newUsername = $username;
            }

            $plainPassword = $form->get('motDePasse')->get('new')->getData();
            if(!empty($plainPassword)) {
                $user->setPlainPassword($plainPassword);
                $user->setConfirmationToken(null);
                $user->setPasswordRequestedAt(null);
                $user->setEnabled(true);
            }

            $newEmail = $form->get('email')->getData();
            if(!empty($newUsername)) {
                $user->setEmail($newEmail);
            }

            $this->get('fos_user.user_manager')->updateUser($user, true);

            return $this->redirect(
                $this->generateUrl(
                    'admin_users_change',
                    array(
                        'username' => $newUsername,
                    )
                )
            );
        }

        return $this->render(
            '@TerAelisUser/AdminUser/change_user.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    public function updateRegistrationMessageAction(Request $request) {
        $renderArray = [];

        $registrationMessageService = $this->get('teraelis.registration.message_service');

        $message = ($registrationMessageService->hasMessage() ? $registrationMessageService->getMessage() : '');

        $form = $this->createFormBuilder()
            ->add('message', 'textarea')
            ->add('submit', 'submit')
            ->getForm();
        $form->setData(array(
            'message' => $message,
        ));
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $registrationMessageService
                ->updateMessage($form->get('message')->getData());

            $message = ($registrationMessageService->hasMessage() ? $registrationMessageService->getMessage() : '');
        }

        return $this->render(
            'TerAelisUserBundle:AdminUser:edit_registration_message.html.twig',
            array_merge(
                $renderArray,
                array(
                    'message' => $message,
                    'form' => $form->createView(),
                )
            )
        );
    }
}
