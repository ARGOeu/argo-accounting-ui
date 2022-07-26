<?php
/**
 * Created by PhpStorm.
 * User: frebault
 * Date: 15/12/15
 * Time: 15:30
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{


    /**
     * @Route("/user/userInfo", name="userInfo")
     */
    public function userInfoAction(Request $request)
    {

        if ($this->getUser()!=null)
            return $this->render('default/userInfo.html.twig', array(  'user' => $this->getUser()));
        else
            throw new AccessDeniedHttpException();


    }


    /**
     * @Route("/error", name="error")
     */

    public function renderError(Request $request)
    {

        return $this->render('bundles/TwigBundle/Exception/error.html.twig');
    }



    /**
     * @Route("/user/refreshSession", name="refreshSession")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function RefreshSessionAction(Request $request)
    {
        $session = $request->getSession();
        $session->invalidate();
        $this->get('security.token_storage')->setToken(NULL);


        return $this->redirect("userInfo");
    }



}