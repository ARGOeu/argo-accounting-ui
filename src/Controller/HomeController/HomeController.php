<?php

namespace App\Controller\HomeController;


use App\Services\LavoisierService;
use Lavoisier\Exceptions\CurlException;
use Lavoisier\Exceptions\HTTPStatusException;
use Lavoisier\Hydrators\CSVasXMLHydrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route(path="/",  name="homepage")
     */
    public function index(): RedirectResponse
    {
        // redirects to the "homepage" route
        return $this->redirectToRoute('projects');

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
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listProjects(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery = new Query($lavoisierUrl, 'listProjects', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabProjects = $result->getArrayCopy();

        $lavQuery = new Query($lavoisierUrl, 'listProviders', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabProviders = $result->getArrayCopy();


        return $this->render("AccountingMetrics/tableProjects.html.twig", [
            'tabProjects' => $tabProjects,
            'tabProviders' => $tabProviders

        ]);

    }

    /**
     * @Route("/providers",  name="providers")
     *
     * list all providers
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listProviders(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery = new Query($lavoisierUrl, 'listProviders', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabProviders = $result->getArrayCopy();


        return $this->render("AccountingMetrics/tableProviders.html.twig", [
            'tabProviders' => $tabProviders,

        ]);

    }

    /**
     * @Route("/metrics-units",  name="metrics-units")
     *
     * list all metrics units
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsUnits(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        $lavQuery3 = new Query($lavoisierUrl, 'listUnits', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery3->setHydrator($hydrator);
        $result3 = $lavQuery3->execute();
        $tabMetricsUnits = $result3->getArrayCopy();

        return $this->render("AccountingMetrics/tableUnits.html.twig", [
            'tabMetricsUnits' => $tabMetricsUnits,

        ]);

    }

    /**
     * @Route("/metrics-definitions",  name="metrics-definitions")
     *
     * list all metrics-definitions
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsDefinitions(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery = new Query($lavoisierUrl, 'listMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabMetricsDef = $result->getArrayCopy();

        $lavQuery2 = new Query($lavoisierUrl, 'listMetricType', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery2->setHydrator($hydrator);
        $result2 = $lavQuery2->execute();
        $tabMetricsTypes = $result2->getArrayCopy();

        $lavQuery3 = new Query($lavoisierUrl, 'listUnits', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery3->setHydrator($hydrator);
        $result3 = $lavQuery3->execute();
        $tabMetricsUnits = $result3->getArrayCopy();

        return $this->render("AccountingMetrics/tableDefinitions.html.twig", [
            'tabMetricsDef' => $tabMetricsDef,
            'tabMetricsUnits' => $tabMetricsUnits,
            'tabMetricsTypes' => $tabMetricsTypes
        ]);

    }

    /**
     * @Route("/metrics-types",  name="metrics-types")
     *
     * list all metrics-types
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsTypes(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery = new Query($lavoisierUrl, 'listMetricType', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabMetricsTypes = $result->getArrayCopy();


        return $this->render("AccountingMetrics/tableTypes.html.twig", [
            'tabMetricsTypes' => $tabMetricsTypes
        ]);

    }

    /**
     * @Route("/installations",  name="installations")
     *
     * list all installations
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listInstallations(Request $request, LavoisierService $lavoisierService)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery = new Query($lavoisierUrl, 'listInstallations', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabInstallations = $result->getArrayCopy();

        $lavQuery = new Query($lavoisierUrl, 'listProjects', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabProjects = $result->getArrayCopy();

        $lavQuery5 = new Query($lavoisierUrl, 'listProviders', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery5->setHydrator($hydrator);
        $result5 = $lavQuery5->execute();
        $tabProviders = $result5->getArrayCopy();

        $lavQuery = new Query($lavoisierUrl, 'listMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabMetricsDef = $result->getArrayCopy();


        return $this->render("AccountingMetrics/tableInstallations.html.twig", [
            'tabInstallations' => $tabInstallations,
            'tabProjects' => $tabProjects,
            'tabProviders' => $tabProviders,
            'tabMetricsDef' => $tabMetricsDef
        ]);

    }

    /**
     * @Route("/metrics",  name="metrics")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetrics(Request $request, LavoisierService $lavoisierService)
    {

        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        $lavQuery = new Query($lavoisierUrl, 'listProjects', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabProjects = $result->getArrayCopy();


        $lavQuery5 = new Query($lavoisierUrl, 'listProviders', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery5->setHydrator($hydrator);
        $result5 = $lavQuery5->execute();
        $tabProviders = $result5->getArrayCopy();

        $lavQuery = new Query($lavoisierUrl, 'listInstallations', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabInstallations = $result->getArrayCopy();

        $details = 0;
        $tabMetricsDetails = [];
        $array_POST = [];
        $project = 0;
        $provider = 0;
        $installation = 0;

        if ($request->request->get('case') == 1) {
            $details = 1;
            $project = $request->request->get('project');
        }
        if ($request->request->get('case') == 2) {
            $details = 2;
            $project_provider = $request->request->get('project_provider');
            $project = explode('___', $project_provider)[0];
            $provider = explode('___', $project_provider)[1];

        }

        if ($request->request->get('case') == 3) {
            $details = 3;
            $installation = $request->request->get('installation');
        }


        if ($details >=1) {

            $array_POST = [
                "projectId" => $project,
                "provider" => $provider,
                "installation" => $installation,
                "details" => $details
            ];

            $lavQuery_details = new Query($lavoisierUrl, 'listMetricsDetails', 'lavoisier', 'xml', $lavoisierPort);
            $lavQuery_details->setHydrator($hydrator);
            $lavQuery_details ->setMethod('POST');
            $lavQuery_details ->setPostFields($array_POST);


                try {
                    $res_details  = $lavQuery_details->execute();
                    $tabMetricsDetails=$res_details->getArrayCopy();


                } catch (CurlException $e) {
                } catch (HTTPStatusException $e) {
                    return new Response("Exception".$e, 500);
                }

        }


        return $this->render("AccountingMetrics/tableMetrics.html.twig", [
            'tabProviders' => $tabProviders,
            'tabProjects' => $tabProjects,
            'tabInstallations' => $tabInstallations,
            'tabMetricsDetails' => $tabMetricsDetails,
            'parameters'=> $array_POST,
            'details'=>$details
        ]);

    }

    /**
     * @Route("/metricsEOSC/ajax/dissociatesProviders",  name="dissociates_providers")
     *
     * ajax calls to dissociates providers to a project
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function dissociatesProviders(LavoisierService $lavoisierService, Request $request)
    {

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $array_POST = [
            'project' => $request->get('project'),
            'provider' => $request->get('provider'),
        ];


        $lavQuery = new Query($lavoisierUrl, 'dissociatesProvider', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The provider has been removed';
        $message2 = 'The dissociation of the provider has failed';

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();
            sleep(1);


            $lavQueryNotify = new Query($lavoisierUrl, 'listProjects', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(3);

            return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

    }

    /**
     * @Route("/ajax/deleteInstallation",  name="delete_installation")
     *
     * ajax calls to remove installation
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function deleteInstallation(LavoisierService $lavoisierService, Request $request)
    {

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $array_POST = [
            'installation_id' => $request->get('installation_id'),
        ];


        $lavQuery = new Query($lavoisierUrl, 'deleteInstallation', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The installation has been removed successfully :' . serialize($array_POST);
        $message2 = 'The deletion has failed :' . serialize($array_POST);

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();


            sleep(3);

            $lavQueryNotify = new Query($lavoisierUrl, 'listInstallations', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(3);

            return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

    }

    /**

     * @Route("/ajax/addInstallation",  name="add_installation")
     *
     * ajax calls to add installation
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function addInstallation(LavoisierService $lavoisierService, Request $request)
    {

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $array_POST = [
            'project' => $request->get('project'),
            'infrastructure' => $request->get('infrastructure'),
            'organisation' => $request->get('provider'),
            'installation' => $request->get('installation'),
            'unit_of_access' => $request->get('metric_definition')
        ];


        $lavQuery = new Query($lavoisierUrl, 'addInstallation', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The new installation has been added successfully ';
        $message2 = 'The creation of a new installation has failed ';

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();
            sleep(2);

            $lavQueryNotify = new Query($lavoisierUrl, 'listInstallations', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(4);


            return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

    }

    /**
     * @Route("/ajax/associatesProviders",  name="associates_providers" )
     *
     * ajax calls to associates providers to a project
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function associatesProviders(LavoisierService $lavoisierService, Request $request)
    {

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $array_POST = [
            'project' => $request->get('project'),
            'provider' => $request->get('provider'),
        ];


        $lavQuery = new Query($lavoisierUrl, 'associatesProvider', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The new provider(s) has been added successfully';
        $message2 = 'The creation of a new provider has failed';

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();


            sleep(2);


            $lavQueryNotify = new Query($lavoisierUrl, 'listProjects', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(4);

            return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

    }

    /**
     * @Route("/ajax/addProvider",  name="add_provider" )
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function addProvider(LavoisierService $lavoisierService, Request $request)
    {


        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        $array_POST = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'website' => $request->get('website'),
            'abbreviation' => $request->get('abbreviation'),
            'logo' => $request->get('logo')];


        $lavQuery = new Query($lavoisierUrl, 'publishProvider', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The new provider has been added successfully';
        $message2 = 'The creation of a new provider has failed';

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();

            sleep(3);


            $lavQueryNotify = new Query($lavoisierUrl, 'listProviders', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(3);


            return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

    }

    /**
     * @Route("/ajax/modifyMetricsDescription",  name="modify_metric_description" )
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function modifyMetricDescription(LavoisierService $lavoisierService, Request $request)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        if ($request->get('type') === 'addition') {
            $array_POST = [
                'metric_name' => $request->get('metricName'),
                'metric_description' => $request->get('metricDescription'),
                'unit_type' => $request->get('metricUnit'),
                'metric_type' => $request->get('metricType')];


            $lavQuery = new Query($lavoisierUrl, 'publishMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
            $message1 = 'A new metric definition has been added successfully';
            $message2 = 'The addition of a new metric definition has failed';

        } else {
            if ($request->get('type') === 'update') {
                $array_POST = [
                    'metric_id' => $request->get('metricId'),
                    'metric_name' => $request->get('metricName'),
                    'metric_description' => $request->get('metricDescription'),
                    'unit_type' => $request->get('metricUnit'),
                    'metric_type' => $request->get('metricType')];

                $lavQuery = new Query($lavoisierUrl, 'patchMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
                $message1 = 'The metric definition has been updated successfully';
                $message2 = 'The metric definition update has failed';


            } // last case : delete
            else {
                $array_POST = [
                    'metric_id' => $request->get('metricId')];
                $lavQuery = new Query($lavoisierUrl, 'deleteMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
                $message1 = 'The metric definition has been deleted successfully';
                $message2 = 'The deletion is not possible. Some metrics are attached to the metric definition';

            }
        }

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res = $lavQuery->execute();

            $lavQueryNotify = new Query($lavoisierUrl, 'listMetricDefinition', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();
            if ($request->get('type') === 'addition')
                sleep(3);


            $code = simplexml_load_string($res);


            if (str_contains($code, '409'))
                return new Response('<em>The deletion is not possible. <br/>Some metrics are attached to the metric definition</em>', 500);
            else
                return new Response('<em>' . $message1 . '</em>', 200);

        } catch (\Exception $exception) {
            return new Response('<em>' . $message2 . '</em>', 500);
        }

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