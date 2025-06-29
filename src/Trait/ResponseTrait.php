<?php

namespace App\Trait;

use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    /**
     * Create a successful JSON response
     */
    protected function successResponse(array $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return $this->json($data, $statusCode);
    }

    /**
     * Create a created JSON response (201)
     */
    protected function createdResponse(array $data): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Create an error JSON response
     */
    protected function errorResponse(string $message, int $statusCode = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->json(['error' => $message], $statusCode);
    }

    /**
     * Create a not found JSON response
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Create an internal server error JSON response
     */
    protected function internalErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Create a validation error JSON response
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getMessage();
        }
        
        return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handle validation errors from Symfony validator
     */
    protected function handleValidationErrors($violations): JsonResponse
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }
        
        return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Handle exceptions and return appropriate JSON response
     */
    protected function handleException(Exception $e): JsonResponse
    {
        if ($e instanceof InvalidArgumentException) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->internalErrorResponse();
    }

    /**
     * Create a message response
     */
    protected function messageResponse(string $message, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return $this->json(['message' => $message], $statusCode);
    }

    /**
     * Create a total response with unit
     */
    protected function totalResponse(float $total, string $unit): JsonResponse
    {
        return $this->json(['total' => $total, 'unit' => $unit]);
    }

    /**
     * Validate JSON request and return decoded data or error
     */
    protected function validateJsonRequest(string $content): array|JsonResponse
    {
        $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!$data) {
            return $this->errorResponse('Invalid JSON');
        }

        return $data;
    }
}
