<?php

namespace TerAelis\UserBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class UserManager extends \FOS\UserBundle\Entity\UserManager
{
    public function findUserByName($username) {
        return $this->em->getRepository('TerAelisUserBundle:User')
            ->findOneByName($username);
    }
}
