<?php

namespace GenitIo\Chat\Services;

use GenitIo\Chat\Exceptions\GenitIoApiException;

class MessageApiClient extends GenitIoApiClient
{
    /**
     * Build the messages endpoint.
     */
    protected function buildEndpoint(
        string $projectSlug,
        string $conversationUuid,
        ?string $messageUuid = null,
        ?string $suffix = null
    ): string {
        $base = "/api/v1/{$this->project}/chat/{$conversationUuid}/messages";

        if ($messageUuid !== null) {
            $base .= '/' . $messageUuid;
        }

        if ($suffix !== null) {
            $base .= '/' . $suffix;
        }

        return $base;
    }



    /**
     * Fetch a paginated list of messages for the conversation.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param array $query
     * @return array
     * @throws GenitIoApiException
     */
    public function list(string $projectSlug, string $conversationUuid, array $query = []): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid);

        return $this->requestJson('GET', $endpoint, $query, 'Failed to list messages');
    }

    /**
     * Create a new message for the conversation.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function create(string $projectSlug, string $conversationUuid, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid);

        return $this->requestJson('POST', $endpoint, $payload, 'Failed to create message');
    }

    /**
     * Retrieve a message from the conversation.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param string $messageUuid
     * @return array
     * @throws GenitIoApiException
     */
    public function show(string $projectSlug, string $conversationUuid, string $messageUuid): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid, $messageUuid);

        return $this->requestJson('GET', $endpoint, [], 'Failed to fetch message');
    }

    /**
     * Update an existing message.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param string $messageUuid
     * @param array $payload
     * @return array
     * @throws GenitIoApiException
     */
    public function update(string $projectSlug, string $conversationUuid, string $messageUuid, array $payload): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid, $messageUuid);

        return $this->requestJson('PUT', $endpoint, $payload, 'Failed to update message');
    }

    /**
     * Delete a message from the conversation.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param string $messageUuid
     * @return bool
     * @throws GenitIoApiException
     */
    public function delete(string $projectSlug, string $conversationUuid, string $messageUuid): bool
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid, $messageUuid);

        return $this->requestBoolean('DELETE', $endpoint, [], 'Failed to delete message');
    }

    /**
     * Mark the message as read.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param string $messageUuid
     * @return array
     * @throws GenitIoApiException
     */
    public function markAsRead(string $projectSlug, string $conversationUuid, string $messageUuid): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid, $messageUuid, 'read');

        return $this->requestJson('POST', $endpoint, [], 'Failed to mark message as read');
    }

    /**
     * Mark the message as delivered.
     *
     * @param string $projectSlug
     * @param string $conversationUuid
     * @param string $messageUuid
     * @return array
     * @throws GenitIoApiException
     */
    public function markAsDelivered(string $projectSlug, string $conversationUuid, string $messageUuid): array
    {
        $endpoint = $this->buildEndpoint($projectSlug, $conversationUuid, $messageUuid, 'delivered');

        return $this->requestJson('POST', $endpoint, [], 'Failed to mark message as delivered');
    }
}