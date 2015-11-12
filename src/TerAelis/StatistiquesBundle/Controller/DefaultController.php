<?php

namespace TerAelis\StatistiquesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TerAelisStatistiquesBundle:Default:index.html.twig', array('name' => $name));
    }
}
