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

        $tabProjects=$api->getRessources('projects',$bearerToken);
        $tabProviders=$api->getRessources('providers',$bearerToken);
        $permissions=$api->getUserPermissions($bearerToken);



        if (count($permissions)>=1) {
            return $this->render("AccountingMetrics/tableProjects.html.twig", [
                'tabProjects' => $tabProjects,
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
        $tabProjects=$api->getRessources('projects',$bearerToken);
        $tabInstallations=$api->getRessources('installations',$bearerToken);
        $tabMetricsDef=$api->getRessources('metric-definitions',$bearerToken);
        $permissions=$api->getUserPermissions($bearerToken);

        if (count($permissions)>=1) {
            return $this->render("AccountingMetrics/tableInstallations.html.twig", [
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
     * @Route("/metrics-units",  name="metrics-units")
     *
     * list all metrics units
     * @return Response
     */

    public function listMetricsUnits(AccountingService $api): Response
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabMetricsUnits=$api->getRessources('unit-types',$bearerToken);

        return $this->render("AccountingMetrics/tableUnits.html.twig", [
            'tabMetricsUnits' => $tabMetricsUnits,

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
     * @Route("/metrics-types",  name="metrics-types")
     *
     * list all metrics-types
     * @return Response
     */

    public function listMetricsTypes(AccountingService $api)
    {

       $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabMetricsTypes=$api->getRessources('metric-types',$bearerToken);

        return $this->render("AccountingMetrics/tableTypes.html.twig", [
            'tabMetricsTypes' => $tabMetricsTypes
        ]);

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
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $permissions=$api->getUserPermissions($bearerToken,false);

            if ($request->request->has('parameters')) {
                $tabMetricsDetails=$api->searchRessources('metrics',json_decode($request->request->get('parameters'),true),$bearerToken);
                $details=1;
                $listIds=json_decode($request->request->get('listIds'),true);
            }

           $tabInstallations=$api->getRessources('installations',$bearerToken);

       return $this->render("AccountingMetrics/tableMetricsDetails.html.twig", ["listIds"=>$listIds,"tabInstallations"=>$tabInstallations, "listEntities"=>$permissions , 'details'=>$details,'tabMetricsDetails'=>$tabMetricsDetails]);

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
     * @Route("/ajax/deleteProvider",  name="delete_provider")
     *
     * ajax calls to remove installation
     * @return Response
     */
    public function deleteProvider(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $response=$api->deleteRessource('/providers/'.$request->get('provider_id'),$bearerToken);
        return new JsonResponse($response);
    }


    /**
     * @Route("/ajax/deleteInstallation",  name="delete_installation")
     *
     * ajax calls to remove installation
     * @return Response
     */
    public function deleteInstallation(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        $response=$api->deleteRessource('/installations/'.$request->get('installation_id'),$bearerToken);
        return new JsonResponse($response);
    }

    /**

     * @Route("/ajax/addInstallation",  name="add_installation")
     *
     * ajax calls to add installation
     * @return Response
     */
    public function addInstallation(AccountingService $api, Request $request)
    {
        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();

        $body = [
            'project' => $request->get('project'),
            'infrastructure' => $request->get('infrastructure'),
            'organisation' => $request->get('provider'),
            'installation' => $request->get('installation'),
            'unit_of_access' => $request->get('metric_definition')
        ];

        $response=$api->addRessource('/installations',$body,$bearerToken );

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

        $body = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'website' => $request->get('website'),
            'abbreviation' => $request->get('abbreviation'),
            'logo' => $request->get('logo')
        ];

       if ($request->get('type')==='add')
       $response=$api->addRessource('/providers',$body,$bearerToken );

        if ($request->get('type')==='update')
            $response=$api->updateRessource('/providers/'.$request->get('id'),$body,$bearerToken );

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

        $body = [
            'project' => $request->get('project'),
            'infrastructure' => $request->get('infrastructure'),
            'organisation' => $request->get('provider'),
            'installation' => $request->get('installation'),
            'unit_of_access' => $request->get('metric_definition')
        ];

        if ($request->get('type') === 'add') {
            $response=$api->addRessource('/installations',$body,$bearerToken );
        }
        else {
            $response = $api->updateRessource('/installations/' .$request->get('id'),$body,$bearerToken);
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

        if ($request->get('type') === 'addition') {
            $body = [
                'metric_name' => $request->get('metricName'),
                'metric_description' => $request->get('metricDescription'),
                'unit_type' => $request->get('metricUnit'),
                'metric_type' => $request->get('metricType')];

            $response=$api->addRessource('/metric-definitions',$body,$bearerToken );


        } else {
            if ($request->get('type') === 'update') {
                $body = [
                    'metric_id' => $request->get('metricId'),
                    'metric_name' => $request->get('metricName'),
                    'metric_description' => $request->get('metricDescription'),
                    'unit_type' => $request->get('metricUnit'),
                    'metric_type' => $request->get('metricType')];
                $response=$api->updateRessource('/metric-definitions/'.$request->get('metricId'),$body,$bearerToken );
            } // last case : delete
            else {
                $response=$api->deleteRessource('/metric-definitions/'.$request->get('metricId'),$bearerToken);
            }

        }

        return new JsonResponse($response);


    }


    /**
     * @Route("/metrics/project/{projectId}",  name="accounting-metrics_byProject")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsforProject(LavoisierService $lavoisierService, string $projectId)
    {

        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        $lavQuery = new Query($lavoisierUrl, 'listMetricsbyProject', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setMethod('POST');
        $lavQuery->setPostFields(array('projectId' => $projectId));
        $lavQuery->setHydrator($hydrator);
        try {
            $result = $lavQuery->execute();
        } catch (CurlException $e) {
        } catch (HTTPStatusException $e) {
            return new Response("Exception", 500);
        }
        $tabMetrics = $result->getArrayCopy();


        return $this->render("AccountingMetrics/MetricsProject.html.twig", [
            'tabMetrics' => $tabMetrics

        ]);

    }


}