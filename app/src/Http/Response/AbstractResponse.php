<?php
declare(strict_types=1);
namespace Http\Response;

use JsonException;
use stdClass;

abstract class AbstractResponse
{
    public const HTTP_OK = 200;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_NOT_FOUND = 404;

    public int $status;

    /**
     * @var string
     */
    public string $body;

    /**
     * AbstractResponse constructor.
     * @param int $status
     * @param mixed $body
     */
    public function __construct(int $status = 200, $body = '')
    {
        $this->status = $status;
        $this->setBody($body);
    }

    /**
     * @param string $body
     * @return $this
     */
    abstract public function setBody(string $body): self;

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getBodyAsObject()
    {
        try {
            return json_decode($this->body, false, 512, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e) {
            return new stdClass();
        }
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatusCode(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function send()
    {
        $this->setHeaders();
        echo $this->getBody();
        exit;
    }

    public function setHeaders(): self
    {
        header('Content-Type: application/html');
        return $this;
    }

}
