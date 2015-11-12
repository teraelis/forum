<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('::index.html.twig');
    }

    public function blogAction()
    {
        return $this->redirect(
            'http://blog.ter-aelis.fr/',
            301
        );
    }
}