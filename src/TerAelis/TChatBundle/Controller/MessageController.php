<?php

namespace TerAelis\TChatBundle\Controller;

use Assetic\Extension\Twig\TwigResource;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use TerAelis\ForumBundle\Twig\Extensions\PostExtension;

class MessageController extends Controller
{
    public function lastAction($idSalon, $timestamp)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()
              ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
                 ->getRoomFull($idSalon);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        $salonUsers = $salon->getUsers();
        $salonMods = $salon->getMods();
        $allow = $this->isGranted('ROLE_ADMIN');
        $mod = $this->isGranted('ROLE_ADMIN');

        if(!$allow) {
            foreach($salonMods as $m) {
                if($m->getId() == $user->getId()) {
                    $allow = true;
                    $mod = true;
                    break;
                }
            }
            if(!$allow) {
                foreach($salonUsers as $u) {
                    if($u->getId() == $user->getId()) {
                        $allow = true;
                        break;
                    }
                }
            }
        }

        if(!$allow) {
            throw $this->createNotFoundException('Salon introuvable.');
        }

        $date = new \DateTime();
        $date->setTimestamp($timestamp);

        $messages = $this->getDoctrine()
            ->getRepository('TerAelisTChatBundle:Message')
            ->getLast($idSalon, $date);

        $returnMessages = array();
        $postExtension = $this->container->get('twig.extension.ter_aelis_post_extension');

        foreach($messages as $m) {
            $date = $m->getCreatedAt();
            $dateString = $postExtension->showDate($date);
            $returnMessages[] = array(
                'user' => $postExtension->showUsername($m->getUser()),
                'date' => $dateString,
                'mod' => $mod,
                'hide' => $m->getHide(),
                'id' => $m->getId(),
                'message' => $m->getMessage()
            );
        }

        $nonVus = $em->getRepository('TerAelisTChatBundle:NonVuTChat')
            ->nonVuBySalon($salon, $user);
        foreach ($nonVus as $nv) {
            $em->remove($nv);
        }
        $em->flush();

        $response = new Response();
        $response->setContent(
            json_encode(
                array(
                     'time' => (new \DateTime())->getTimestamp(),
                     'messages' => $returnMessages
                )));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
