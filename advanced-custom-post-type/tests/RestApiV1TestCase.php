<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Strings;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\ApiKey\ApiKeyModel;
use ACPT\Core\Repository\ApiRepository;

abstract class RestApiV1TestCase extends AbstractTestCase
{
    /**
     * Holds the WP REST Server object
     *
     * @var \WP_REST_Server
     */
    private $server;

    /**
     * set up the server
     */
    public function setUp(): void
    {
        parent::setUp();

        if(!function_exists('curl_init')) {
            die('cURL not available!');
        }

        // Initiating the REST API.
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server();
        do_action('rest_api_init');
        wp_set_current_user(1);
    }

    /**
     * shut down the server
     */
    public function tearDown(): void
    {
        parent::tearDown();

        // Shutting down the REST API.
        global $wp_rest_server;
        $wp_rest_server = null;
    }

    /**
     * @param string $verb
     * @param string $url
     * @param array  $data
     * @param array  $headers
     *
     * @return array
     * @throws \Exception
     */
    public function callRestApi($verb, $url, $data = [], $headers = [])
    {
        $allowedVerbs = [
            'DELETE',
            'GET',
            'HEAD',
            'PATCH',
            'POST',
            'PUT',
        ];

        if(!in_array($verb, $allowedVerbs)){
            throw new \Exception($verb . ' not allowed');
        }

        $finalUrl =  $this->getBasePath() . $url;

        $request = new \WP_REST_Request($verb, $finalUrl);
        $request->set_header('content-type', 'application/json');
        $request->set_header('accept', 'application/json');

        foreach ($headers as $key => $value){
            $request->set_header( $key, $value );
        }

        if (!empty($data)) {
            $request->set_body(json_encode($data));
        }

        $request->set_body_params([
            'results' => wp_json_encode([
                'failures' => 5,
            ]),
            'env' => wp_json_encode([
                'php_version' => phpversion(),
            ]),
        ]);

        $response = $this->server->dispatch($request);

        return [
            'status' => $response->get_status(),
            'headers' => $response->get_headers(),
            'response' => json_encode($response->get_data(), true),
        ];
    }

    /**
     * @param string $verb
     * @param string $url
     * @param array $data
     *
     * @return array
     * @throws \Exception
     */
    public function callAuthenticatedRestApi($verb, $url, $data = [])
    {
        // create a key if not exists
        $key = 'mauretto78';
        $secret = 'mauretto78';

        $apiKeyModel = ApiRepository::get([
            'key' => $key,
            'secret' => $secret,
        ]);

        if($apiKeyModel === null){

            $apiKeyModel = ApiKeyModel::hydrateFromArray([
                'id' => Uuid::v4(),
                'uid' => 1,
                'key' => $key,
                'secret' => $secret,
                'createdAt' => new \DateTime(),
            ]);

            ApiRepository::save($apiKeyModel);
        }

        $headers = [
            'acpt-api-key' => $key.'-'.$secret
        ];

        return $this->callRestApi($verb, $url, $data, $headers);
    }

    /**
     * @return string
     */
    private function getBasePath()
    {
        $restUrl = get_rest_url();

        if(Strings::contains('?rest_route=', $restUrl)){
            return '/?rest_route=/acpt/v1';
        }

        return '/acpt/v1';
    }
}