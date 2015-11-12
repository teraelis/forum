<?php

namespace TerAelis\TChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

class SidebarController extends Controller
{
    public function getAction($pole) {
        $user = $this->getUser();

        if($user != null) {
            $arrayRender = array('pole' => $pole);

            $this->get('teraelis.user_stats')
                ->updateUser($user);

            // Tests if the user has new messages
            $em = $this->get('doctrine.orm.entity_manager');
            $nonVuRepo = $em->getRepository('TerAelisTChatBundle:NonVuTChat');
            $result = $nonVuRepo->countByUser($user);
            $arrayRender['hasNewMessage'] = $result > 0;

            return $this->render('TerAelisTChatBundle:Sidebar:connected.html.twig', $arrayRender);
        } else {
            $request = $this->container->get('request');
            $session = $request->getSession();

            // last username entered by the user
            $lastUsername = (null === $session) ? '' : $session->get(SecurityContext::LAST_USERNAME);
            $csrfToken = $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate');
            $array = array(
                'pole' => $pole,
                'last_username' => $lastUsername,
                'csrf_token' => $csrfToken,
            );
            return $this->render('TerAelisTChatBundle:Sidebar:subscribe.html.twig', $array);
        }
    }
}
