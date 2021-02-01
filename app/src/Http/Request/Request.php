<?php
declare(strict_types=1);
namespace Http\Request;

use Http\Client\Exception\ClientException;
use Http\Client\Curl;
use Http\Response\AbstractResponse;
use JsonException;

/**
 * @throws ClientException
 */
class Request
{

    public const ENCODING_QUERY = 0;
    public const ENCODING_JSON = 1;
    public const ENCODING_RAW = 2;
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';

    /**
     * @var array
     */
    public static array $allowedMethods = [
        self::METHOD_POST,
        self::METHOD_GET,
    ];

    /**
     * @var string
     */
    private string $method = self::METHOD_GET;

    /**
     * @var string
     */
    private string $url = '';

    /**
     * @var int
     */
    private int $encoding = self::ENCODING_JSON;

    /**
     * @var mixed
     */
    private $data = [];

    /**
     *
     * @var Curl
     */
    private $curl;

    /**
     * @param mixed $curl
     */
    public function __construct($curl)
    {
        $this->curl = $curl;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Request
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Request
     */
    public function addData(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @return boolean
     */
    public function hasData(): bool
    {
        return !empty($this->data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param int $encoding
     * @return Request
     */
    public function setEncoding(int $encoding): self
    {
        $this->encoding = $encoding;
        return $this;
    }

    /**
     * @return array|false|mixed|string
     * @throws ClientException
     */
    public function encodeData()
    {
        try {
            switch ($this->encoding) {
                case static::ENCODING_JSON:
                    return json_encode($this->data, JSON_THROW_ON_ERROR);
                case static::ENCODING_QUERY:
                    return (!is_null($this->data) ? http_build_query($this->data) : '');
                case static::ENCODING_RAW:
                    return $this->data;
            }
        }
        catch (JsonException $e) {
            throw new ClientException(sprintf('Error encoding JSON: %s', $e->getMessage()));
        }
        throw new ClientException(sprintf('Encoding %s unknown', $this->encoding));
    }

    /**
     * @return AbstractResponse
     * @throws ClientException
     */
    public function send()
    {
        return $this->curl->sendRequest($this);
    }
}
