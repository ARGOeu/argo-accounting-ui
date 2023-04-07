<?php

namespace App\Controller\HomeController;


use App\Service\AccountingService;
use App\Service\LavoisierService;
use http\Client;
use Lavoisier\Exceptions\CurlException;
use Lavoisier\Exceptions\HTTPStatusException;
use Lavoisier\Hydrators\CSVasXMLHydrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Home\Type\MailType;
use App\Entity\Home\Mail;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Component\HttpFoundation\Response;

use Lavoisier\Query;
use Lavoisier\Hydrators\EntriesHydrator;
use Lavoisier\Exceptions;


use Symfony\Component\HttpFoundation\RedirectResponse;


class HomeController extends AbstractController
{

    /**
     * @var $container \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var AccountingService
     */
    private $api;



    /**
     * @Route("/user/userInfo", name="userInfo")
     */
    public function userInfoAction(Request $request,AccountingService $api)    {


        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

       $permissions=$api->getUserPermissions($bearerToken,false);
        $globalPermissions=$api->getUserGeneralPermissions($bearerToken);


        if (count($permissions)>=1) {
            return $this->render('default/userInfo.html.twig', array('user' => $this->getUser(), 'listEntities' => $permissions, 'globalPermissions'=>$globalPermissions));
        }
        else {
            return $this->render("AccountingMetrics/noPermissions.html.twig");

        }
    }


    /**
     * @Route(path="/",  name="homepage")
     */
    public function index()
    {

        return $this->render("/home/home.html.twig");
    }

    /**
     * @Route("/home/data_protection", name="data_protection")
     */
    public function DataProtectionAction()
    {
        return $this->render("/home/DataProtection.html.twig");

    }

    /**
     * @Route("/home/termsofuse", name="termsofuse")
     */
    public function TermsUseAction()
    {
        return $this->render("/home/TermsOfUse.html.twig");

    }

    /**
     * @Route("/projects",  name="projects")
     *
     * list all projetcs
     * @return Response
     */

    public function listProjects(AccountingService $api, Request $request)
    {


        $status = $request->request->get('status');
        $message = $request->request->get('message');

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }


        $tabProviders=$api->getRessources('providers',$bearerToken);
        $permissions=$api->getUserPermissions($bearerToken,false);



