<?php

namespace Deplink\Constraints\Exceptions;

use Throwable;

class IncorrectJsonValueException extends \Exception
{
    /**
     * @var mixed
     */
    public $json;

    public function __construct($message = "", $json, $code = 0, Throwable $previous = null)
    {
        $this->json = $json;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return $this->json;
    }
}
