<?php
namespace TerAelis\UserBundle\Controller;

use FOS\UserBundle\Controller\ChangePasswordController as Controller;
use FOS\UserBundle\Model\UserInterface;

class ChangePasswordController extends Controller {
    protected function getRedirectionUrl(UserInterface $user)
    {
        return $this->container->get('router')->generate('user_profile', array('id' => $user->getId()));
    }
}