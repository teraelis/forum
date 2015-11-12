<?php

namespace TerAelis\StatistiquesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use TerAelis\ForumBundle\Entity\Categorie;

class ViewsController extends Controller
{
    public function postView($ip, $categorie, $post = null) {
        $em = $this->getDoctrine()->getManager();
        $viewRepo = $em->getRepository('TerAelisStatistiquesBundle:View');
        $view = $viewRepo->findOneBy(array(
            'ip' => $ip,
            'categorie' => $categorie,
            'post' => $post,
        ));

        if($view == null) {
            $view = new View($ip, $post);
        } else {
            $view->setCount($view->getCount() + 1);
        }

        $em->persist($view);
        $em->flush();
        return true;
    }

    public function deleteView($id) {
        $em = $this->getDoctrine()->getManager();
        $viewRepo = $em->getRepository('TerAelisStatistiquesBundle:View');
        $view = $viewRepo->findOneBy(array(
            'id' => $id
        ));
        if($view != null) {
            $em->remove($view);
            $em->flush();
        }
        return true;
    }
}
