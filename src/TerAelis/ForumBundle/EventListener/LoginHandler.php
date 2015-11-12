<?php
namespace TerAelis\ForumBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginHandler implements AuthenticationSuccessHandlerInterface {
    private $router;

    /**
     * LoginHandler constructor.
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $session = $request->getSession();
        if($session->has('_security.main.target_path')) {
            $target_path = $session->get('_security.main.target_path');
            if (!empty($target_path)) {
                return new RedirectResponse($target_path);
            }
        } else if($session->has('pole_aff')) {
            $pole = $session->get('pole_aff');
            return new RedirectResponse($this->router->generate(
                'taforum_forum',
                array(
                    'pole' => $pole,
                )
            ));
        } else {
            return new RedirectResponse('/');
        }
    }
}
