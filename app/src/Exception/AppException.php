<?php
declare(strict_types=1);
namespace Exception;

use Exception;

class AppException extends Exception
{
    /** @var int */
    protected $code = 500;
}
