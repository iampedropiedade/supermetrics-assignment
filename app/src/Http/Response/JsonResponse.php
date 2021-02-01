<?php
declare(strict_types=1);
namespace Http\Response;

use JsonException;

class JsonResponse extends AbstractResponse
{

    /**
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param array $body
     * @return $this
     */
    public function setBodyArray(array $body): self
    {
        try {
            $this->body = json_encode($body, JSON_THROW_ON_ERROR);
        }
        catch (JsonException $e) {
            $this->body = $e->getMessage();
        }
        return $this;
    }

    public function setHeaders(): self
    {
        header('Content-Type: application/json');
        return $this;
    }
}
