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


    private $json;

    public function __construct()
    {
        $this->json=
        '{
        "size_of_page": 4,
   "number_of_page": 1,
   "total_elements": 4,
   "total_pages": 1,
   "content": [
       {
           "id": "888743",
           "acronym": "DABAT",
           "title": "DNA-sensing by AIM2 in activated B cells: novel targets to improve allogeneic haematopoietic stem cell transplantation",
           "start_date": "2020-10-01",
           "end_date": "2023-09-30",
           "call_identifier": "H2020-MSCA-IF-2019",
           "permissions": [
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Installation"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Provider"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Metric"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "MetricDefinition"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "ASSOCIATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DISSOCIATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Project"
               }
           ],
           "providers": [
               {
                   "id": "etais",
                   "name": "Estonian Scientific Computing Infrastructure",
                   "website": "https://etais.ee",
                   "abbreviation": "ETAIS",
                   "logo": "https://dellingr.neic.no/assets/img/logos/etais.png",
                   "permissions": [],
                   "installations": [
                       {
                           "id": "63562d1642ce693d14efe391",
                           "infrastructure": "infra",
                           "installation": "installation",
                           "unit_of_access": "63562ce6a6b3c27f486bdea3",
                           "permissions": []
                       },
                       {
                           "id": "635bc818dfbbc514301e4144",
                           "infrastructure": "infra_test",
                           "installation": "installation_name_test",
                           "unit_of_access": "635643d842ce693d14efe396",
                           "permissions": []
                       },
                       {
                           "id": "635bc9c4dfbbc514301e4146",
                           "infrastructure": "WoK",
                           "installation": "4b_temporary_installation",
                           "unit_of_access": "6357c360597e7203b51bd4a7",
                           "permissions": []
                       }
                   ]
               },
               {
                   "id": "ubora",
                   "name": "Open Biomedical Engineering e-platform for Innovation through Education",
                   "website": "http://ubora-biomedical.org/",
                   "abbreviation": "UBORA",
                   "logo": "http://ubora-biomedical.org/wp-content/uploads/2017/01/UBORA-Logo-Final-JPEGb.jpg",
                   "permissions": [],
                   "installations": []
               },
               {
                   "id": "umg-br",
                   "name": "University of Minas Gerais",
                   "website": "https://ufmg.br/",
                   "abbreviation": "UMG",
                   "logo": "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/Symbolfumg.jpg/375px-Symbolfumg.jpg",
                   "permissions": [],
                   "installations": []
               },
               {
                   "id": "elixir-uk",
                   "name": "ELIXIR United Kingdom",
                   "website": "https://elixiruknode.org/",
                   "abbreviation": "ELIXIR-UK",
                   "logo": "https://i0.wp.com/elixiruknode.org/wp-content/uploads/2017/10/elixir-site-icon-e1519212356120.png?fit=200%2C150&ssl=1",
                   "permissions": [],
                   "installations": []
               },
               {
                   "id": "predictia",
                   "name": "Predictia Intelligent Data Solutions SL",
                   "website": "https://predictia.es/",
                   "abbreviation": "Predictia",
                   "logo": "https://predictia.es/sites/all/themes/predictia/logo.png",
                   "permissions": [],
                   "installations": []
               }
           ]
       },
       {
           "id": "843702",
           "acronym": "LCxLCProt",
           "title": "Comprehensive two-dimensional liquid chromatography for the characterization of protein biopharmaceuticals at the protein level",
           "start_date": "2020-01-01",
           "end_date": "2020-12-31",
           "call_identifier": "H2020-MSCA-IF-2018",
           "permissions": [],
           "providers": [
               {
                   "id": "etais",
                   "name": "Estonian Scientific Computing Infrastructure",
                   "website": "https://etais.ee",
                   "abbreviation": "ETAIS",
                   "logo": "https://dellingr.neic.no/assets/img/logos/etais.png",
                   "permissions": [],
                   "installations": [
                       {
                           "id": "6356443ca6b3c27f486bdeac",
                           "infrastructure": "infra-etais",
                           "installation": "installation-etais",
                           "unit_of_access": "63562ce6a6b3c27f486bdea3",
                           "permissions": [
                               {
                                   "access_permissions": [
                                       {
                                           "operation": "CREATE",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "DELETE",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "UPDATE",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "READ",
                                           "access_type": "ALWAYS"
                                       }
                                   ],
                                   "collection": "Metric"
                               },
                               {
                                   "access_permissions": [
                                       {
                                           "operation": "DELETE",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "UPDATE",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "ACL",
                                           "access_type": "ALWAYS"
                                       },
                                       {
                                           "operation": "READ",
                                           "access_type": "ALWAYS"
                                       }
                                   ],
                                   "collection": "Installation"
                               }
                           ]
                       }
                   ]
               }
           ]
       },
       {
           "id": "894921",
           "acronym": "PlaGE",
           "title": "Playing at the Gateways of Europe: theatrical languages and performatives practices in the Migrants Reception Centres of the Mediterranean Area",
           "start_date": "2020-10-01",
           "end_date": "2023-09-30",
           "call_identifier": "H2020-MSCA-IF-2019",
           "permissions": [
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Installation"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Provider"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Metric"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "CREATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "UPDATE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "DELETE",
                           "access_type": "ENTITY"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "MetricDefinition"
               },
               {
                   "access_permissions": [
                       {
                           "operation": "ASSOCIATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "DISSOCIATE",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "ACL",
                           "access_type": "ALWAYS"
                       },
                       {
                           "operation": "READ",
                           "access_type": "ALWAYS"
                       }
                   ],
                   "collection": "Project"
               }
           ],
           "providers": [
               {
                   "id": "umg-br",
                   "name": "University of Minas Gerais",
                   "website": "https://ufmg.br/",
                   "abbreviation": "UMG",
                   "logo": "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/Symbolfumg.jpg/375px-Symbolfumg.jpg",
                   "permissions": [],
                   "installations": [
                       {
                           "id": "635902afd3df1a296b28ece3",
                           "infrastructure": "umg-br-infra",
                           "installation": "umg-br-insta",
                           "unit_of_access": "6357c360597e7203b51bd4a7",
                           "permissions": []
                       }
                   ]
               }
           ]
       },
       {
           "id": "101017452",
           "acronym": "OpenAIRE Nexus",
           "title": "OpenAIRE-Nexus Scholarly Communication Services for EOSC users",
           "start_date": "2021-01-01",
           "end_date": "2023-06-30",
           "call_identifier": "H2020-INFRAEOSC-2020-2",
           "permissions": [],
           "providers": [
               {
                   "id": "openaire",
                   "name": "OpenAIRE",
                   "website": "https://www.openaire.eu/",
                   "abbreviation": "OpenAIRE",
                   "logo": "https://www.openaire.eu/images/OpenAIRE_branding/Logo_Horizontal.png",
                   "permissions": [
                       {
                           "access_permissions": [
                               {
                                   "operation": "CREATE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "DELETE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "UPDATE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "ACL",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "READ",
                                   "access_type": "ALWAYS"
                               }
                           ],
                           "collection": "Installation"
                       },
                       {
                           "access_permissions": [
                               {
                                   "operation": "ACL",
                                   "access_type": "ALWAYS"
                               }
                           ],
                           "collection": "Provider"
                       },
                       {
                           "access_permissions": [
                               {
                                   "operation": "CREATE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "DELETE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "UPDATE",
                                   "access_type": "ALWAYS"
                               },
                               {
                                   "operation": "READ",
                                   "access_type": "ALWAYS"
                               }
                           ],
                           "collection": "Metric"
                       }
                   ],
                   "installations": [
                       {
                           "id": "6357c309597e7203b51bd4a6",
                           "infrastructure": "other",
                           "installation": "test-installation",
                           "unit_of_access": "6357c2a9597e7203b51bd4a5",
                           "permissions": []
                       }
                   ]
               }
           ]
       }
   ],
   "links": []
}';
    }

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


        return $this->render('default/userInfo.html.twig', array('user'=>$this->getUser(),'listEntities'=>$permissions));

    }


    /**
     * @Route(path="/",  name="homepage")
     */
    public function index(): RedirectResponse
    {
        // redirects to the "homepage" route
        return $this->redirectToRoute('projects');

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

        return $this->render("AccountingMetrics/tableProjects.html.twig", [
            'tabProjects' => $tabProjects,
            'tabProviders' => $tabProviders,
            'permissions'=>$permissions,
            "message"=>$message,
            "status"=> $status

        ]);

    }

    /**
     * @Route("/providers",  name="providers")
     *
     * list all providers
     * @return Response
     */

    public function listProviders( AccountingService $api)
    {


        $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
        try {
            $api->checkValidityToken($bearerToken);
        }
        catch (ClientException $exception) {
            return new RedirectResponse('/login');
        }

        $tabProviders=$api->getRessources('providers',$bearerToken);
        return $this->render("AccountingMetrics/tableProviders.html.twig", [
            'tabProviders' => $tabProviders,

        ]);

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


        return $this->render("AccountingMetrics/tableInstallations.html.twig", [
            'tabInstallations' => $tabInstallations,
            'tabProjects' => $tabProjects,
            'tabMetricsDef' => $tabMetricsDef,
            "permissions"=>$permissions,
            "message"=>$message,
            "status"=> $status
        ]);

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

        return $this->render("AccountingMetrics/tableDefinitions.html.twig", [
            'tabMetricsDef' => $tabMetricsDef,
            'tabMetricsUnits' => $tabMetricsUnits,
            'tabMetricsTypes'=>$tabMetricsTypes,
            "message"=>$message,
            "status"=> $status

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

            $bearerToken = $this->container->get('security.token_storage')->getToken()->getAccessToken();
            $response=$api->modifyProviders($request->get('mode'), $request->get('project'),$request->get('provider'),$bearerToken);

        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            return new JsonResponse(json_encode(["message" => "Action successful", "status" => $status]));

        } catch (\Exception $exception) {

            $status = $response->getStatusCode();
            $message = $exception->getResponse()->getInfo()['response_headers'][2];
            return new JsonResponse(json_encode(["message" => $message, "status" => $status]));

        }
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


        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            return new JsonResponse(json_encode(["message" => "Action successful", "status" => $status]));

        } catch (\Exception $exception) {

            $status = $response->getStatusCode();
            $message = $exception->getResponse()->getInfo()['response_headers'][0];
            return new JsonResponse(json_encode(["message" => $message, "status" => $status]));

        }


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

        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            return new JsonResponse(json_encode(["message" => "Action successful", "status" => $status]));

        } catch (\Exception $exception) {

            $status = $response->getStatusCode();
            $message = $exception->getResponse()->getInfo()['response_headers'][0];
            return new JsonResponse(json_encode(["message" => $message, "status" => $status]));

        }
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

        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            return new JsonResponse(json_encode(["message" => "Action successful", "status" => $status]));

        } catch (\Exception $exception) {

            $status = $response->getStatusCode();
            $message = $exception->getResponse()->getInfo()['response_headers'][0];
            return new JsonResponse(json_encode(["message" => $message, "status" => $status]));

        }


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

        try {
            $status = $response->getStatusCode();
            $headers = $response->getHeaders();
            return new JsonResponse(json_encode(["message" => "Action successful", "status" => $status]));

        } catch (\Exception $exception) {

            $status = $response->getStatusCode();
            $message = $exception->getResponse()->getInfo()['response_headers'][0];

            return new JsonResponse(json_encode(["message" => $message, "status" => $status]));

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