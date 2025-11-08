<?php

namespace GenitIo\Chat\Traits;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait HandlesApiResponses
{
    protected function apiErrorResponse(string $message, GenitIoApiException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $exception->getMessage(),
        ], $exception->getCode() ?: 400);
    }

    protected function unexpectedErrorResponse(\Throwable $exception): JsonResponse
    {
        Log::error(sprintf('Unexpected error in %s', static::class), ['exception' => $exception]);

        return response()->json([
            'success' => false,
            'message' => 'An unexpected error occurred.',
            'error' => config('app.debug') ? $exception->getMessage() : 'Internal server error',
        ], 500);
    }

    /**
     * Normalize payload before sending it to the API.
     */
    protected function prepareMessagePayload(array $payload): array
    {
        if (array_key_exists('data', $payload)) {
            $payload['data'] = $this->normalizeDataField($payload['data']);
        }

        return array_filter(
            $payload,
            static fn($value) => $value !== null
        );
    }

    /**
     * Decode the data field if it is provided as JSON string.
     */
    protected function normalizeDataField(mixed $data): mixed
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $data;
    }
}
