<?php

namespace TerAelis\TChatBundle\Controller;

use FOS\UserBundle\Util\Canonicalizer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\TChatBundle\Entity\Message;
use TerAelis\TChatBundle\Entity\NonVuTChat;
use TerAelis\TChatBundle\Entity\Salon;
use TerAelis\TChatBundle\Form\ChangeNameSalonType;
use TerAelis\TChatBundle\Form\MessageType;
use TerAelis\TChatBundle\Form\SalonType;
use TerAelis\TChatBundle\Form\UserListType;
use TerAelis\UserBundle\Entity\User;
use TerAelis\UserBundle\Entity\UserRepository;

class SalonController extends Controller
{
    public function listAction() {
        $user = $this->getUser();

        $repo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisTChatBundle:Salon');

        $salons = $repo->getRooms($user->getId(), null);

        $nonVuBySalonId = $this->getNonVusBySalon($user, $salons);

        $public = array();
        $private = array();

        foreach($salons as $salon) {
            if(array_key_exists($salon->getId(), $nonVuBySalonId)) {
                $salon->new = true;
            } else {
                $salon->new = false;
            }

            if($salon->getPrivate()) {
                $users = $salon->getUsers();
                foreach($users as $u) {
                    if($user->getId() != $u->getId()) {
                        $userToDisplay = $u;
                    }
                }

                $private[] = array(
                    'id' => $salon->getId(),
                    'user' => $userToDisplay,
                    'new' => $salon->new
                );
            } else {
                $public[] = $salon;
            }
        }

        return $this->render('TerAelisTChatBundle:Salon:list.html.twig', array(
            'private' => $private,
            'public' => $public
        ));
    }

    public function createAction(Request $request) {
        $user = $this->getUser();

        $salon = new Salon;
        $form = $this->createForm(new SalonType(), $salon,
            array(
                'userType' => $this->get('fos_user.username_form_type')
            )
        );

        // Le formulaire est déjà envoyé
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $users = $form->get('users')->getData();
            $this->get('teraelis.tchat.salon_manager')
                ->createSalon(
                    $salon,
                    $users,
                    array($user),
                    false
                );
            return $this->redirect($this->generateUrl('teraelis_tchat'));
        }

