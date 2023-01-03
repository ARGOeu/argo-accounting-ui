<?php
/**
 * Created by PhpStorm.
 * User: frebault
 * Date: 15/12/15
 * Time: 15:30
 */

namespace App\Controller;

use App\Service\AccountingAPIService;
use Lavoisier\Hydrators\EntriesHydrator;
use Lavoisier\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;



class DefaultController extends AbstractController
{



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

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        return $this->redirect("/oauth/logout");
    }





}