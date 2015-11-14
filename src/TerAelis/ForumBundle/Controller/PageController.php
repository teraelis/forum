<?php
namespace TerAelis\ForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use TerAelis\ForumBundle\Entity\Page;
use TerAelis\ForumBundle\Form\PageType;

class PageController extends Controller
{
    public function pageAction($pagePermalink) {
        $page = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Page')
            ->findOneBy(
                array(
                    'pagePermalink' => $pagePermalink
                )
            );

        if (!empty($page)) {
            return $this->render(
                'TerAelisForumBundle:Page:page.html.twig',
                array(
                    'page' => $page,
                )
            );
        } else {
            throw $this->createNotFoundException(
                'La page demandée n\'existe pas.'
            );
        }
    }

    public function adminPagesAction() {
        $pages = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Page')
            ->findAll();

        return $this->render(
            'TerAelisForumBundle:Page:admin_page.html.twig',
            array(
                'pages' => $pages
            )
        );
    }

    public function adminPageEditAction(Request $request, $pagePermalink) {
        $page = $this->get('doctrine.orm.entity_manager')
            ->getRepository('TerAelisForumBundle:Page')
            ->findOneBy(
                array(
                    'pagePermalink' => $pagePermalink,
                )
            );

        $form = $this->createForm(
            new PageType(),
            $page
        )
            ->add('submit', 'submit', array(
                'label' => 'Editer'
            ));

        return $this->processPageForm($request, $form, $page);
    }

    public function adminPageAddAction(Request $request) {
        $form = $this->createForm(
            new PageType()
        )
            ->add('submit', 'submit', array(
             'label' => 'Créer'
            ));

        return $this->processPageForm($request, $form);
    }

    public function adminPageDeleteAction(Request $request, $pagePermalink) {
        $em = $this->get('doctrine.orm.entity_manager');
        $page = $em
            ->getRepository('TerAelisForumBundle:Page')
            ->findOneBy(array(
                'pagePermalink' => $pagePermalink
            ));

        if(empty($page)) {
            return $this->createNotFoundException(
                'Page introuvable'
            );
        }

        $form = $this->createFormBuilder()
            ->add('submit', 'submit', array(
                'label' => 'Confirmer la suppression'
            ))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->remove($page);
            $em->flush();

            return $this->redirect($this->generateUrl('taforum_admin_pages'));
        }

        return $this->render(
            'TerAelisForumBundle:Page:delete_page.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @param Request       $request
     * @param FormInterface $form
     *
     * @param Page          $page
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function processPageForm(Request $request, $form, $page = null) {
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $page = $form->getData();

            $em = $this->get('doctrine.orm.entity_manager');

            $em->persist($page);
            $em->flush();

            return $this->redirect($this->generateUrl('taforum_admin_page', array(
                'pagePermalink' => $page->getPagePermalink(),
            )));
        }

        return $this->render('TerAelisForumBundle:Page:admin_page_edit.html.twig', array(
            'page' => (!empty($page) ? $page : null),
            'form' => $form->createView(),
        ));
    }
}