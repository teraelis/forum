<?php

namespace TerAelis\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use TerAelis\UserBundle\Entity\User;

class UpdateUser {
    private $em;

    /**
     * UpdateUser constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param User $user
     */
    public function updateUser(User $user) {
        $date = new \DateTime();
        $date->sub(new \DateInterval('PT3600S'));
        if($user->getLastUpdate() < $date) {
            $user->setNbSujets($this->em->getRepository('TerAelisForumBundle:Post')->getNumberById($user->getId()));
            $user->setNbCommentaires($this->em->getRepository('TerAelisCommentBundle:Comment')->getNumberById($user->getId()));
            $user->setNbMessages($this->em->getRepository('TerAelisTChatBundle:Message')->getNumberById($user->getId()));
            $user->setLastUpdate();
            $this->em->persist($user);
            $this->em->flush();
        }
    }
}