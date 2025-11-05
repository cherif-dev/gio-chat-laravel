<?php

namespace GenitIo\Chat\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GenitIo\Chat\Exceptions\GenitIoApiException;

class GenitIoApiClient
{
    protected string $baseUrl;
    protected string $project;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retryDelay;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->baseUrl = config('chat.base_url');
        $this->project = config('chat.project');
        $this->apiKey = config('chat.project_api_key');
        $this->timeout = config('chat.timeout', 30);
        $this->retryTimes = config('chat.retry_times', 3);
        $this->retryDelay = config('chat.retry_delay', 100);
        $this->verifySsl = config('chat.verify_ssl', true);
        $this->validateConfiguration();
    }

    /**
     * Validate that required configuration is present.
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->baseUrl)) {
            throw new \RuntimeException('Genit IO API base URL is not configured. Set GENIT_IO_API_BASE_URL in your .env file.');
        }

        if (empty($this->project)) {
            throw new \RuntimeException('Genit IO project is not configured. Set GENIT_IO_PROJECT in your .env file.');
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Genit IO API key is not configured. Set GENIT_IO_PROJECT_API_KEY in your .env file.');
        }
    }

    /**
     * Create a configured HTTP client instance.
     */
    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ])
            ->timeout($this->timeout)
            ->retry($this->retryTimes, $this->retryDelay, function ($exception, $request) {
                // Only retry on connection errors or 5xx server errors
                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception instanceof \Illuminate\Http\Client\RequestException
                        && $exception->response->status() >= 500);
            })
            ->when(!$this->verifySsl, function ($client) {
                return $client->withoutVerifying();
            });
    }

    /**
     * Create a contact in the Genit IO system.
     *
     * @param array $data Contact data
     * @return array Response data from Genit IO API
     * @throws GenitIoApiException
     */
    public function createContact(array $data): array
    {
        try {
            $response = $this->client()
                ->post("/api/v1/{$this->project}/contacts", $data);

            $this->logRequest('POST', "/api/v1/{$this->project}/contacts", $data, $response);

            if ($response->successful()) {
                return $response->json();
            }

            throw $this->createException($response, 'Failed to create contact in Genit IO');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logError('Connection error while creating contact', $e, $data);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (GenitIoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('Unexpected error while creating contact', $e, $data);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Update a contact in the Genit IO system.
     *
     * @param string $contactId Contact ID
     * @param array $data Updated contact data
     * @return array Response data from Genit IO API
     * @throws GenitIoApiException
     */
    public function updateContact(string $contactId, array $data): array
    {
        try {
            $response = $this->client()
                ->put("/api/v1/{$this->project}/contacts/{$contactId}", $data);

            $this->logRequest('PUT', "/api/v1/{$this->project}/contacts/{$contactId}", $data, $response);

            if ($response->successful()) {
                return $response->json();
            }

            throw $this->createException($response, 'Failed to update contact in Genit IO');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logError('Connection error while updating contact', $e, $data);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (GenitIoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('Unexpected error while updating contact', $e, $data);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Get a contact from the Genit IO system.
     *
     * @param string $contactId Contact ID
     * @return array Response data from Genit IO API
     * @throws GenitIoApiException
     */
    public function getContact(string $contactId): array
    {
        try {
            $response = $this->client()
                ->get("/api/v1/{$this->project}/contacts/{$contactId}");

            $this->logRequest('GET', "/api/v1/{$this->project}/contacts/{$contactId}", [], $response);

            if ($response->successful()) {
                return $response->json();
            }

            throw $this->createException($response, 'Failed to get contact from Genit IO');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logError('Connection error while getting contact', $e);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (GenitIoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('Unexpected error while getting contact', $e);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Delete a contact from the Genit IO system.
     *
     * @param string $contactId Contact ID
     * @return bool Success status
     * @throws GenitIoApiException
     */
    public function deleteContact(string $contactId): bool
    {
        try {
            $response = $this->client()
                ->delete("/api/v1/{$this->project}/contacts/{$contactId}");

            $this->logRequest('DELETE', "/api/v1/{$this->project}/contacts/{$contactId}", [], $response);

            if ($response->successful()) {
                return true;
            }

            throw $this->createException($response, 'Failed to delete contact from Genit IO');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logError('Connection error while deleting contact', $e);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (GenitIoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('Unexpected error while deleting contact', $e);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Create an exception from a failed response.
     */
    protected function createException(Response $response, string $message): GenitIoApiException
    {
        $statusCode = $response->status();
        $body = $response->json();
        $errorMessage = $body['message'] ?? $body['error'] ?? $response->body();

        return new GenitIoApiException(
            "{$message}: [{$statusCode}] {$errorMessage}",
            $statusCode
        );
    }

    /**
     * Log API request and response.
     */
    protected function logRequest(string $method, string $endpoint, array $data, Response $response): void
    {
        if (config('app.debug')) {
            Log::channel('daily')->info('Genit IO API Request', [
                'method' => $method,
                'endpoint' => $endpoint,
                'request_data' => $data,
                'status_code' => $response->status(),
                'response_body' => $response->json(),
            ]);
        }
    }

    /**
     * Log API error.
     */
    protected function logError(string $message, \Exception $exception, array $context = []): void
    {
        Log::channel('daily')->error('Genit IO API Error: ' . $message, [
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context,
        ]);
    }



    /**
     * Get conversations for a contact from the Genit IO system.
     *
     * @param string $contactId Contact ID
     * @param string|null $participantId Optional participant ID to filter conversations
     * @return array Response data from Genit IO API
     * @throws GenitIoApiException
     */
    public function getConversation(string $contactId, string $participantId): array
    {
        try {
            $endpoint = "/api/v1/{$this->project}/chat/{$contactId}/{$participantId}";


            $response = $this->client()->get($endpoint);

            $this->logRequest('GET', $endpoint, [], $response);

            if ($response->successful()) {
                $response = $response->json();
                return $response['conversation'];
            }

            throw $this->createException($response, 'Failed to get conversation from Genit IO');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->logError('Connection error while getting conversation', $e);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        } catch (GenitIoApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logError('Unexpected error while getting conversation', $e);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