        if (count($permissions)>=1) {
            return $this->render("AccountingMetrics/tableProjects.html.twig", [

               'tabProviders' => $tabProviders,
                'permissions' => $permissions,
                "message" => $message,
                "status" => $status
            ]);
        }
        else {
            return $this->render("AccountingMetrics/noPermissions.html.twig");

        }

    }

    /**
     * @Route("/myProviders",  name="my_providers")
     *
     * list all providers
     * @return Response
     */

    public function listmyProviders( AccountingService $api, Request $request)
    {

        $status = $request->request->get('status');
        $message = $request->request->get('message');



        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $permissions=$api->getUserPermissions($bearerToken,false);


        $id=$this->getUser()->getUserIdentifier();


        return $this->render("AccountingMetrics/tableMyProviders.html.twig", [

                'permissions'=>$permissions,
                'userId'=>$id,
                'status'=>$status,
                'message'=>$message

            ]);



    }

    /**
     * @Route("/providers",  name="providers")
     *
     * list all providers
     * @return Response
     */

    public function listProviders( AccountingService $api, Request $request)
    {

        $status = $request->request->get('status');
        $message = $request->request->get('message');



        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $permissions=$api->getUserGeneralPermissions($bearerToken);
        $id=$this->getUser()->getUserIdentifier();

        if (count($permissions)>=1) {
        $tabProviders=$api->getRessources('providers',$bearerToken);
        return $this->render("AccountingMetrics/tableProviders.html.twig", [
            'tabProviders' => $tabProviders,
            'permissions'=>$permissions,
            'userId'=>$id,
            'status'=>$status,
            'message'=>$message

        ]);
    }
        else {
            return $this->render("AccountingMetrics/noPermissions.html.twig");

        }

    }

    /**
     * @Route("/installations",  name="installations")
     *
     * list all installations
     * @return Response
     */

    public function listInstallations(AccountingService $api, Request $request)
    {

        $status = $request->request->get('status');
        $message = $request->request->get('message');

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabMetricsDef=$api->getRessources('metric-definitions',$bearerToken);
        $permissions=$api->getUserPermissions($bearerToken,false);
      //  $tabResources=$api->getRessources('resources',$bearerToken);
        $tabInstallations=$api->getRessources('installations',$bearerToken);
        $tabPrefRes=array();
        $tabResources=array();
        foreach ($tabInstallations as $installations) {
            foreach ($installations as $installation) {
                if (isset($installation["resource"]))
                $tabPrefRes[$installation["id"]]=$installation["resource"];
                else
                    $tabPrefRes[$installation["id"]]='N.A';
            }

        }

        if (count($permissions)>=1) {
            return $this->render("AccountingMetrics/tableInstallations.html.twig", [
                'tabMetricsDef' => $tabMetricsDef,
                "permissions" => $permissions,
                "tabResources" => $tabResources,
                "message" => $message,
                "status" => $status,
                "tabPrefRes"=>$tabPrefRes
            ]);
        } else {
            return $this->render("AccountingMetrics/noPermissions.html.twig");
        }

    }

    /**
     * @Route("/unit-types",  name="unit-types")
     *
     * list all metrics units
     * @return Response
     */

    public function listMetricsUnits(Request $request, AccountingService $api): Response
    {


        $status = $request->request->get('status');
        $message = $request->request->get('message');

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $permissions=$api->getUserGeneralPermissions($bearerToken);
        $tabMetricsUnits=$api->getRessources('unit-types',$bearerToken);

        return $this->render("AccountingMetrics/tableUnits.html.twig", [
            'tabMetricsUnits' => $tabMetricsUnits,
            'permissions'=>$permissions,
            "message"=>$message,
            "status"=> $status,

        ]);

    }

    /**
     * @Route("/metrics-definitions",  name="metrics-definitions")
     *
     * list all metrics-definitions
     * @return Response
     */

    public function listMetricsDefinitions(Request $request, AccountingService $api)
    {

        $status = $request->request->get('status');
        $message = $request->request->get('message');

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabMetricsUnits=$api->getRessources('unit-types',$bearerToken);
        $tabMetricsDef=$api->getRessources('metric-definitions',$bearerToken);
        $tabMetricsTypes=$api->getRessources('metric-types',$bearerToken);
        $permissions=$api->getUserGeneralPermissions($bearerToken);

        return $this->render("AccountingMetrics/tableDefinitions.html.twig", [
            'tabMetricsDef' => $tabMetricsDef,
            'tabMetricsUnits' => $tabMetricsUnits,
            'tabMetricsTypes'=>$tabMetricsTypes,
            "message"=>$message,
            "status"=> $status,
            "userId"=>$this->getUser()->getUserIdentifier(),
            "permissions"=>$permissions

        ]);

    }

    /**
     * @Route("/metric-types",  name="metric-types")
     *
     * list all metrics-types
     * @return Response
     */

    public function listMetricsTypes(Request $request,AccountingService $api)
    {
        $status = $request->request->get('status');
        $message = $request->request->get('message');

       $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
       $permissions=$api->getUserGeneralPermissions($bearerToken);




        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabMetricsTypes=$api->getRessources('metric-types',$bearerToken);

        return $this->render("AccountingMetrics/tableTypes.html.twig", [
            'tabMetricsTypes' => $tabMetricsTypes,
            'permissions'=>$permissions,
            "message"=>$message,
            "status"=> $status,
        ]);

    }


    /**
     * @Route("/add_metrics",  name="add_metrics")
     *
     * form to get metrics provider list
     * @return Response
     */
    public function addMetrics(Request $request, AccountingService $api)
    {
        $status = $request->request->get('status');
        $message = $request->request->get('message');

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }
        $tabProjects=$api->getRessources('projects',$bearerToken);
        $tabInstallations=$api->getRessources('installations',$bearerToken);
        $tabMetricsDef=$api->getRessources('metric-definitions',$bearerToken);
        $permissions=$api->getUserPermissions($bearerToken);

        if (count($permissions)>=1) {
            return $this->render("AccountingMetrics/addMetrics.html.twig", [
                'tabInstallations' => $tabInstallations,
                'tabProjects' => $tabProjects,
                'tabMetricsDef' => $tabMetricsDef,
                "permissions" => $permissions,
                "message" => $message,
                "status" => $status
            ]);
        } else {
            return $this->render("AccountingMetrics/noPermissions.html.twig");

        }

    }

    /**
     * @Route("/metrics",  name="metrics")
     *
     * form to get metrics provider list
     * @return Response
     */

    public function listMetrics(Request $request, AccountingService $api)
    {

        $details=0;
        $listIds=0;
        $tabMetricsDetails=null;
        if ($request->request->has('parameters'))
            $parameters= json_decode($request->request->get('parameters'));
        else
            $parameters="";
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $permissions=$api->getUserPermissions($bearerToken,false);

            if ($parameters!=='') {
                $tabMetricsDetails=$api->searchRessources('metrics',$parameters,$bearerToken);
                $details=1;
                $listIds=json_decode($request->request->get('listIds'),true);
            }

           $tabInstallations=$api->getRessources('installations',$bearerToken);

       return $this->render("AccountingMetrics/tableMetrics.html.twig", ["listIds"=>$listIds,
           "tabInstallations"=>$tabInstallations,
           "listEntities"=>$permissions ,
          "parameters"=>$parameters,
           'details'=>$details,'tabMetricsDetails'=>$tabMetricsDetails]);

    }

    /**
     * @Route("/ajax/modifyProvidersProject",  name="modify_providers_project")
     *
     * ajax calls to dissociates/associate providers to a project
     * @return JsonResponse
     */
    public function modifyProviders( Request $request,AccountingService $api)
    {
        $providers=explode(",",substr($request->get('provider'),1),);
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $response=$api->modifyProviders($request->get('mode'), $request->get('project'),$providers,$bearerToken);
        return new JsonResponse($response);
    }


    /**
     * @Route("/ajax/modifyMetric",  name="modify_one_metric")
     *
     * ajax calls to add a signel metric
     * @return Response
     */
    public function modifyOneMetric(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        $body =$request->request->all();
        unset($body['installation_id']);
        unset($body['type']);

        if ($request->get('type')==='add')
            $response=$api->addRessource('/installations/'.$request->get('installation_id').'/metrics',$body,$bearerToken);

        if ($request->get('type')==='update') {
            unset($body['id']);
            $response = $api->updateRessource('/installations/'.$request->get('installation_id').'/metrics/'.$request->get('id'),$body,$bearerToken);
        }



        return new JsonResponse($response);
    }

    /**
     * @Route("/ajax/deleteMetric",  name="delete_one_metric")
     *
     * ajax calls to add a signel metric
     * @return Response
     */
    public function deleteOneMetric(AccountingService $api, Request $request)
    {

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $response=$api->deleteRessource('/installations/'.$request->get('installation_id').'/metrics/'.$request->get('id'),$bearerToken);
        return new JsonResponse($response);
    }

    /**
     * @Route("/ajax/deleteProvider",  name="delete_provider")
     *
     * ajax calls to remove provider
     * @return Response
     */
    public function deleteProvider(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $response=$api->deleteRessource('/providers/'.$request->get('provider_id'),$bearerToken);
        return new JsonResponse($response);
    }



    /**

     * @Route("/ajax/modifyProvider",  name="modify_provider")
     *
     * ajax calls to add  provider
     * @return Response
     */
    public function modifyProvider(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        $body =$request->request->all();
        unset($body['type']);

       if ($request->get('type')==='add')
            $response=$api->addRessource('/providers',$body,$bearerToken );

        if ($request->get('type')==='update') {
            unset($body['id']);
            $response = $api->updateRessource('/providers/' . $request->get('id'), $body, $bearerToken);
        }

        if ($request->get('type')==='delete')
            $response=$api->deleteRessource('/providers/'.$request->get('id'),$bearerToken );

        return new JsonResponse($response);
    }

    /**
     * @Route("/ajax/modifyInstallation",  name="modify_installation" )
     *
     *
     * @return Response
     */

    public function modifyInstallation(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $body =$request->request->all();
        unset($body['type']);

        if ($request->get('type')==='add') {
            $response = $api->addRessource('/installations', $body, $bearerToken);
        }
        if ($request->get('type')==='update') {
            unset($body['installation_id']);
            $response = $api->updateRessource('/installations/' . $request->get('installation_id'),$body, $bearerToken);
        }

        if ($request->get('type')==='delete') {
            $response = $api->deleteRessource('/installations/' . $request->get('installation_id'), $bearerToken);
        }
        return new JsonResponse($response);


    }



    /**
     * @Route("/ajax/modifyMetricsDescription",  name="modify_metric_description" )
     *
     * form to get metrics provider list
     * @return Response
     */

    public function modifyMetricDescription(AccountingService $api, Request $request)
    {

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $body =$request->request->all();
        unset($body['type']);


        if ($request->get('type') === 'add') {
            $response=$api->addRessource('/metric-definitions',$body,$bearerToken );
        } else {
            if ($request->get('type') === 'update') {
                unset($body['metric_id']);
                $response=$api->updateRessource('/metric-definitions/'.$request->get('metric_id'),$body,$bearerToken );
            } // last case : delete
            else {
                $response=$api->deleteRessource('/metric-definitions/'.$request->get('metric_id'),$bearerToken);
            }

        }

        return new JsonResponse($response);


    }

    /**
     * @Route("/ajax/modifyMetricType",  name="modify_metric_type" )
     *
     * form to get metrics provider list
     * @return Response
     */

    public function modifyMetricType(AccountingService $api, Request $request)
    {

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $body =$request->request->all();
        unset($body['type']);


        if ($request->get('type') === 'add') {
            $response=$api->addRessource('/metric-types',$body,$bearerToken );
        } else {
            if ($request->get('type') === 'update') {
                unset($body['id']);
                $response=$api->updateRessource('/metric-types/'.$request->get('id'),$body,$bearerToken );
            } // last case : delete
            else {
                $response=$api->deleteRessource('/metric-types/'.$request->get('id'),$bearerToken);
            }
        }
        return new JsonResponse($response);

    }


    /**
     * @Route("/ajax/modifyUnit",  name="modify_unit" )
     *
     * form to get metrics provider list
     * @return Response
     */

    public function modifyUnit(AccountingService $api, Request $request)
    {

        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $body =$request->request->all();
        unset($body['type']);


        if ($request->get('type') === 'add') {
            $response=$api->addRessource('/unit-types',$body,$bearerToken );
        } else {
            if ($request->get('type') === 'update') {
                unset($body['id']);
                $response=$api->updateRessource('/unit-types/'.$request->get('id'),$body,$bearerToken );
            } // last case : delete
            else {
                $response=$api->deleteRessource('/unit-types/'.$request->get('id'),$bearerToken);
            }

        }

        return new JsonResponse($response);


    }


    /**
     * @Route("/metricsbyEntities",  name="metrics_by_entity")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsbyEntity(AccountingService $api, Request $request)
    {

        $status = $request->request->get('status');
        $message = $request->request->get('message');
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $parameters=[];

        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }
        $tabInstallations=$api->getRessources('installations',$bearerToken);
        $tabMetricsDef=$api->getRessources('metric-definitions',$bearerToken);

        if ($request->get('type') === 'providers')
        {
            $url='/projects/'.$request->get('project_id').'/'.$request->get('type') .'/'.$request->get('id').'/metrics';
            $parameters['type']=$request->get('type');
            $parameters['id']=$request->get('id');
            $parameters['project_id']=$request->get('project_id');

        }
        else {
            $url='/'.$request->get('type') .'/'.$request->get('id').'/metrics';
            $parameters['type']=$request->get('type');
            $parameters['id']=$request->get('id');
            $parameters['project_id']=$request->get('project_id');

        }

        $permissions=$api->getUserPermissions($bearerToken,false);
        $tabMetricsDetails=$api->getRessources($url,$bearerToken,true);

        return $this->render("AccountingMetrics/tableMetricsDetails.html.twig", [
            'tabMetricsDetails' => $tabMetricsDetails,
            'tabInstallations'=>$tabInstallations,
            'tabMetricsDef'=>$tabMetricsDef,
            'permissions'=>$permissions,
            'parameters'=>$parameters,
            "status"=>$status,
            "message"=>$message

        ]);

    }


}