<?php
declare(strict_types=1);

namespace Application;

use Exception\AppException;
use Stats\Stats;
use Http\Response\JsonResponse;

class Application
{

    public static function run(): void
    {
        $response = new JsonResponse();
        try {
            $response->setBodyArray((new Stats())->get());
        }
        catch (AppException $e) {
            $response->setStatusCode($e->getCode());
            $response->setBody($e->getMessage());
        }
        $response->send();
    }
}
