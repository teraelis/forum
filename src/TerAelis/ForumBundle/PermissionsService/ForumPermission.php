<?php

namespace TerAelis\ForumBundle\PermissionsService;

use \Doctrine\Bundle\DoctrineBundle\Registry;

class ForumPermission {
    protected $doctrine;
    protected $em;
    protected $usersParameters;

    function __construct(Registry $doctrine, $group_default)
    {
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager();
        $this->groupDefault = $group_default;
    }

    public function getPerm($user) {
        if (!empty($user)) {
            $usrId = $user->getId();
            $perm = $this->getPermission($usrId);
            $perm['userId'] = $usrId;
        } else {
            $perm = $this->getPermissionDefault();
            $perm['userId'] = 0;
        }
        return $perm;
    }

    public function getPermissionDefault() {
        $groupArray = [];
        $groupArray[] = $this->groupDefault;
        $perm = $this->getFromGroupArray($groupArray);
        return $perm;
    }

    public function getPermission($id) {
        $em = $this->em;
        /* On récupère tous les groups dans lequel est l'utilisateur */
        $groups = $em->getRepository('TerAelisUserBundle:Group')->findUsr($id);
        // On fait un array des id des groupes
        $groupArray = [];
        foreach($groups as $group) {
            $groupArray[] = $group[0]->getId();
        }
        $groupArray[] = $this->groupDefault;
        $perm = $this->getFromGroupArray($groupArray);
        return $perm;
    }

    public function getFromGroupArray($groupArray) {
        // On récupère les permissions
        $permissions = $this->em->getRepository('TerAelisForumBundle:Permission')->findByGroups($groupArray);
        // On les traite pour les rendre facilement manipulables
        $perm = array(
            'voirCategorie' => array(),
            'voirSujet' => array(),
            'creerSujet' => array(),
            'creerSpecial' => array(),
            'repondreSujet' => array(),
            'editerMessage' => array(),
            'supprimerMessage' => array(),
            'creerSondage' => array(),
            'voter' => array(),
            'moderer' => array(),
        );
        foreach($permissions as $permission) {
            $id = $permission->getCategorie()->getId();
            if (isset($perm['voirCategorie'][$id])) {
                $perm['voirCategorie'][$id] = $perm['voirCategorie'][$id] || $permission->getVoirCategorie();
                $perm['voirSujet'][$id] = $perm['voirSujet'][$id] || $permission->getVoirSujet();
                $perm['creerSujet'][$id] = $perm['creerSujet'][$id] || $permission->getCreerSujet();
                $perm['creerSpecial'][$id] = $perm['creerSpecial'][$id] || $permission->getCreerSpecial();
                $perm['repondreSujet'][$id] = $perm['repondreSujet'][$id] || $permission->getRepondreSujet();
                $perm['editerMessage'][$id] = $perm['editerMessage'][$id] || $permission->getEditerMessage();
                $perm['supprimerMessage'][$id] = $perm['supprimerMessage'][$id] || $permission->getSupprimerMessage();
                $perm['creerSondage'][$id] = $perm['creerSondage'][$id] || $permission->getCreerSondage();
                $perm['voter'][$id] = $perm['voter'][$id] || $permission->getVoter();
                $perm['moderer'][$id] = $perm['moderer'][$id] || $permission->getModerer();
            } else {
                $voirCategorie =  $permission->getVoirCategorie();
                $voirSujet =  $permission->getVoirSujet();
                $creerSujet =  $permission->getCreerSujet();
                $creerSpecial =  $permission->getCreerSpecial();
                $repondreSujet =  $permission->getRepondreSujet();
                $editerMessage =  $permission->getEditerMessage();
                $supprimerMessage =  $permission->getSupprimerMessage();
                $creerSondage =  $permission->getCreerSondage();
                $voter =  $permission->getVoter();
                $moderer =  $permission->getModerer();
                if (isset($voirCategorie) && $voirCategorie != null) {
                    $perm['voirCategorie'][$id] = $voirCategorie;
                } else {
                    $perm['voirCategorie'][$id] = 0;
                }
                if (isset($voirSujet) && $voirSujet != null) {
                    $perm['voirSujet'][$id] = $voirSujet;
                } else {
                    $perm['voirSujet'][$id] = 0;
                }
                if (isset($creerSujet) && $creerSujet != null) {
                    $perm['creerSujet'][$id] = $creerSujet;
                } else {
                    $perm['creerSujet'][$id] = 0;
                }
                if (isset($creerSpecial) && $creerSpecial != null) {
                    $perm['creerSpecial'][$id] = $creerSpecial;
                } else {
                    $perm['creerSpecial'][$id] = 0;
                }
                if (isset($repondreSujet) && $repondreSujet != null) {
                    $perm['repondreSujet'][$id] = $repondreSujet;
                } else {
                    $perm['repondreSujet'][$id] = 0;
                }
                if (isset($editerMessage) && $editerMessage != null) {
                    $perm['editerMessage'][$id] = $editerMessage;
                } else {
                    $perm['editerMessage'][$id] = 0;
                }
                if (isset($supprimerMessage) && $supprimerMessage != null) {
                    $perm['supprimerMessage'][$id] = $supprimerMessage;
                } else {
                    $perm['supprimerMessage'][$id] = 0;
                }
                if (isset($creerSondage) && $creerSondage != null) {
                    $perm['creerSondage'][$id] = $creerSondage;
                } else {
                    $perm['creerSondage'][$id] = 0;
                }
                if (isset($voter) && $voter != null) {
                    $perm['voter'][$id] = $voter;
                } else {
                    $perm['voter'][$id] = 0;
                }
                if (isset($moderer) && $moderer != null) {
                    $perm['moderer'][$id] = $moderer;
                } else {
                    $perm['moderer'][$id] = 0;
                }
            }
        }
        return $perm;
    }

    /**
     * @param array $array
     * @param int   $categorieId
     * @param array $perm
     * @return boolean
     */
    public function hasAtLeastOneRight($array, $categorieId, $perm)
    {
        $result = false;
        foreach($array as $permName) {
            $result = $this->hasRight($permName, $categorieId, $perm);
            if($result) {
                break;
            }
        }
        return $result;
    }

    public function hasRight($permName, $categorieId, $perm)
    {
        if(array_key_exists($permName, $perm)) {
            $permValues = $perm[$permName];
            if(array_key_exists($categorieId, $permValues)) {
                $permAuthorization = $permValues[$categorieId];
                if(!empty($permAuthorization) && $permAuthorization == 1) {
                    return true;
                }
            }
        }
    }
}