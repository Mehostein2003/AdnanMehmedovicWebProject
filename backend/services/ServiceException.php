<?php
// Thrown by the services when a request cannot be fulfilled.
// The exception code is the HTTP status that the API returns (400, 404, 503, ...).
// $details can hold one message per invalid field.

class ServiceException extends Exception
{
    private array $details;

    public function __construct(string $message, int $httpCode = 400, array $details = [])
    {
        parent::__construct($message, $httpCode);
        $this->details = $details;
    }

    public function getDetails(): array
    {
        return $this->details;
    }
}
