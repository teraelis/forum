<?php

namespace TerAelis\StatistiquesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StatistiquesController extends Controller
{
    public function indexAction($byPeriod = '') {
        $renderArray = [];
        if(empty($byPeriod)) {
            $byPeriod = 'month';
        }

        switch ($byPeriod) {
            case 'month':
                $today = new \DateTime();
                $dateStart = clone $today;
                $dateStart->modify('-12 months');
                $dateEnd = $today;
                break;
            default:
                throw new \ErrorException('Wrong period given');
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $poleStats = $em->getRepository('TerAelisStatistiquesBundle:PoleStat')
            ->findByPeriod(
                $byPeriod,
                $dateStart,
                $dateEnd
            );

        $poleStatsView = [
            $this->rootToPole('litterature') => [],
            $this->rootToPole('graphisme') => [],
            $this->rootToPole('rolisme') => [],
            $this->rootToPole('interpole') => [],
        ];
        $maxNbSujet = 0;
        $maxNbCommentaire = 0;
        $globalMaxNbSujet = 0;
        $globalMaxNbCommentaire = 0;
        $globalStatsView = [];
        foreach($poleStats as $stat) {
            $pole = $this->rootToPole($stat['pole']);
            if(!empty($pole)) {
                $date = $this->dateToView($byPeriod, new \DateTime($stat['date']));

                if(!array_key_exists($pole, $poleStatsView)) {
                    $poleStatsView[$pole] = [];
                }

                $poleStatsView[$pole][] = array(
                    'nb_sujet' => $stat['nb_sujet'],
                    'nb_commentaire' => $stat['nb_commentaire'],
                    'date' => $date,
                );

                $maxNbSujet = max($maxNbSujet, $stat['nb_sujet']);
                $maxNbCommentaire = max($maxNbCommentaire, $stat['nb_commentaire']);

                if(array_key_exists($stat['month'], $globalStatsView)) {
                    $globalStatsView[$stat['month']]['nb_sujet'] += $stat['nb_sujet'];
                    $globalStatsView[$stat['month']]['nb_commentaire'] += $stat['nb_commentaire'];
                } else {
                    $globalStatsView[$stat['month']] = [
                        'nb_sujet' => $stat['nb_sujet'],
                        'nb_commentaire' => $stat['nb_commentaire'],
                        'date' => $date,
                    ];
                }
                $globalMaxNbSujet += $stat['nb_sujet'];
                $globalMaxNbCommentaire += $stat['nb_commentaire'];
            }
        }

        $renderArray['poleStats'] = array(
            'nb_sujet' => $maxNbSujet,
            'nb_commentaire' => $maxNbCommentaire,
            'global_nb_sujet' => $globalMaxNbSujet,
            'global_nb_commentaire' => $globalMaxNbCommentaire,
            'stats' => $poleStatsView,
            'monthly' => $globalStatsView,
        );

        $postsStats = $em->getRepository('TerAelisForumBundle:Post')
            ->findMostActivePosts(10);
        $nbCommentSujetMax = 0;
        foreach($postsStats as $p) {
            $nbCommentSujetMax = max($nbCommentSujetMax, $p->getNumberComment());
        }
        $renderArray['sujetsActifs'] = array(
            'sujets' => $postsStats,
            'nb_commentaire_max' => $nbCommentSujetMax,
        );

        $bestUsers = $em->getRepository('TerAelisStatistiquesBundle:MembreStat')
            ->findBestUser(10);
        $renderArray['membresActifs'] = $this->sortBestUsers($bestUsers, $renderArray);

        $bestUsers = $em->getRepository('TerAelisStatistiquesBundle:MembreStat')
            ->findBestUserMonthly(10);
        $renderArray['membresActifsMonthly'] = $this->sortBestUsers($bestUsers, $renderArray);

        $bestUsers = $em->getRepository('TerAelisStatistiquesBundle:MembreStat')
            ->findBestUserLastWeek(10);
        $renderArray['membresActifsWeekly'] = $this->sortBestUsers($bestUsers, $renderArray);

        return $this->render(
            'TerAelisStatistiquesBundle:Statistiques:index.html.twig',
            $renderArray
        );
    }

    private function rootToPole($pole)
    {
        switch($pole) {
            case $this->getParameter('litterature'):
            case 'litterature':
                $result = 'Pôle littéraire';
                break;
            case $this->getParameter('graphisme'):
            case 'graphisme':
                $result = 'Pôle graphique';
                break;
            case $this->getParameter('rolisme'):
            case 'rolisme':
                $result = 'Pôle rôliste';
                break;
            case $this->getParameter('interpole'):
            case 'interpole':
                $result = 'Interpôle';
                break;
            default:
                $result = '';
        }
        return $result;
    }

    private function dateToView($byPeriod, \DateTime $date)
    {
        $result = clone $date;
        switch ($byPeriod) {
            case 'month':
                $result->setTime(0,0,0);
                break;

        }
        return $result;
    }

    /**
     * @param $bestUsers
     * @return array
     */
    public function sortBestUsers($bestUsers)
    {
        $membresActifs = [];
        $nbCommentMaxGlobal = 0;
        $nbSujetMaxGlobal = 0;
        foreach ($bestUsers as $stat) {
            $membresActifs[] = array(
                'user' => $stat[0]->getMembre(),
                'nb_commentaire' => $stat['nb_commentaire'],
                'nb_sujet' => $stat['nb_sujet'],
            );
            $nbCommentMaxGlobal = max($nbCommentMaxGlobal, $stat['nb_commentaire']);
            $nbSujetMaxGlobal = max($nbSujetMaxGlobal, $stat['nb_sujet']);
        }
        return array(
            'total' => $membresActifs,
            'total_comment_max' => $nbCommentMaxGlobal,
            'total_sujet_max' => $nbSujetMaxGlobal
        );
    }
}