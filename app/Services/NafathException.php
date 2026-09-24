<?php

namespace App\Services;

use RuntimeException;

class NafathException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $statusCode = 0,
        protected ?string $responseBody = null,
    ) {
        parent::__construct($message, $statusCode);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function responseBody(): ?string
    {
        return $this->responseBody;
    }
}