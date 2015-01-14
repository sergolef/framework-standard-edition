<?php

namespace Rest\Bundles\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

//use FOS\RestBundle\Controller\Annotations\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Rest\Bundles\ApiBundle\Entity\User;

class AuthController extends Controller
{
    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    protected function loginUser(User $user)
    {
        $security = $this->get('security.context');
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $roles = $user->getRoles();
        $token = new UsernamePasswordToken($user, null, $providerKey, $roles);
        $security->setToken($token);
    }

    protected function logoutUser()
    {
        $security = $this->get('security.context');
        $token = new AnonymousToken(null, new User());
        $security->setToken($token);
        $this->get('session')->invalidate();
    }

    protected function checkUser()
    {
        $security = $this->get('security.context');

        if ($token = $security->getToken()) {
            $user = $token->getUser();
            if ($user instanceof User) {
                return $user;
            }
        }

        return false;
    }

    protected function checkUserPassword(User $user, $password)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        if(!$encoder){
            return false;
        }
        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }

    /**
    * @Route("/auth/login.{_format}", name="domain_upsell_api_auth_login", defaults={"_format" = "json"})
    * @Method("POST")
    */
    public function loginAction()
    {
        $request = $this->getRequest();
        $username = $request->get('username');
        $password = $request->get('password');

        $um = $this->getUserManager();
        $user = $um->findUserByUsername($username);
    //    var_dump($user);
    //    exit;
        if(!$user){
            $user = $um->findUserByEmail($username);
        }

        if(!$user instanceof User){
            throw new NotFoundHttpException('Username or password is broken');
        }
        if(!$this->checkUserPassword($user, $password)){
            throw new AccessDeniedException('Username or password is broken');
        }

        $this->loginUser($user);
        return array('success' => true, 'user' => $user);
    }

    /**
    * @Route("/auth/logout.{_format}", name="domain_upsell_api_auth_logout", defaults={"_format" = "json"})
    * @Method("POST")
    */
    public function logoutAction()
    {
        $this->logoutUser();
        return array('success' => true);
    }

    /**
    * @Route("/auth/loginCheck.{_format}", name="domain_upsell_api_auth_login_check", defaults={"_format" = "json"})
    * @Method("POST")
    */
    public function loginCheckAction()
    {
        if ($user = $this->checkUser()) {
            return array(
                'success' => true,
                'user' => $user
            );
        } else {
            throw new AccessDeniedException();
        }
    }
}
