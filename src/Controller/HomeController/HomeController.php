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
        return $this->redirectToRoute('metrics');

    }




    /**
     * @Route("/metrics",  name="metrics")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetrics(Request $request,LavoisierService $lavoisierService)
    {

        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");

        $lavQuery= new Query($lavoisierUrl, 'listMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setHydrator($hydrator);
        $result = $lavQuery->execute();
        $tabMetricsDef=$result->getArrayCopy();


        $lavQuery2= new Query($lavoisierUrl, 'listMetricType', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery2->setHydrator($hydrator);
        $result2 = $lavQuery2->execute();
        $tabMetricsTypes=$result2->getArrayCopy();

        $lavQuery3= new Query($lavoisierUrl, 'listUnits', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery3->setHydrator($hydrator);
        $result3 = $lavQuery3->execute();
        $tabMetricsUnits=$result3->getArrayCopy();

        $lavQuery4= new Query($lavoisierUrl, 'listProjects', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery4->setHydrator($hydrator);
        $result4 = $lavQuery4->execute();
        $tabProjects=$result4->getArrayCopy();

        $lavQuery5= new Query($lavoisierUrl, 'listProviders', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery5->setHydrator($hydrator);
        $result5 = $lavQuery5->execute();
        $tabProviders=$result5->getArrayCopy();

        $project="-- Project --";
        $provider="-- Provider --";
        $tabMetricsProject=null;
        $tabMetricsProvider=null;



        if ($request->request->get('form') == 1) {
                $project=$request->request->get('projectName');
            $provider=$request->request->get('providerName');

            if ($project!=0) {
                $lavQuery = new Query($lavoisierUrl, 'listMetricsbyProject', 'lavoisier', 'xml', $lavoisierPort);
                $lavQuery->setMethod('POST');
                $lavQuery->setPostFields(array('projectId' => $project));
                $lavQuery->setHydrator($hydrator);
                try {
                    $result = $lavQuery->execute();
                } catch (CurlException $e) {
                } catch (HTTPStatusException $e) {
                    return new Response("Exception", 500);
                }
                $tabMetricsProject=$result->getArrayCopy();

            }

        }

        return $this->render("AccountingMetrics/AccountingMetrics.html.twig", [
            'tabMetricsDef'=>$tabMetricsDef,
            'tabMetricsTypes' =>$tabMetricsTypes,
            'tabMetricsUnits' =>$tabMetricsUnits,
            'tabProjects' =>$tabProjects,
            'tabProviders' =>$tabProviders,
            'project'=>$project,
            'provider' => $provider,
            'tabMetricsProject'=>$tabMetricsProject,
            'tabMetricsProvider'=>$tabMetricsProvider
        ]);

    }


    /**
     * @Route("/metricsEOSC/ajax/addProvider",  name="add_provider")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */
    public function addProvider(LavoisierService $lavoisierService, Request $request)
    {
        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");



        $array_POST = [
            'id' => $request->get('id'),
            'name' => $request->get('name'),
            'website' => $request->get('website'),
            'abbreviation' => $request->get('abbreviation'),
            'logo'=>$request->get('logo')];


        $lavQuery = new Query($lavoisierUrl, 'publishProvider', 'lavoisier', 'xml', $lavoisierPort);
        $message1 = 'The new provider has been added successfully';
        $message2 = 'The creation of a new provider has failed';

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res=$lavQuery->execute();

            sleep(3);


            $lavQueryNotify = new Query($lavoisierUrl, 'listProviders', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();

            sleep(3);


            return new Response('<em>'.$message1.'</em>',200);

        }
        catch (\Exception $exception) {
            return new Response('<em>'.$message2.'</em>',500);
        }

    }

    /**
     * @Route("/metricsEOSC/ajax/modifyMetricsDescription",  name="modify_metric_description")
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


        if ($request->get('type')==='addition') {
            $array_POST = [
                'metric_name' => $request->get('metricName'),
                'metric_description' => $request->get('metricDescription'),
                'unit_type' => $request->get('metricUnit'),
                'metric_type' => $request->get('metricType')];


            $lavQuery = new Query($lavoisierUrl, 'publishMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
            $message1='A new metric definition has been added successfully';
            $message2='The addition of a new metric definition has failed';

        }
        else {
            if ($request->get('type')==='update') {
                $array_POST = [
                    'metric_id' => $request->get('metricId'),
                    'metric_name' => $request->get('metricName'),
                    'metric_description' => $request->get('metricDescription'),
                    'unit_type' => $request->get('metricUnit'),
                    'metric_type' => $request->get('metricType')];

                $lavQuery = new Query($lavoisierUrl, 'patchMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
                $message1='The metric definition has been updated successfully';
                $message2='The metric definition update has failed';


            } // last case : delete
            else {
                $array_POST = [
                    'metric_id' => $request->get('metricId')];
                $lavQuery = new Query($lavoisierUrl, 'deleteMetricDefinition', 'lavoisier', 'xml', $lavoisierPort);
                $message1='The metric definition has been deleted successfully';
                $message2='The deletion is not possible. Some metrics are attached to the metric definition';

            }
        }

        try {
            $lavQuery->setMethod('POST');
            $lavQuery->setPostFields($array_POST);
            $res=$lavQuery->execute();

            $lavQueryNotify = new Query($lavoisierUrl, 'listMetricDefinition', 'notify', 'xml', $lavoisierPort);
            $lavQueryNotify->execute();
            if ($request->get('type')==='addition')
                sleep(3);

            $code=new SimpleXMLElement($res);

            if (str_contains($code,'409'))
                return new Response('<em>The deletion is not possible. <br/>Some metrics are attached to the metric definition</em>',500);
            else
                return new Response('<em>'.$message1.'</em>',200);

        }
        catch (\Exception $exception) {
            return new Response('<em>'.$message2.'</em>',500);
        }

    }





    /**
     * @Route("/metrics/project/{projectId}",  name="accounting-metrics_byProject")
     *
     * form to get metrics provider list
     * @param LavoisierService $lavoisierService
     * @return Response
     */

    public function listMetricsforProject(LavoisierService $lavoisierService,string $projectId)
    {

        $hydrator = new EntriesHydrator();

        $lavoisierUrl = $this->getParameter('lavoisierUrl');
        $lavoisierPort = $this->getParameter("lavoisierPort");


        $lavQuery= new Query($lavoisierUrl, 'listMetricsbyProject', 'lavoisier', 'xml', $lavoisierPort);
        $lavQuery->setMethod('POST');
        $lavQuery->setPostFields(array('projectId'=>$projectId));
        $lavQuery->setHydrator($hydrator);
        try {
            $result = $lavQuery->execute();
        } catch (CurlException $e) {
        } catch (HTTPStatusException $e) {
            return new Response("Exception",500);
        }
        $tabMetrics=$result->getArrayCopy();




        return $this->render("AccountingMetrics/MetricsProject.html.twig", [
            'tabMetrics'=>$tabMetrics

        ]);

    }


}