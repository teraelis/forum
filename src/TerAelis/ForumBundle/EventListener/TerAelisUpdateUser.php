<?php
namespace TerAelis\ForumBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Security\Core\SecurityContext;
use TerAelis\ForumBundle\Controller\AdminController;
use TerAelis\ForumBundle\Controller\BlogController;
use TerAelis\ForumBundle\Controller\ForumController;

class TerAelisUpdateUser
{
    private $context;
    private $em;
    private $request;

    function __construct(SecurityContext $context, EntityManager $em, Request $request)
    {
        $this->context = $context;
        $this->em = $em;
        $this->request = $request;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        $controller = $event->getController();
        if(!is_array($controller)) {
            return;
        }

        $controller = $controller[0];
        if($controller instanceof ForumController || $controller instanceof BlogController) {
            $user = $this->context->getToken()->getUser();
            if($user != null && $user !== 'anon.') {
                $user->setLastVisit();
                $em = $controller->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
        }
        return;
    }
}