<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use TerAelis\ForumBundle\Entity\Event;
use TerAelis\ForumBundle\Form\EventType;

class EventController extends Controller
{
    public function allAction($pole) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('TerAelisForumBundle:Event');

        $events = $repo->findNext();

        return $this->render(
            'TerAelisForumBundle:Events:list.html.twig',
            array(
                'pole' => $pole,
                'events' => $events
            )
        );
    }

    public function sidebarAction($pole) {
        $em = $this->get('doctrine.orm.entity_manager');

        $repo = $em->getRepository('TerAelisForumBundle:Event');

        $events = $repo->findNext($this->getParameter('nb_event'));

        return $this->render(
            'TerAelisForumBundle:Events:sidebar.html.twig',
            array(
                'pole' => $pole,
                'events' => $events
            )
        );
    }

    public function showEventAction($eventId) {
        $event = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Event')
            ->findOneBy(
                array(
                    'id' => $eventId
                )
            );
        if(empty($event)) {
            throw new NotFoundHttpException('Evènement introuvable.');
        }

        return $this->redirect($event->getUrl());
    }

    public function createEventAction(Request $request, $pole)
    {
        $user = $this->getUser();
        if(empty($user) || !($this->isGranted('ROLE_CREATE_EVENT') || $this->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('Vous n\'avez pas le droit de créer de nouveaux évènements.');
        }

        $event = new Event();
        $event->setDate(new \DateTime());
        $event->setPriority(false);
        $form = $this->createForm(
            new EventType(),
            $event,
            array(
                'can_moderate' => $this->isGranted('ROLE_MODO_EVENT') || $this->isGranted('ROLE_ADMIN'),
            )
        );
        $form->add('submit', 'submit', array(
            'label' => 'Créer',
        ));

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            return $this->handleEventForm($pole, $form);
        }

        return $this->render(
            'TerAelisForumBundle:Events:edit_event.html.twig',
            array(
                'title' => 'Créer un évènement',
                'form' => $form->createView(),
            )
        );
    }

    public function editEventAction(Request $request, $pole, $eventId) {
        $user = $this->getUser();
        if(empty($user) || !($this->isGranted('ROLE_MODO_EVENT') || $this->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'éditer des évènements.');
        }

        $event = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Event')
            ->findOneBy(
                array(
                    'id' => $eventId
                )
            );
        if(empty($event)) {
            throw new NotFoundHttpException('Evènement introuvable.');
        }

        $form = $this->createForm(
            new EventType(),
            $event,
            array(
                'can_moderate' => $this->isGranted('ROLE_MODO_EVENT') || $this->isGranted('ROLE_ADMIN'),
            )
        );
        $form->add('submit', 'submit', array(
            'label' => 'Editer',
        ));

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            return $this->handleEventForm($pole, $form);
        }

        return $this->render(
            'TerAelisForumBundle:Events:edit_event.html.twig',
            array(
                'title' => 'Editer un évènement',
                'form' => $form->createView(),
            )
        );
    }

    public function deleteEventAction(Request $request, $pole, $eventId) {
        $user = $this->getUser();
        if(empty($user) || !($this->isGranted('ROLE_MODO_EVENT') || $this->isGranted('ROLE_ADMIN'))) {
            throw new AccessDeniedException('Vous n\'avez pas le droit d\'éditer des évènements.');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $event = $em
            ->getRepository('TerAelisForumBundle:Event')
            ->findOneBy(
                array(
                    'id' => $eventId
                )
            );
        if(empty($event)) {
            throw new NotFoundHttpException('Evènement introuvable.');
        }

        $form = $this->createFormBuilder()
            ->add('submit', 'submit', array(
                'label' => 'Supprimer',
            ))
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $em->remove($event);
            $em->flush();

            return $this->redirect($this->generateUrl(
                'taforum_event_list',
                array(
                    'pole' => $pole,
                )
            ));
        }

        return $this->render(
            'TerAelisForumBundle:Events:delete_event.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param FormInterface $form
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function handleEventForm($pole, $form)
    {
        $event = $form->getData();

        $em = $this->get('doctrine.orm.entity_manager');

        $em->persist($event);
        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'taforum_event_list',
                array(
                    'pole' => $pole,
                )
            )
        );
    }
}