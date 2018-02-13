<?php

namespace ellsif\WelCMS;

/**
 * カスタムExceptionクラス。
 */
class Exception extends \Exception
{
    private $responseCode;

    private $errors;

    public function __construct($message, $code = 0, \Exception $previous = null, $errors = null, int $responseCode = 500)
    {
        parent::__construct($message, $code, $previous);
        $this->responseCode = $responseCode;
        $this->errors = $errors;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}