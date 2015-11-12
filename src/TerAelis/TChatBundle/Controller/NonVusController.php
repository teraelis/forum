<?php
namespace TerAelis\TChatBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NonVusController extends Controller
{
    public function nonVusAction($pole) {
        $user = $this->getUser();
        if($user != null) {
            $hasNew = true;

        } else {
            $hasNew = false;
        }

        return $this->render('TerAelisTChatBundle:NonVus:view.html.twig', array(
            'pole' => $pole,
            'user' => $user,
            'hasNew' => $hasNew
        ));
    }
}