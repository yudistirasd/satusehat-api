<?php

namespace Satusehat\Integration;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
// Guzzle HTTP Package
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use Satusehat\Integration\Exception\Helper\OAuth2ClientException;
// SATUSEHAT Model & Log
use Satusehat\Integration\Models\SatusehatLog;
use Satusehat\Integration\Models\SatuSehatProfileFasyankes;
use Satusehat\Integration\Models\SatusehatToken;

class OAuth2Client
{
    use Tenant;

    public $satusehat_enable = true;

    public $patient_dev = ['P02478375538', 'P02428473601', 'P03647103112', 'P01058967035', 'P01836748436', 'P01654557057', 'P00805884304', 'P00883356749', 'P00912894463'];

    public $practitioner_dev = ['10009880728', '10006926841', '10001354453', '10010910332', '10018180913', '10002074224', '10012572188', '10018452434', '10014058550', '10001915884'];

    public $base_url;

    public $auth_url;

    public $fhir_url;

    public $client_id;

    public $client_secret;

    public $organization_id;

    public $override;

    public $satusehat_env;

    public $oauth2_error = [
        'statusCode' => 401,
        'res' => 'Unauthorized. Token not found',
    ];

    public $codeFasyankes;

    public SatuSehatProfileFasyankes $profile;

    public function __construct()
    {
        // Load .env jika belum dimuat
        $dotenv = Dotenv::createUnsafeImmutable(getcwd());
        $dotenv->safeLoad();

        // Satusehat enabled (default true)
        $v = getenv('SATUSEHAT_ENABLE');
        $this->satusehat_enable = ($v === false || $v === '') ? true : filter_var($v, FILTER_VALIDATE_BOOLEAN);

        $this->override = config('satusehatintegration.ss_parameter_override');
        $this->satusehat_env = getenv('SATUSEHAT_ENV');

        // Validasi environment awal
        if (empty($this->satusehat_env) && ! $this->override) {
            throw new OAuth2ClientException('SATUSEHAT environment is missing');
        }

        if (! in_array($this->satusehat_env, ['DEV', 'STG', 'PROD']) && ! $this->override) {
            throw new OAuth2ClientException(
                sprintf(
                    'SATUSEHAT environment invalid, supported (DEV, STG, PROD). %s given.',
                    $this->satusehat_env
                )
            );
        }

        // Map environment ke variabel ENV yang sesuai
        $this->setEnvironmentConfig($this->satusehat_env);

        // Jika override aktif, ambil data dari profile
        if ($this->override) {
            $this->applyProfileOverride();
        }

        // Validasi kredensial wajib
        $this->validateCredentials();

        // Endpoint default (bisa diubah dari ENV)
        $authEndpoint = getenv('SATUSEHAT_AUTH_ENDPOINT') ?: '/oauth2/v1';
        $fhirEndpoint = getenv('SATUSEHAT_FHIR_ENDPOINT') ?: '/fhir-r4/v1';

        // Final endpoint URLs
        $this->auth_url = $this->base_url . $authEndpoint;
        $this->fhir_url = $this->base_url . $fhirEndpoint;
    }

    protected function setEnvironmentConfig(string $env): void
    {
        $env = strtoupper($env);

        $defaults = [
            'DEV' => 'https://api-satusehat-dev.dto.kemkes.go.id',
            'STG' => 'https://api-satusehat-stg.dto.kemkes.go.id',
            'PROD' => 'https://api-satusehat.kemkes.go.id',
        ];

        $this->base_url = getenv("SATUSEHAT_BASE_URL_{$env}") ?: $defaults[$env];
        $this->client_id = getenv("CLIENTID_{$env}");
        $this->client_secret = getenv("CLIENTSECRET_{$env}");
        $this->organization_id = getenv("ORGID_{$env}");
    }

    protected function applyProfileOverride(): void
    {
        $this->profile = $this->getProfile();

        $this->codeFasyankes = $this->profile->kode;
        $this->client_id = $this->profile->client_key;
        $this->client_secret = $this->profile->secret_key;
        $this->organization_id = $this->profile->organization_id;
    }

    protected function validateCredentials(): void
    {
        if (empty($this->client_id) || empty($this->client_secret) || empty($this->organization_id)) {
            throw new OAuth2ClientException(sprintf(
                'SATUSEHAT environment defined as %s, but CLIENTID_%1$s / CLIENTSECRET_%1$s / ORGID_%1$s not set',
                $this->satusehat_env
            ));
        }
    }

    public function token()
    {
        if (! $this->satusehat_enable) {
            $this->oauth2_error = [
                'statusCode' => 503,
                'res' => 'SATUSEHAT integration disabled',
            ];

            return null;
        }

        $token = SatusehatToken::where('environment', $this->satusehat_env)->where('client_id', $this->client_id)->orderBy('created_at', 'desc')
            ->where('created_at', '>', now()->subMinutes(50))->first();

        if ($token) {
            return $token->token;
        }

        $client = new Client;

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];
        $options = [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ];

