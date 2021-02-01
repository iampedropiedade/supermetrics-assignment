<?php
declare(strict_types=1);
namespace Http\Response;

class Response extends AbstractResponse
{

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body): self
    {
        $this->body = $body;
        return $this;
    }
}
