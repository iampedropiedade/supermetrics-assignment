<?php
declare(strict_types=1);
namespace Http\Client;

use Http\Client\Exception\ClientException;
use Http\Response\AbstractResponse;
use Http\Response\Response;
use Http\Request\Request;

/**
 * @method AbstractResponse get(string $url) Execute a GET request
 * @method AbstractResponse post(string $url, mixed $data) Execute a POST request
 * And more HTTP verbs could be added...
 */
abstract class AbstractClient
{

    protected string $requestClass = Request::class;
    protected AbstractResponse $response;

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @param int $encoding
     * @return Request
     */
    abstract public function createRequest(string $method, string $url, array $data = [], int $encoding = Request::ENCODING_QUERY): Request;

    /**
     * @param Request $request
     * @throws ClientException
     */
    abstract public function prepareRequest(Request $request): void;

    /**
     * @param Request $request
     * @return AbstractResponse
     */
    abstract public function sendRequest(Request $request): AbstractResponse;

    /**
     * @param  string $response
     * @return AbstractResponse
     */
    abstract protected function createResponseObject(string $response): AbstractResponse;

    /**
     * AbstractClient constructor.
     * @param AbstractResponse|null $response
     */
    public function __construct(AbstractResponse $response = null)
    {
        $this->response = $response ?? new Response();
    }

    /**
     * @param string $func
     * @param array $args
     * @return AbstractResponse
     * @throws ClientException
     */
    public function __call(string $func, array $args): AbstractResponse
    {
        $method = strtolower($func);
        $encoding = Request::ENCODING_QUERY;
        if (!array_key_exists($method, Request::$allowedMethods)) {
            throw new ClientException('Not a valid HTTP method.');
        }
        if (!isset($args[0])) {
            throw new ClientException('Missing argument URL');
        }
        $url = $args[0];
        $data = $args[1] ?? null;
        $request = $this->createRequest($method, $url, $data, $encoding);
        return $this->sendRequest($request);
    }

}
