<?php

namespace GenitIo\Chat\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GenitIo\Chat\Exceptions\GenitIoApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class GenitIoApiClient
{
    protected string $baseUrl;
    protected string $origin;
    protected string $project;
    protected string $apiKey;
    protected int $timeout;
    protected int $retryTimes;
    protected int $retryDelay;
    protected bool $verifySsl;

    public function __construct()
    {
        $this->origin = config('app.url');
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
     * Get the project configuration.
     *
     * @return array
     * @throws \RuntimeException
     */
    public function getProjectConfig(): array
    {
        return [
            'url' => $this->origin . '/genit-io-chat/' . $this->project,
            'base_url' => $this->baseUrl . '/api/v1/' . $this->project,
            'key' => $this->apiKey,
        ];
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
                'Origin' => $this->origin,
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
     * Get the config from the Genit IO project.
     *
     * @return array Response data from Genit IO API
     * @throws GenitIoApiException
     */
    public function getConfig(): array
    {
        try {
            $response = $this->client()
                ->get("/api/v1/{$this->project}/config");

            $this->logRequest('GET', "/api/v1/{$this->project}/config", [], $response);

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
     * Send the request to the API and handle errors.
     *
     * @throws GenitIoApiException
     */
    protected function sendRequest(string $method, string $endpoint, array $payloadOrQuery): Response
    {
        $httpMethod = strtoupper($method);
        $requestData = $payloadOrQuery;

        try {
            $client = $this->client();

            switch ($httpMethod) {
                case 'GET':
                    $response = $client->get($endpoint, $payloadOrQuery);
                    break;
                case 'POST':
                    $response = $client->post($endpoint, $payloadOrQuery);
                    break;
                case 'PUT':
                    $response = $client->put($endpoint, $payloadOrQuery);
                    break;
                case 'PATCH':
                    $response = $client->patch($endpoint, $payloadOrQuery);
                    break;
                case 'DELETE':
                    $response = $client->delete($endpoint, $payloadOrQuery);
                    break;
                default:
                    throw new \InvalidArgumentException("Unsupported HTTP method [{$httpMethod}]");
            }

            $this->logRequest($httpMethod, $endpoint, $requestData, $response);

            return $response;
        } catch (ConnectionException $exception) {
            $this->logError("Connection error during {$httpMethod} {$endpoint}", $exception, $payloadOrQuery);
            throw new GenitIoApiException(
                'Unable to connect to Genit IO API: ' . $exception->getMessage(),
                0,
                $exception
            );
        } catch (RequestException $exception) {
            $response = $exception->response;
            if ($response) {
                $this->logRequest($httpMethod, $endpoint, $requestData, $response);
                throw $this->createException($response, 'Genit IO API request failed');
            }

            $this->logError("Request error during {$httpMethod} {$endpoint}", $exception, $payloadOrQuery);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $exception->getMessage(),
                0,
                $exception
            );
        } catch (GenitIoApiException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->logError("Unexpected error during {$httpMethod} {$endpoint}", $exception, $payloadOrQuery);
            throw new GenitIoApiException(
                'Unexpected error communicating with Genit IO API: ' . $exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * Perform an HTTP request that returns JSON.
     *
     * @throws GenitIoApiException
     */
    protected function requestJson(string $method, string $endpoint, array $payloadOrQuery, string $errorMessage): array
    {
        $response = $this->sendRequest($method, $endpoint, $payloadOrQuery);
        if ($response->successful()) {
            $json = $response->json();

            return is_array($json) ? $json : [];
        }

        throw $this->createException($response, $errorMessage);
    }

    /**
     * Perform an HTTP request that returns a boolean on success.
     *
     * @throws GenitIoApiException
     */
    protected function requestBoolean(string $method, string $endpoint, array $payload, string $errorMessage): bool
    {
        $response = $this->sendRequest($method, $endpoint, $payload);

        if ($response->successful()) {
            return true;
        }

        throw $this->createException($response, $errorMessage);
    }
}
