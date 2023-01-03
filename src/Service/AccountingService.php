<?php

namespace App\Service;

use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class AccountingService extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function checkValidityToken($accessToken) {

            $response = $this->client->request('GET',
            $this->getParameter('aai_access_info_url') ,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout'=>10
            ],
        );
    }


    public function getUserPermissions($accessToken,$summary=true)
    {
        $response = $this->client->request('GET',
            $this->getParameter('accounting_api_url') . '/clients/me',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout'=>10
            ],

        );


            $tabListPermissionsRaw=json_decode($response->getContent(), true);

        if($summary==true) {
            $tabListPermissions = array();
            foreach ($tabListPermissionsRaw['content'] as $tabProjects) {
                foreach ($tabProjects['permissions'] as $permissions) {
                    foreach ($permissions as $key => $permission) {
                        if (is_array($permission)) {
                            foreach ($permission as $access_right) {
                                $tabListPermissions[$tabProjects['acronym']][$permissions['collection']][$access_right['operation']] = $access_right['access_type'];
                            }
                        }
                    }
                }
            }
        }

        if($summary==false)
            return $tabListPermissionsRaw;
        else
         return  $tabListPermissions;


    }

    public function getRessources($resource_type,$accessToken)
    {


        if ($resource_type === 'projects') {
            $api_url = '/projects';
        }
        if ($resource_type === 'providers') {
            $api_url = '/providers/all';
        }
        if ($resource_type === 'installations') {
            $api_url = '/installations/all';
        }

        if ($resource_type === 'metric-definitions') {
            $api_url = '/metric-definitions';
        }

        if ($resource_type === 'unit-types') {
            $api_url = '/metric-definitions/unit-types';
        }

        if ($resource_type === 'metric-types') {
            $api_url = '/metric-definitions/metric-types';
        }

        try {
        $response = $this->client->request('GET',
            $this->getParameter('accounting_api_url') . $api_url.'?size=100',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout'=>10
            ],

        );
            return (json_decode($response->getContent(), true));
            } catch (ClientException $e) {
        return new JsonResponse(json_encode($e->getMessage()), $e->getCode());
        }

    }

    public function searchRessources($resource_type,$search,$accessToken)
    {


        if ($resource_type === 'metrics') {
            $api_url = '/metrics/search';
        }


        try {
            $response = $this->client->request('POST',
                $this->getParameter('accounting_api_url') . $api_url.'?size=100',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout'=>10,
                    'body'=>json_encode($search)
                ],


            );
            return (json_decode($response->getContent(), true));
        } catch (ClientException $e) {
            return new JsonResponse(json_encode($e->getMessage()), $e->getCode());
        }

    }


    public function addRessource($api_url,$body,$accessToken)
    {

        try {
            $response = $this->client->request('POST',
                $this->getParameter('accounting_api_url') . $api_url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout'=>10,
                    'body'=>json_encode($body)
                ],

            );
            return ($response);
        } catch (ClientException $e) {
            return new JsonResponse(json_encode($e->getMessage()), $e->getCode());
        }

    }

    public function updateRessource($api_url,$body,$accessToken)
    {

        try {
            $response = $this->client->request('PATCH',
                $this->getParameter('accounting_api_url') . $api_url,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout'=>10,
                    'body'=>json_encode($body)
                ],

            );

            return ($response);
        } catch (ClientException $e) {
            return new JsonResponse(json_encode($e->getMessage()), $e->getCode());
        }

    }

    public function deleteRessource($api_url,$accessToken)
    {

        try {
            $response = $this->client->request('DELETE',
            $this->getParameter('accounting_api_url') . $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout'=>10
            ],

        );
        return ($response);
        } catch (ClientException $e) {
        return new JsonResponse(json_encode($e->getMessage()), $e->getStatusCode());
        }

    }

    public function modifyProviders($mode,$project_id,$providers,$accessToken)
    {
        $post=["providers"=>array($providers)];


        try {
            $response = $this->client->request('POST',
                $this->getParameter('accounting_api_url') . '/projects/'.$project_id.'/'.$mode,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout'=>10,
                    'body'=>json_encode($post)
                ],
            );
            return ($response);
        } catch (ClientException $e) {
            return new JsonResponse(json_encode($e->getMessage()), $e->getStatusCode());
        }

    }


}
