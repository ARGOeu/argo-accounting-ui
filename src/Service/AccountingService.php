<?php

namespace App\Service;

use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

    public function checkValidityToken($accessToken)
    {

        $response = $this->client->request('GET',
            $this->getParameter('aai_access_info_url'),
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10
            ],
        );
    }


    public function getUserGeneralPermissions($accessToken)
    {
        $response = $this->client->request('GET',
            $this->getParameter('accounting_api_url') . '/clients/general-permissions',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10]
        );

        if ($response->getStatusCode() == 403) {

            $this->client->request('POST',
                $this->getParameter('accounting_api_url') . '/clients',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10
                ],
            );

            $response = $this->client->request('GET',
                $this->getParameter('accounting_api_url') . '/clients/general-permissions',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10]
            );
        }


        return json_decode($response->getContent(), true);
    }

    public function getUserPermissions($accessToken, $summary = true)
    {
        $response = $this->client->request('GET',
            $this->getParameter('accounting_api_url') . '/clients/me',
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10
            ],

        );

        if ($response->getStatusCode() == 403) {

            $this->client->request('POST',
                $this->getParameter('accounting_api_url') . '/clients',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10
                ],
            );

            $response = $this->client->request('GET',
                $this->getParameter('accounting_api_url') . '/clients/me',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10]
            );
        }


        $tabListPermissionsRaw = json_decode($response->getContent(), true);


        if ($summary == true) {

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


        if ($summary == false)
            return $tabListPermissionsRaw;
        else
            return $tabListPermissions;


    }

    public function getRessources($resource_type, $accessToken)
    {


        if ($resource_type === 'projects') {
            $api_url = '/projects';
        }
        if ($resource_type === 'providers') {
            $api_url = '/providers';
        }
        if ($resource_type === 'installations') {
            $api_url = '/installations/all';
        }

        if ($resource_type === 'metric-definitions') {
            $api_url = '/metric-definitions';
        }

        if ($resource_type === 'unit-types') {
            $api_url = '/unit-types';
        }

        if ($resource_type === 'metric-types') {
            $api_url = '/metric-types';
        }

        $Notterminated = true;
        $i = 0;
        $responseTab = array();

        // multiple pages in the output
        while ($Notterminated and $i < 50) {
            $i++;
            $response[$i] = $this->client->request('GET',
                $this->getParameter('accounting_api_url') . $api_url . '?size=100&page=' . $i,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10
                ],

            );
            $data = json_decode($response[$i]->getContent(), true);


                if (count($data['content']) == 0) {
                    $Notterminated = false;
                } else {
                    $responseTab[] = $data['content'];
                }
        }

        return $responseTab;

    }

    public function searchRessources($resource_type, $search, $accessToken)
    {

        if ($resource_type === 'metrics') {
            $api_url = '/metrics/search';
        }



        $Notterminated = true;
        $i = 0;
        $responseTab = array();

        // multiple pages in the output
        while ($Notterminated and $i < 50) {
            $i++;
            $response[$i] = $this->client->request('POST',
                $this->getParameter('accounting_api_url') . $api_url . '?size=100&page=' . $i,
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $accessToken
                    ],
                    'timeout' => 10,
                    'body' => json_encode($search)
                ],

            );

            $data = json_decode($response[$i]->getContent(false), true);

            if (isset($data['content'])) {
                if (count($data['content']) == 0) {
                    $Notterminated = false;
                } else {
                    $responseTab[] = $data['content'];
                }
            }
            else {
                $Notterminated = false;
            }
        }

        return $responseTab;

    }


    public function addRessource($api_url, $body, $accessToken)
    {

        $response = $this->client->request('POST',
            $this->getParameter('accounting_api_url') . $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10,
                'body' => json_encode($body)
            ]);

        if ($response->getStatusCode() > 201) {
            $content = $response->getContent(false); //this throws ClientException
        } else {
            $content = json_encode(array("code" => $response->getStatusCode(), "message" => "Creation successful"));
        }
        return $content;

    }

    public function updateRessource($api_url, $body, $accessToken)
    {

        $response = $this->client->request('PATCH',
            $this->getParameter('accounting_api_url') . $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10,
                'body' => json_encode($body)
            ],
        );

        if ($response->getStatusCode() > 201) {
            $content = $response->getContent(false); //this throws ClientException
        } else {
            $content = json_encode(array("code" => $response->getStatusCode(), "message" => "Update successful"));
        }
        return $content;

    }

    public function deleteRessource($api_url, $accessToken)
    {


        $response = $this->client->request('DELETE',
            $this->getParameter('accounting_api_url') . $api_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10
            ],

        );

        if ($response->getStatusCode() > 201) {
            $content = $response->getContent(false); //this throws ClientException
        } else {
            $content = json_encode(array("code" => $response->getStatusCode(), "message" => "Deletion successful"));
        }
        return $content;

    }

    public function modifyProviders($mode, $project_id, $providers, $accessToken)
    {
        $post = ["providers" => $providers];


        $response = $this->client->request('POST',
            $this->getParameter('accounting_api_url') . '/projects/' . $project_id . '/' . $mode,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $accessToken
                ],
                'timeout' => 10,
                'body' => json_encode($post)
            ],
        );

        if ($response->getStatusCode() > 201) {
            $content = $response->getContent(false); //this throws ClientException
        } else {
            $content = json_encode(array("code" => $response->getStatusCode(), "message" => "Item(s) successfuly " . $mode . "d"));
        }
        return $content;
    }


}
