<?php

namespace TerAelis\TChatBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use TerAelis\TChatBundle\Entity\Message;
use TerAelis\TChatBundle\Entity\NonVuTChat;
use TerAelis\TChatBundle\Entity\Salon;
use TerAelis\UserBundle\Entity\User;

class PersistTchat
{
    protected $doctrine;

    function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function buildMessage(User $user, Salon $salon, $content, $date) {
        $message = new Message();
        $message->setSalon($salon);
        $message->setUser($user);
        $message->setCreatedAt($date);
        $message->setMessage($content);
        return $message;
    }

    function persistMessage(Message $message) {
        $em = $this->doctrine->getManager();
        $em->persist($message);
        $em->flush();

        $salon = $message->getSalon();
        $usersToNotify = $salon->getUsers();
        foreach($usersToNotify as $user) {
            if($user->getId() != $message->getUser()->getId()) {
                $nonVu = new NonVuTChat();
                $nonVu->setUser($user);
                $nonVu->setSalon($salon);
                $em->persist($nonVu);
            }
        }
        $usersToNotify = $salon->getMods();
        foreach($usersToNotify as $user) {
            if($user->getId() != $message->getUser()->getId()) {
                $nonVu = new NonVuTChat();
                $nonVu->setUser($user);
                $nonVu->setSalon($salon);
                $em->persist($nonVu);
            }
        }
        $em->flush();

        $salon->setLastUpdate(new \DateTime());
        $em->persist($salon);
        $em->flush();
    }
}
