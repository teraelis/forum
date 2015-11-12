<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CookieController extends Controller
{
    public function indexAction(Request $request) {
        $cookies = $request->cookies;
        if(!empty($cookies) && (!$cookies->has('accept_cookies') || !$cookies->get('accept_cookies'))) {
            $render = true;
        } else {
            $render = false;
        }
        return $this->render('TerAelisForumBundle:Cookie:index.html.twig', array('render' => $render));
    }
}