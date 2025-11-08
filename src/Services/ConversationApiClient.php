<?php

namespace GenitIo\Chat\Services;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class ConversationApiClient extends GenitIoApiClient
{
    /**
     * Get a list of conversations for a contact.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param array $query
     * @return array
     * @throws GenitIoApiException
     */
    public function list(string $projectSlug, string $contactUuid, array $query = []): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid);

        return $this->requestJson('GET', $endpoint, $query, 'Failed to list conversations');
    }

    /**
     * Create a conversation for a contact.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function create(string $projectSlug, string $contactUuid, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid);

        return $this->requestJson('POST', $endpoint, $payload, 'Failed to create conversation');
    }

    /**
     * Get a specific conversation.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @return array
     * @throws GenitIoApiException
     */
    public function show(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId);

        return $this->requestJson('GET', $endpoint, [], 'Failed to get conversation');
    }

    /**
     * Update a conversation.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function update(string $projectSlug, string $contactUuid, string $conversationId, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId);

        return $this->requestJson('PUT', $endpoint, $payload, 'Failed to update conversation');
    }

    /**
     * Delete a conversation.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @return bool
     * @throws GenitIoApiException
     */
    public function delete(string $projectSlug, string $contactUuid, string $conversationId): bool
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId);

        return $this->requestBoolean('DELETE', $endpoint, [], 'Failed to delete conversation');
    }

    /**
     * Assign a conversation.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function assign(string $projectSlug, string $contactUuid, string $conversationId, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'assign');

        return $this->requestJson('POST', $endpoint, $payload, 'Failed to assign conversation');
    }

    /**
     * Unassign a conversation.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @return array
     * @throws GenitIoApiException
     */
    public function unassign(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'unassign');

        return $this->requestJson('POST', $endpoint, [], 'Failed to unassign conversation');
    }

    /**
     * Update conversation status.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $conversationId
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function updateStatus(string $projectSlug, string $contactUuid, string $conversationId, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'status');

        return $this->requestJson('POST', $endpoint, $payload, 'Failed to update conversation status');
    }

    /**
     * Pin a conversation.
     */
    public function pin(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'pin');

        return $this->requestJson('POST', $endpoint, [], 'Failed to pin conversation');
    }

    /**
     * Unpin a conversation.
     */
    public function unpin(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'unpin');

        return $this->requestJson('POST', $endpoint, [], 'Failed to unpin conversation');
    }

    /**
     * Star a conversation.
     */
    public function star(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'star');

        return $this->requestJson('POST', $endpoint, [], 'Failed to star conversation');
    }

    /**
     * Unstar a conversation.
     */
    public function unstar(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'unstar');

        return $this->requestJson('POST', $endpoint, [], 'Failed to unstar conversation');
    }

    /**
     * Archive a conversation.
     */
    public function archive(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'archive');

        return $this->requestJson('POST', $endpoint, [], 'Failed to archive conversation');
    }

    /**
     * Unarchive a conversation.
     */
    public function unarchive(string $projectSlug, string $contactUuid, string $conversationId): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, $conversationId, 'unarchive');

        return $this->requestJson('POST', $endpoint, [], 'Failed to unarchive conversation');
    }

    /**
     * Get or create a conversation between two participants.
     *
     * @param string $projectSlug
     * @param string $contactUuid
     * @param string $participantUuid
     * @return array
     * @throws GenitIoApiException
     */
    public function getOrCreate(string $projectSlug, string $contactUuid, string $participantUuid): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $contactUuid, null,   $participantUuid);
        dump($endpoint);
        return $this->requestJson('GET', $endpoint, [], 'Failed to fetch conversation with participant');
    }

    /**
     * Build the conversations endpoint.
     */
    protected function buildEndpoint(string $projectSlug, string $contactUuid, ?string $conversationId = null, ?string $suffix = null): string
    {
        $base = "/api/v1/{$projectSlug}/contacts/{$contactUuid}/conversations";

        if ($conversationId !== null) {
            $base .= '/' . $conversationId;
        }

        if ($suffix !== null) {
            $base .= '/' . $suffix;
        }

        return $base;
    }

    /**
     * Perform an HTTP request that returns JSON.
     *
     * @param string $method
     * @param string $endpoint
     * @param array $payloadOrQuery
     * @param string $errorMessage
     * @return array
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
     * @param string $method
     * @param string $endpoint
     * @param array $payload
     * @param string $errorMessage
     * @return bool
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
