<?php
namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class ApiException extends RuntimeException
{
    private string $errorCode;

    public function __construct(string $message = "Une erreur est survenue lors de l'execution de la requete", int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->errorCode = 'API_EXCEPTION';
    }

    public static function make(string $message, string $errCode, int $code = 400, Throwable|null $previous = null) {
        $result = new self($message, $code, $previous);
        
        $result->errorCode = $errCode;

        return $result;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}