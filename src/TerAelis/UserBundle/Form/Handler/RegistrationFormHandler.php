<?php
namespace TerAelis\UserBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Form\Handler\RegistrationFormHandler as BaseRegistrationFormHandler;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\GroupManagerInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use TerAelis\TChatBundle\Entity\Salon;
use TerAelis\TChatBundle\Service\PersistTchat;
use TerAelis\TChatBundle\Service\SalonManager;
use TerAelis\UserBundle\Entity\UserRole;
use TerAelis\UserBundle\Service\RegistrationMessageService;

class RegistrationFormHandler extends BaseRegistrationFormHandler
{
    private $groupManager;
    private $em;
    private $defaultUserId;
    private $salonManager;
    private $persistManager;

    public function __construct(FormInterface $form, Request $request, UserManagerInterface $userManager, MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator, GroupManagerInterface $groupManager, EntityManager $em, SalonManager $salonManager, PersistTchat $persistManager, $defaultUserId, RegistrationMessageService $registrationMessage)
    {
        parent::__construct(
            $form,
            $request,
            $userManager,
            $mailer,
            $tokenGenerator
        );
        $this->em = $em;
        $this->groupManager = $groupManager;
        $this->salonManager = $salonManager;
        $this->defaultUserId = $defaultUserId;
        $this->persistManager = $persistManager;
        $this->registrationMessage = $registrationMessage;
    }


    protected function onSuccess(UserInterface $user, $confirmation)
    {
        $group = $this->groupManager->findGroupBy(
            array(
                'id' => 2
            )
        );

        if(!empty($group)) {
            $role = new UserRole();
            $role->setGroupe($group);
            $role->setUser($user);
            $role->setRole("usr");
            $this->em->persist($role);
            $this->em->flush();

            $user->addGroup($group);
            $user->setChosenGroup($group);
        }

        parent::onSuccess($user, $confirmation);

        $mainBotAccount = $this->userManager->findUserBy(
            array('id' => 20)
        );

        if($this->registrationMessage->hasMessage()) {
            $content = $this->registrationMessage->getMessage();
        } else {
            $content = '';
        }

        if(!empty($mainBotAccount) && !empty($content)) {
            $salon = new Salon();
            $salon->setName('Discussion privée');
            $salon = $this->salonManager->createSalon(
                $salon,
                array($user, $mainBotAccount),
                array($user),
                true
            );

            $message = $this->persistManager->buildMessage(
                $mainBotAccount,
                $salon,
                $content,
                new \DateTime()
            );
            $this->persistManager->persistMessage($message);
        }
    }

}