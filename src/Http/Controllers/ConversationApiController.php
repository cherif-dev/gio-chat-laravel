<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use GenitIo\Chat\Models\Contact;
use GenitIo\Chat\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConversationApiController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get conversation for a contact with a specific participant.
     *
     * @param Request $request
     * @param Contact $contact - Route model binding by uuid
     * @param string $participant - Participant UUID
     * @return JsonResponse
     */
    public function getConversation(Request $request, Contact $contact, string $participant): JsonResponse
    {
        try {
            // Verify that the authenticated user owns this contact
            $user = $request->user();

            if ($user && $contact->contactable_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access to this contact.',
                ], 403);
            }

            // Get conversation from Genit IO API
            $conversationData = $this->chatService->getConversation(
                $contact->contact_id,
                $participant
            );

            return response()->json([
                'message' => 'Conversation retrieved successfully.',
                'data' => $conversationData,
            ]);
        } catch (GenitIoApiException $e) {
            return response()->json([
                'message' => 'Failed to retrieve conversation from Genit IO.',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
