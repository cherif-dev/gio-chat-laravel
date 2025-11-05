<?php

namespace GenitIo\Chat\Exceptions;

class GenitIoApiException extends \Exception
{
    /**
     * Create a new Genit IO API exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        return response()->json([
            'error' => 'GENIT.IO_API_ERROR',
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 500);
    }
}