        return $this->render('TerAelisTChatBundle:Salon:create.html.twig', array('form' => $form->createView()));
    }

    public function showAction(Request $request, $id) {
        $user = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getRoomFull($id);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        $salonUsers = $salon->getUsers();
        $salonMods = $salon->getMods();
        $allow = false;
        $mod = false;

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

        $messages = $salon->getMessages();

        // Création du formulaire
        $message = new Message();
        $message->setSalon($salon);
        $message->setUser($user);

        if ($request->isMethod('POST')) {
            $message->setCreatedAt(new \DateTime());
            $message->setMessage(json_decode($request->getContent())->message);

            $persistService = $this->get('teraelis.tchat.persist_chat');
            $persistService->persistMessage($message);

            $response = new Response();
            $response->setContent(
                json_encode(
                    array(
                        'response'=> 'ok',
                        'createdAt'=> $message->getCreatedAt()->getTimestamp()
                    )
                )
            );
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else if($request->isMethod('OPTION')) {
            $response = new Response();
            $response->headers->set('Allow', 'GET', 'POST', 'OPTIONS');
            return $response;
        }
        $form = $this->createForm(new MessageType(), $message);

        $renderArray = array(
            'date' => (new \DateTime())->getTimestamp(),
            'salon' => $salon,
            'users' => $salonUsers,
            'mods' => $salonMods,
            'mod' => $mod,
            'messages' => $messages,
            'form' => $form->createView()
        );

        if($salon->getPrivate()) {
            $otherUser = null;
            foreach($salonUsers as $otherUser) {
                if($user->getId() != $otherUser->getId())
                    break;
            }
            $renderArray['otherUser'] = $otherUser;
        }

        $nonVus = $em->getRepository('TerAelisTChatBundle:NonVuTChat')
            ->nonVuBySalon($salon, $user);
        foreach ($nonVus as $nv) {
            $em->remove($nv);
        }
        $em->flush();

        return $this->render('TerAelisTChatBundle:Salon:show.html.twig', $renderArray);
    }

    public function privateDiscussionAction($userId) {
        $user = $this->getUser();
        if($user == null) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        $em = $this->getDoctrine()
            ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getPrivateRoom($user->getId(), $userId);

        if($salon == null) {
            $otherUser = $em->getRepository('TerAelisUserBundle:User')
                            ->findOneBy(array('id' => $userId));
            if($otherUser == null) {
                throw $this->createNotFoundException('User not found');
            }

            $salon = new Salon();
            $salon->setName('Discussion privée');

            $this->get('teraelis.tchat.salon_manager')
                 ->createSalon(
                     $salon,
                     array($user, $otherUser),
                     array($user, $otherUser),
                     true
                 );

            $em->persist($salon);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
    }

    public function quitAction($id, $userId = null) {
        /* On récupère le salon */
        $em = $this->getDoctrine()
            ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getRoomFull($id);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        /* On récupère le user a delete et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();

        if($userId == null || $userId == 0) {
            $userToDelete = $user;
        } else {
            $allow = $this->isGranted('ROLE_ADMIN');
            $salonMods = $salon->getMods();
            if(!$allow) {
                foreach($salonMods as $m) {
                    if($m->getId() == $user->getId()) {
                        $allow = true;
                        break;
                    }
                }
            }
            if(!$allow) {
                throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
            }
            $userToDelete = $em->getRepository('TerAelisUserBundle:User')
                ->findOneBy(array('id'=>$userId));
            if(empty($userToDelete)) {
                return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $id)));
            }
        }

        $form = $this->createFormBuilder()
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);
            if($form->isValid()) {
                $salon->removeUser($userToDelete);
                $em->persist($salon);
                $em->flush();

                return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
            }
        }
        return $this->render("TerAelisTChatBundle:Salon:form.html.twig", array(
            'salon' => $salon,
            'form' => $form->createView(),
            'submit' => "Supprimer ".$userToDelete->getUsername()." du salon"
        ));
    }

    public function addUserAction(Request $request, $id) {
        /* On récupère le salon */
        $em = $this->getDoctrine()
            ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getRoomFull($id);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        /* On récupère le user et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();
        $allow = $this->isGranted('ROLE_ADMIN');
        $salonMods = $salon->getMods();
        if(!$allow) {
            foreach($salonMods as $m) {
                if($m->getId() == $user->getId()) {
                    $allow = true;
                    break;
                }
            }
        }
        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        /* Gestion du formulaire */
        $userAdded = new User();
        $form = $this->createFormBuilder()
            ->add('user', $this->get('fos_user.username_form_type'))
            ->getForm();

        // Le formulaire est déjà envoyé
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $salon->addUser($form->get('user')->getData());
            $em->persist($salon);
            $em->flush();

            return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
        }
        return $this->render("TerAelisTChatBundle:Salon:form.html.twig", array(
            'salon' => $salon,
            'form' => $form->createView(),
            'submit' => "Ajouter l'utilisateur"
        ));
    }

    public function setModeratorAction($id, $userId) {
        /* On récupère le salon */
        $em = $this->getDoctrine()
            ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getRoomFull($id);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        /* On récupère le user et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();
        $allow = $this->isGranted('ROLE_ADMIN');
        $salonMods = $salon->getMods();
        if(!$allow) {
            foreach($salonMods as $m) {
                if($m->getId() == $user->getId()) {
                    $allow = true;
                    break;
                }
            }
        }
        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        $userToPromote = $em->getRepository('TerAelisUserBundle:User')
            ->findOneBy(array('id' => $userId));
        if(empty($userToPromote)) {
            throw $this->createNotFoundException("Utilisateur invalide.");
        }

        $form = $this->createFormBuilder()
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);

            if($form->isValid()) {
                $salon->removeUser($userToPromote);
                $salon->addMod($userToPromote);
                $em->persist($salon);
                $em->flush();

                return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
            }
        }
        return $this->render("TerAelisTChatBundle:Salon:form.html.twig", array(
            'salon' => $salon,
            'form' => $form->createView(),
            'submit' => "Promouvoir ".$userToPromote->getUsername()." au rang de modérateur"
        ));
    }

    public function setUserAction($id, $userId) {
        /* On récupère le salon */
        $em = $this->getDoctrine()
            ->getManager();
        $salon = $em->getRepository('TerAelisTChatBundle:Salon')
            ->getRoomFull($id);

        if($salon == null){
            throw $this->createNotFoundException('Salon introuvable.');
        }

        /* On récupère le user et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();
        $allow = $this->isGranted('ROLE_ADMIN');
        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        $userToDowngrade = $em->getRepository('TerAelisUserBundle:User')
            ->findOneBy(array('id' => $userId));
        if(empty($userToDowngrade)) {
            throw $this->createNotFoundException("Utilisateur invalide.");
        }

        $form = $this->createFormBuilder()
            ->getForm();

        // Le formulaire est déjà envoyé
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST')
        {
            $form->bind($request);

            if($form->isValid()) {
                $salon->removeMod($userToDowngrade);
                $salon->addUser($userToDowngrade);
                $em->persist($salon);
                $em->flush();

                return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
            }
        }
        return $this->render("TerAelisTChatBundle:Salon:form.html.twig", array(
            'salon' => $salon,
            'form' => $form->createView(),
            'submit' => "Rétrograder ".$userToDowngrade->getUsername()." au rang de participant"
        ));
    }

    public function hideAction($idMessage, $newVal) {
        $em = $this->getDoctrine()
            ->getManager();
        $repo = $em->getRepository('TerAelisTChatBundle:Message');

        $message = $repo->getMessage($idMessage);
        if(empty($message)) {
            throw $this->createNotFoundException("Message introuvable (id = ".$idMessage.").");
        }

        $salon = $message->getSalon();

        /* On récupère le user et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();
        $allow = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $salonMods = $salon->getMods();
        if(!$allow) {
            foreach($salonMods as $m) {
                if($m->getId() == $user->getId()) {
                    $allow = true;
                    break;
                }
            }
        }
        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        $message->setHide($newVal);
        $em->persist($message);
        $em->flush();

        return $this->redirect($this->generateUrl('teraelis_tchat_show', array('id' => $salon->getId())));
    }

    public function getSideBarAction($user) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repoSalon = $em->getRepository('TerAelisTChatBundle:Salon');

        $arrayRender = array();
        $publicRooms = $repoSalon->getRooms($user->getId(), $this->container->getParameter('sidebar.nbsalon'), true);
        $fullRooms = [];
        foreach($publicRooms as $r) {
            $fullRooms[] = $r;
        }

        $contacts = $em->getRepository('TerAelisUserBundle:User')
            ->getContacts($user, $this->container->getParameter('sidebar.nbsalon'));

        $privateRooms = [];
        foreach($contacts as $contact) {
            $salons = $contact->getSalons();
            foreach($salons as $s) {
                $privateRooms[] = $s;
                $fullRooms[] = $s;
            }
        }

        if(count($fullRooms) > 0) {
            $nonVusBySalonId = $this->getNonVusBySalon($user, $fullRooms);

            if(count($publicRooms) > 0) {
                foreach ($publicRooms as $room) {
                    if (array_key_exists($room->getId(), $nonVusBySalonId)) {
                        $room->new = true;
                    } else {
                        $room->new = false;
                    }
                }
                $arrayRender['salons'] = $publicRooms;
                $arrayRender['emptyRooms'] = false;
            } else {
                $arrayRender['emptyRooms'] = true;
            }

            if(count($contacts) > 0) {
                $connected = [];
                $disconnected = [];
                foreach($contacts as $contact) {
                    $new = false;
                    $salons = $contact->getSalons();
                    foreach ($salons as $s) {
                        if (array_key_exists($s->getId(), $nonVusBySalonId)) {
                            $new = true;
                        }
                    }
                    $contact->new = $new;

                    $lastVisit = $contact->getLastVisit();
                    $timeLimit = (new \Datetime())->modify('-15 min');
                    if(is_object($lastVisit)) {
                        $contact->connected = $timeLimit < $contact->getLastVisit();
                    } else {
                        $contact->connected = $timeLimit->getTimestamp() < $contact->getLastVisit();
                    }

                    if($contact->connected) {
                        $connected[] = $contact;
                    } else {
                        $disconnected[] = $contact;
                    }
                }

                $arrayRender['contacts'] = array_merge($connected, $disconnected);
                $arrayRender['emptyContacts'] = false;
            } else {
                $arrayRender['emptyContacts'] = true;
            }
        } else {
            $arrayRender['emptyContacts'] = true;
            $arrayRender['emptyRooms'] = true;
        }

        return $this->render('TerAelisTChatBundle:Sidebar:messagerie.html.twig', $arrayRender);
    }

    public function changeNameAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();
        $salonRepo = $em->getRepository('TerAelisTChatBundle:Salon');

        try {
            $salon = $salonRepo->findOneBy(array('id' => $id));
        } catch(\Exception $e) {
            throw $this->createNotFoundException('Salon introuvable.');
        }

        /* On récupère le user et on s'assure qu'il a le droit de delete */
        $user = $this->getUser();
        if(!$user) {
            throw new AccessDeniedException('Vous devez être authentifié pour pouvoir effectuer cette action.');
        }
        $allow = $this->get('security.context')->isGranted('ROLE_ADMIN');
        $salonMods = $salon->getMods();
        if(!$allow) {
            foreach($salonMods as $m) {
                if($m->getId() == $user->getId()) {
                    $allow = true;
                    break;
                }
            }
        }
        if(!$allow) {
            throw new AccessDeniedException("Vous n'avez pas le droit de modérer ce salon.");
        }

        $form = $this->createForm(new ChangeNameSalonType(), $salon);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($salon);
            $em->flush();

            return $this->redirect($this->generateUrl('teraelis_tchat_show', array( 'id' => $salon->getId() )));
        }

        return $this->render('TerAelisTChatBundle:Salon:form.html.twig', array(
            'salon' => $salon,
            'form' => $form->createView(),
            'submit' => 'Changer de nom'
        ));
    }

    private function getNonVusBySalon($user, $rooms) {
        $em = $this->getDoctrine()
            ->getManager();

        $nonVus = $em->getRepository('TerAelisTChatBundle:NonVuTChat')->findByUser($user, $rooms);
        $nonVuBySalonId = array();
        foreach($nonVus as $nv) {
            $nonVuBySalonId[$nv->getSalon()->getId()] = true;
        }

        return $nonVuBySalonId;
    }
}
