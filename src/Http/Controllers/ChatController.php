<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use GenitIo\Chat\Services\ChatApiClient;
use GenitIo\Chat\Services\ConversationApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use GenitIo\Chat\Traits\HandlesApiResponses;

class ChatController extends Controller
{
    use HandlesApiResponses;

    public function __construct(
        protected ChatApiClient $chatApiClient,
        protected ConversationApiClient $conversationApiClient
    ) {}

    public function get_or_create_conversation(string $project, string $contact, string $participant): JsonResponse
    {
        try {
            $conversationData = $this->chatApiClient->getOrCreateConversation($contact, $participant);
            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to get or create conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }
}
