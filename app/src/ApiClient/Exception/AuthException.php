<?php
namespace ApiClient\Exception;

use Exception;

class AuthException extends Exception
{
    /** @var string */
    protected $message = 'We could not authenticate this APP on the API';
}
