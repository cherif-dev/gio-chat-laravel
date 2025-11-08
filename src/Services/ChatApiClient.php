<?php

namespace GenitIo\Chat\Services;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

class ChatApiClient extends GenitIoApiClient
{
    /**
     * Build the chat endpoint.
     */

    protected function buildEndpoint(string $suffix): string
    {
        return "/api/v1/{$this->project}/chat/{$suffix}";
    }

    /**
     * Get a conversation between two contacts.
     */
    public function getOrCreateConversation(string $contactUuid, string $participantUuid): array
    {
        $endpoint = $this->buildEndpoint($contactUuid . '/' . $participantUuid);
        return $this->requestJson('GET', $endpoint, [], 'Failed to get conversation');
    }
}
