<?php
declare(strict_types=1);
namespace ApiClient;

use ApiClient\Exception\ApiException;
use ApiClient\Exception\AuthException;
use Http\Request\Request;
use Http\Client\Curl;
use Http\Client\Exception\ClientException;
use Cache\Cache;
use Http\Response\JsonResponse;

class Api
{
    protected const BASE_URL = 'https://api.supermetrics.com/';
    protected const AUTH_PATH = 'assignment/register';
    protected const TOKEN_CACHE = 'api_token';

    /**
     * @var string
     */
    protected string $clientEmail = 'pedro@nitrogenio.net';

    /**
     * @var string
     */
    protected string $clientName = 'Pedro Piedade';

    /**
     * @var string
     */
    protected string $clientId = 'ju16a6m81mhid5ue1z3v2g0uh';

    /**
     * @var string
     */
    protected string $token;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * Api constructor.
     * @throws ApiException
     */
    public function __construct()
    {
        try {
            $this->authenticate();
        }
        catch (AuthException $e) {
            throw new ApiException($e->getMessage());
        }
        $this->request = new Request(new Curl(new JsonResponse()));
        $this->request->addData('sl_token', $this->token);
    }

    /**
     * @throws AuthException
     */
    protected function authenticate(): void
    {
        $cache = new Cache();
        if ($token = $cache->get(self::TOKEN_CACHE)) {
            $this->token = (string)$token;
            return;
        }
        $this->token = $this->doAuth();
        $cache->set(self::TOKEN_CACHE, $this->token);
    }

    /**
     * @return string
     * @throws AuthException
     */
    protected function doAuth(): string
    {
        $auth = new Request(new Curl(new JsonResponse()));
        $auth->setUrl($this->buildUrl(self::BASE_URL, self::AUTH_PATH));
        $auth->addData('client_id', $this->clientId);
        $auth->addData('email', $this->clientEmail);
        $auth->addData('name', $this->clientName);
        $auth->setMethod(Request::METHOD_POST);
        $auth->setEncoding(Request::ENCODING_RAW);
        try {
            $response = $auth->send();
        }
        catch (ClientException $e) {
            throw new AuthException;
        }
        $authResponseObject = $response->getBodyAsObject();
        if (!isset($authResponseObject->data->sl_token)) {
            throw new AuthException;
        }
        return $authResponseObject->data->sl_token;
    }

    /**
     * @param string ...$parts
     * @return string
     */
    protected function buildUrl(...$parts): string
    {
        $url = '';
        foreach ($parts as $part) {
            $url .= $part;
        }
        return $url;
    }

}
