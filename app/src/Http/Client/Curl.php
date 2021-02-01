<?php
declare(strict_types=1);
namespace Http\Client;

use Http\Client\Exception\ClientException;
use Http\Request\Request;
use Http\Response\AbstractResponse;

/**
 * A very simple Curl wrapper
 * @method AbstractResponse get(string $url) Execute a GET request
 * @method AbstractResponse post(string $url, mixed $data) Execute a POST request
 */
class Curl extends AbstractClient
{
    /**
     * @var resource
     */
    protected $ch;

    /**
     * @param string $method
     * @param string $url
     * @param array $data
     * @param int $encoding
     * @return Request
     */
    public function createRequest(string $method, string $url, array $data = [], int $encoding = Request::ENCODING_QUERY): Request
    {
        $request = new $this->requestClass($this);
        $request->setMethod($method);
        $request->setUrl($url);
        $request->setData($data);
        $request->setEncoding($encoding);
        return $request;
    }

    /**
     * @param Request $request
     * @throws ClientException
     */
    public function prepareRequest(Request $request): void
    {
        $this->ch = curl_init();
        if (!is_resource($this->ch)) {
            throw new ClientException('We could not initialize Curl');
        }
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
        if ($request->hasData()) {
            if ($request->getMethod() === Request::METHOD_POST) {
                curl_setopt($this->ch, CURLOPT_POST, 1);
            }
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request->encodeData());
        }
        curl_setopt($this->ch, CURLOPT_URL, $this->buildUrl($request->getUrl(), $request->getData()));

    }

    /**
     * @param string $url
     * @param array $query
     * @return string
     */
    public function buildUrl(string $url, array $query): string
    {
        if (empty($query)) {
            return $url;
        }
        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }
        $queryString = '';
        if (isset($parts['query']) && $parts['query']) {
            $queryString .= $parts['query'] . '&' . http_build_query($query);
        }
        else {
            $queryString = http_build_query($query);
        }
        $retUrl = '';
        if (isset($parts['scheme'], $parts['host'])) {
            $retUrl = sprintf('%s://%s', $parts['scheme'], $parts['host']);
        }
        if (isset($parts['port'])) {
            $retUrl .= ':'.$parts['port'];
        }
        if (isset($parts['path'])) {
            $retUrl .= $parts['path'];
        }
        if ($queryString) {
            $retUrl .= '?' . $queryString;
        }
        return $retUrl;
    }

    /**
     * @param Request $request
     * @return AbstractResponse
     * @throws ClientException
     */
    public function sendRequest(Request $request): AbstractResponse
    {
        $this->prepareRequest($request);
        $result = curl_exec($this->ch);
        if ($result === false) {
            $errorMessage = curl_error($this->ch);
            curl_close($this->ch);
            throw new ClientException('CURL request failed: ' . $errorMessage);
        }
        $response = $this->createResponseObject(is_string($result) ? $result : '');
        curl_close($this->ch);
        return $response;
    }

    /**
     * @param  string $response
     * @return AbstractResponse
     */
    protected function createResponseObject(string $response): AbstractResponse
    {
        $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $this->response->setStatusCode($statusCode);
        $this->response->setBody(substr($response, $headerSize));
        return $this->response;
    }

}
