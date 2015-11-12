<?php

namespace TerAelis\TChatBundle\Service;

use Doctrine\ORM\EntityManager;
use TerAelis\TChatBundle\Entity\NonVuTChat;
use TerAelis\TChatBundle\Entity\Salon;
use TerAelis\UserBundle\Entity\User;

class SalonManager {
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * SalonManager constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) { $this->em = $em; }

    /**
     * @param Salon  $salon
     * @param User[] $users
     * @param User[] $mods
     * @param bool   $isPrivate
     * @return Salon
     */
    public function createSalon(Salon $salon, $users, $mods, $isPrivate) {
        $salon->resetUsers();
        foreach($users as $u) {
            $salon->addUser($u);
        }

        foreach($mods as $m) {
            $salon->addMod($m);
        }

        $salon->setPrivate($isPrivate);

        $this->em->persist($salon);
        $this->em->flush();

        foreach($users as $u) {
            $nv = new NonVuTChat();
            $nv->setSalon($salon);
            $nv->setUser($u);
            $this->em->persist($nv);
        }
        $this->em->flush();

        return $salon;
    }
}