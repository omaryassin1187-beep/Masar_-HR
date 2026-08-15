<?php

namespace App\Exceptions;

use Exception;

class ResignationException extends Exception
{
    public function __construct(
        string $message,
        protected int $statusCode = 422
    ) {
        parent::__construct($message);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public static function activeResignationExists(): self
    {
        return new self('An active resignation request already exists for this employee.', 422);
    }

    public static function noActiveContract(): self
    {
        return new self('No active contract found for this employee.', 422);
    }

    public static function unauthorizedReassignment(): self
    {
        return new self('You are not authorized to reassign tasks for this resignation request.', 403);
    }

    public static function invalidTaskReassignment(): self
    {
        return new self('One or more tasks are not assignable to this employee or are not in a valid state.', 422);
    }

    public static function notImmediate(): self
    {
        return new self('Classification only applies to immediate resignations.', 422);
    }

    public static function alreadyClassified(): self
    {
        return new self('This resignation has already been classified.', 422);
    }

    public static function notFound(): self
    {
        return new self('Resignation request not found.', 404);
    }

    public static function invalidType(): self
    {
        return new self('Invalid resignation type. Must be "immediate" or "with_notice".', 422);
    }
}
