<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use GenitIo\Chat\Models\Contact;
use GenitIo\Chat\Services\ConversationApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use GenitIo\Chat\Traits\HandlesApiResponses;

class ConversationController extends Controller
{
    use HandlesApiResponses;

    public function __construct(
        protected ConversationApiClient $conversationApiClient
    ) {}

    /**
     * List conversations for a contact.
     */
    public function index(Request $request, string $project, Contact $contact): JsonResponse
    {

        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $query = array_filter(
                $request->query(),
                static fn($value) => $value !== null && $value !== ''
            );

            $conversations = $this->conversationApiClient->list(
                $project,
                $contact->contact_id,
                $query
            );

            return response()->json($conversations);
        } catch (GenitIoApiException $exception) {
            Log::error('Failed to fetch conversations', ['exception' => $exception]);
            return $this->apiErrorResponse('Failed to fetch conversations', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
            Log::error('Failed to fetch conversations', ['exception' => $exception]);
        }
    }

    /**
     * Create a new conversation for the contact.
     */
    public function store(Request $request, string $project, Contact $contact): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        $validated = $request->validate([
            'contact_id' => 'required|string',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|string|in:support,sales,general,complaint,feedback,technical',
            'priority' => 'nullable|string|in:low,normal,high,urgent',
            'status' => 'nullable|string|in:active,pending,resolved,closed,archived',
            'tags' => 'nullable|array',
            'custom_fields' => 'nullable|array',
            'is_private' => 'boolean',
            'auto_assign_enabled' => 'boolean',
            'notifications_enabled' => 'boolean',
        ]);

        try {
            $conversation = $this->conversationApiClient->create(
                $project,
                $contact->contact_id,
                $validated
            );

            return response()->json($conversation, 201);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to create conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Show a specific conversation.
     */
    public function show(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->show(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to fetch conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Update a conversation.
     */
    public function update(Request $request, string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        $validated = $request->validate([
            'title' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'type' => 'sometimes|nullable|string|in:support,sales,general,complaint,feedback,technical',
            'priority' => 'sometimes|nullable|string|in:low,normal,high,urgent',
            'status' => 'sometimes|nullable|string|in:active,pending,resolved,closed,archived',
            'tags' => 'sometimes|nullable|array',
            'custom_fields' => 'sometimes|nullable|array',
            'is_private' => 'sometimes|boolean',
            'auto_assign_enabled' => 'sometimes|boolean',
            'notifications_enabled' => 'sometimes|boolean',
        ]);

        try {
            $conversationData = $this->conversationApiClient->update(
                $project,
                $contact->contact_id,
                $conversation,
                $validated
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to update conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Delete a conversation.
     */
    public function destroy(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $this->conversationApiClient->delete(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversation deleted successfully.',
            ]);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to delete conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Assign a conversation to a participant.
     */
    public function assign(Request $request, string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        $validated = $request->validate([
            'assigned_to' => 'required|string',
        ]);

        try {
            $conversationData = $this->conversationApiClient->assign(
                $project,
                $contact->contact_id,
                $conversation,
                $validated
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to assign conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Unassign a conversation.
     */
    public function unassign(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->unassign(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to unassign conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(Request $request, string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        $validated = $request->validate([
            'status' => 'required|string|in:active,pending,resolved,closed,archived',
        ]);

        try {
            $conversationData = $this->conversationApiClient->updateStatus(
                $project,
                $contact->contact_id,
                $conversation,
                $validated
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to update conversation status', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Pin a conversation.
     */
    public function pin(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->pin(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to pin conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Unpin a conversation.
     */
    public function unpin(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->unpin(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to unpin conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Star a conversation.
     */
    public function star(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->star(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to star conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Unstar a conversation.
     */
    public function unstar(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->unstar(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to unstar conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Archive a conversation.
     */
    public function archive(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->archive(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to archive conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Unarchive a conversation.
     */
    public function unarchive(string $project, Contact $contact, string $conversation): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->unarchive(
                $project,
                $contact->contact_id,
                $conversation
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to unarchive conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Get or create a conversation with a participant.
     */
    public function getConversation(string $project, Contact $contact, string $participant): JsonResponse
    {
        if ($response = $this->ensureContactInProject($contact, $project)) {
            return $response;
        }

        try {
            $conversationData = $this->conversationApiClient->getOrCreate(
                $project,
                $contact->contact_id,
                $participant
            );

            return response()->json($conversationData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to retrieve conversation', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    protected function ensureContactInProject(Contact $contact, string $project): ?JsonResponse
    {
        if ($contact->project_slug !== $project) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found in this project.',
            ], 404);
        }

        return null;
    }
}
