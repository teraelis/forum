<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class OrientationController extends Controller
{
    public function indexAction() {
        return $this->render('TerAelisForumBundle:Orientation:index.html.twig');
    }
}