        // Create session
        $url = $this->auth_url . '/accesstoken?grant_type=client_credentials';
        $request = new Request('POST', $url, $headers);

        try {
            $res = $client->sendAsync($request, $options)->wait();
            $contents = json_decode($res->getBody()->getContents());

            if (isset($contents->access_token)) {
                SatusehatToken::create([
                    'environment' => $this->satusehat_env,
                    'client_id' => $this->client_id,
                    'token' => $contents->access_token,
                ]);

                return $contents->access_token;
            } else {
                return $this->respondError($this->oauth2_error);
            }
        } catch (ClientException $e) {
            // error.
            $res = json_decode($e->getResponse()->getBody()->getContents());
            $issue_information = $res->issue[0]->details->text;

            $this->log('Authentication', $issue_information, 'POST Token', $url, null, (array) $res);

            return $issue_information;
        }
    }

    public function log($resource, $id, $action, $url, $payload, $response)
    {
        $status = new SatusehatLog;
        $status->resource_type = $resource;
        $status->response_id = $id;
        $status->action = $action;
        $status->url = $url;
        $status->payload = $payload;
        $status->response = $response;
        $status->user_id = auth()->user() ? auth()->user()->id : 'Cron Job';
        $status->save();
    }

    public function respondError($message)
    {
        $statusCode = $message['statusCode'];
        $res = $message['res'];

        return [$statusCode, $res];
    }

    public function get_by_id($resource, $id)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->fhir_url . '/' . $resource . '/' . $id;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' | $response->total == 0) {
                $id = 'Error ' . $statusCode;
            }
            $this->log($resource, $id, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function get_by_nik($resource, $nik)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->fhir_url . '/' . $resource . '?identifier=https://fhir.kemkes.go.id/id/nik|' . $nik;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' | $response->total == 0) {
                $id = 'Not Found';
            } else {
                $id = $response->entry['0']->resource->id;
            }
            $this->log($resource, $id, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    /**
     * Get request to SATUSEHAT master data resource
     *
     * @param [type] $resource
     * @param [type] $queryString
     * @return void
     */
    public function ss_kfa_get($resource, $queryString)
    {

        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->base_url . '/kfa-v2/' . $resource . $queryString;

        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($resource == 'products/all?') {
                if (! empty($response) && empty($response->total)) {
                    $id = 'Not Found';
                } else {
                    $id = 'Kfa_GET_' . $resource;
                }
            }

            if ($resource == 'products?') {
                if (! empty($response) && empty($response->result)) {
                    $id = 'Not Found';
                } else {
                    $id = 'Kfa_GET_' . $resource;
                }
            }

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_post($resource, $body)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->fhir_url . ($resource == 'Bundle' ? '' : '/' . $resource);
        $request = new Request('POST', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($resource === 'Patient') {
                // Patient

                // Get patient identifer
                $patient_obj = json_decode($body);
                $url = $patient_obj->identifier[0]->system;
                $parsed_url = parse_url($url, PHP_URL_PATH);
                $exploded_url = explode('/', $parsed_url);
                $identifier_type = $exploded_url[2];

                if ($identifier_type === 'nik') {
                    if ($response->success !== true) {
                        $id = 'Error ' . $statusCode;
                    }
                    $id = $response->data->patient_id;
                } elseif ($identifier_type === 'nik-ibu') {
                    if ($response->create_patient->success !== true) {
                        $id = 'Error ' . $statusCode;
                    }
                    $id = $response->create_patient->data->patient_id;
                }
            } else {
                // Other than patient
                if ($response->resourceType == 'OperationOutcome' || $statusCode >= 400) {
                    $id = 'Error ' . $statusCode;
                } else {
                    if ($resource == 'Bundle') {
                        $id = 'Success ' . $statusCode;
                    } else {
                        $id = $response->id;
                    }
                }
            }
            $this->log($resource, $id, 'POST', $url, (array) json_decode($body), (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'POST', $url, (array) json_decode($body), (array) $res);

            return [$statusCode, $res];
        }

        $res = $client->sendAsync($request)->wait();
        echo $res->getBody();
    }

    public function ss_put($resource, $id, $body)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->fhir_url . '/' . $resource . '/' . $id;
        $request = new Request('PUT', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' || $statusCode >= 400) {
                $id = 'Error ' . $statusCode;
            } else {
                $id = $response->id;
            }
            $this->log($resource, $id, 'PUT', $url, (array) json_decode($body), (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'PUT', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_patch($resource, $id, $body)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($this->oauth2_error);
        }

        $client = new Client;
        $headers = [
            'Content-Type' => 'application/json-patch+json',
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $url = $this->fhir_url . '/' . $resource . '/' . $id;
        $request = new Request('PATCH', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' || $statusCode >= 400) {
                $id = 'Error ' . $statusCode;
            } else {
                $id = $response->id;
            }
            $this->log($resource, $id, 'PATCH', $url, (array) json_decode($body), (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log($resource, 'Error ' . $statusCode, 'PATCH', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }
}
