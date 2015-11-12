<?php
namespace TerAelis\StatistiquesBundle\DailyStats;

use Doctrine\ORM\EntityManager as EntityManager;
use TerAelis\StatistiquesBundle\Entity\DailyUpdate;
use TerAelis\StatistiquesBundle\Entity\MembreStat;
use TerAelis\StatistiquesBundle\Entity\PoleStat;
use TerAelis\StatistiquesBundle\Entity\SujetStat;

class DailyStatsService {
    /**
     * @var EntityManager
     */
    private $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function saveDaily()
    {
        $this->beginTransaction();

        $date = $this->getLastDate();

        $this->saveDailyMembre($date, false);
        $this->saveDailySujet($date, false);
        $this->saveDailyPole($date, false);

        $this->updateLastUpdate(new \DateTime());

        $this->commit();
    }

    /**
     * Calcule réalisés depuis la dernière MaJ :
     * - le nombre de sujet
     * - le nombre de commentaires
     * @param \DateTime $date
     * @param bool|true $standAlone
     */
    public function saveDailyMembre($date, $standAlone = true) {
        $entityManager = $this->em;

        if($standAlone)
            $this->beginTransaction();

        $dailyStats = [];

        $nbSujets = $entityManager->getRepository('TerAelisForumBundle:Post')
            ->findNbCreatedByUserSince($date);
        foreach($nbSujets as $result) {
            $user = $result['user'];
            $nbSujet = $result['nb_sujet'];

            $dailyStat = (new MembreStat())
                ->setMembre($user)
                ->setNbSujet($nbSujet)
                ->setNbCommentaire(0);

            $dailyStats[$user->getId()] = $dailyStat;
        }
        unset($nbSujets);

        $nbCommentaires = $entityManager->getRepository('TerAelisCommentBundle:Comment')
            ->findNbCreatedByUserSince($date);
        foreach($nbCommentaires as $result) {
            $user = $result['user'];
            $nbCommentaire = $result['nb_commentaire'];

            if(array_key_exists($user->getId(), $dailyStats)) {
                $dailyStat = $dailyStats[$user->getId()];
                $dailyStat->setNbCommentaire($nbCommentaire);
            } else {
                $dailyStat = (new MembreStat())
                    ->setMembre($user)
                    ->setNbSujet(0)
                    ->setNbCommentaire($nbCommentaire);
            }

            $dailyStats[$user->getId()] = $dailyStat;
        }
        unset($nbCommentaires);

        $now = new \DateTime();
        foreach($dailyStats as $d) {
            $d->setDate($now);
            $entityManager->persist($d);
            $entityManager->flush();
        }
        unset($dailyStats);

        if($standAlone)
            $this->commit();
    }

    /*
     * Comptabilise le nombre de commentaires sur un sujet depuis la date passée en paramètres
     */
    public function saveDailySujet($date, $standAlone = true) {
        $entityManager = $this->em;

        if($standAlone)
            $this->beginTransaction();

        $nbSujets = $entityManager->getRepository('TerAelisForumBundle:Post')
            ->findNbCommentsSince($date);
        $now = new \DateTime();
        foreach($nbSujets as $result) {
            $sujet = $result['sujet'];
            $nbCommentaire = $result['nb_comment'];

            $dailyStat = (new SujetStat())
                ->setSujet($sujet)
                ->setNbCommentaire($nbCommentaire);
            $dailyStat->setDate($now);
            $entityManager->persist($dailyStat);
            $entityManager->flush();
        }
        unset($nbSujets);

        if($standAlone)
            $this->commit();
    }

    /*
     * Comptabilise le nombre de sujets et le nombre de commentaires par pôles
     */
    public function saveDailyPole($date, $standAlone = true) {
        $entityManager = $this->em;

        if($standAlone)
            $this->beginTransaction();

        $dailyStats = array();

        $nbSujets = $entityManager->getRepository('TerAelisForumBundle:Post')
            ->findByPoleSince($date);
        foreach($nbSujets as $result) {
            $categorie = $result['categorie'];
            $nbSujet = $result['nb_sujet'];

            $root = $categorie->getRoot();
            if(array_key_exists($root, $dailyStats)) {
                $dailyStat = $dailyStats[$root];
                $dailyStat->setNbSujet($dailyStat->getNbSujet() + $nbSujet);
            } else {
                $dailyStat = new PoleStat();
                $dailyStat->setPole($root)
                    ->setNbSujet($nbSujet)
                    ->setNbCommentaire(0);
            }

            $dailyStats[$root] = $dailyStat;
        }
        unset($nbSujets);

        $nbComments = $entityManager->getRepository('TerAelisCommentBundle:Comment')
            ->findByPoleSince($date);
        foreach($nbComments as $result) {
            $categorie = $result['categorie'];
            $nbComment = $result['nb_comment'];

            $root = $categorie->getRoot();
            if(array_key_exists($root, $dailyStats)) {
                $dailyStat = $dailyStats[$root];
                $dailyStat->setNbCommentaire($dailyStat->getNbCommentaire() + $nbComment);
            } else {
                $dailyStat = new PoleStat();
                $dailyStat->setPole($root)
                    ->setNbSujet(0)
                    ->setNbCommentaire($nbComment);
            }

            $dailyStats[$root] = $dailyStat;
        }
        unset($nbCommentaires);

        $now = new \DateTime();
        foreach($dailyStats as $d) {
            $d->setDate($now);
            $entityManager->persist($d);
            $entityManager->flush();
        }
        unset($dailyStats);

        if($standAlone)
            $this->commit();
    }

    private function beginTransaction()
    {
        $this->em->beginTransaction();
    }

    private function commit()
    {
        $this->em->commit();
    }

    private function getLastDate()
    {
        $result = $this->em->getRepository('TerAelisStatistiquesBundle:DailyUpdate')
            ->findLastUpdate();
        if(empty($result)) {
            return null;
        } else {
            return $result->getLastUpdate();
        }
    }

    private function updateLastUpdate($date)
    {
        $lastUpdate = new DailyUpdate();
        $lastUpdate->setLastUpdate($date);
        $this->em->persist($lastUpdate);
        $this->em->flush();
    }
}