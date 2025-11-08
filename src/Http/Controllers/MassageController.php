<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\Exceptions\GenitIoApiException;
use GenitIo\Chat\Services\MessageApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use GenitIo\Chat\Traits\HandlesApiResponses;

class MassageController extends Controller
{
    use HandlesApiResponses;

    protected MessageApiClient $messageApiClient;

    public function __construct(MessageApiClient $messageApiClient)
    {
        $this->messageApiClient = $messageApiClient;
    }

    /**
     * Display a listing of messages for the conversation.
     */
    public function index(Request $request, string $project, string $conversation): JsonResponse
    {
        try {
            $query = array_filter(
                $request->query(),
                static fn($value) => $value !== null && $value !== ''
            );

            $messages = $this->messageApiClient->list(
                $project,
                $conversation,
                $query
            );

            return response()->json($messages);
        } catch (GenitIoApiException $exception) {
            Log::error('Failed to fetch messages', ['exception' => $exception]);
            return $this->apiErrorResponse('Failed to fetch messages', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Store a newly created message.
     */
    public function store(Request $request, string $project, string $conversation): JsonResponse
    {
        $validated = $request->validate([
            'sender_id' => 'required|string',
            'type' => 'required|string|in:text,image,audio,video,document,location,contact,sticker,buttons,list,interactive,reaction,call,link,custom',
            'content' => 'nullable|string',
            'body' => 'nullable|string',
            'data' => 'nullable',
        ]);

        $payload = $this->prepareMessagePayload($validated);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['file'] = [
                'name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'content' => base64_encode($file->get()),
            ];
        }

        try {
            $message = $this->messageApiClient->create(
                $project,
                $conversation,
                $payload
            );

            return response()->json($message, 201);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to create message', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Display the specified message.
     */
    public function show(string $project, string $conversation, string $message): JsonResponse
    {
        try {
            $messageData = $this->messageApiClient->show(
                $project,
                $conversation,
                $message
            );

            return response()->json($messageData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to fetch message', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Update the specified message.
     */
    public function update(Request $request, string $project, string $conversation, string $message): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'sometimes|nullable|string',
            'body' => 'sometimes|nullable|string',
            'type' => 'sometimes|string|in:text,image,audio,video,document,location,contact,sticker,buttons,list,interactive,reaction,call,link,custom',
            'data' => 'sometimes|nullable',
        ]);

        $payload = $this->prepareMessagePayload($validated);

        try {
            $messageData = $this->messageApiClient->update(
                $project,
                $conversation,
                $message,
                $payload
            );

            return response()->json($messageData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to update message', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Remove the specified message.
     */
    public function destroy(string $project, string $conversation, string $message): JsonResponse
    {
        try {
            $this->messageApiClient->delete(
                $project,
                $conversation,
                $message
            );

            return response()->json([
                'success' => true,
                'message' => 'Message deleted successfully.',
            ]);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to delete message', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(string $project, string $conversation, string $message): JsonResponse
    {
        try {
            $messageData = $this->messageApiClient->markAsRead(
                $project,
                $conversation,
                $message
            );

            return response()->json($messageData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to mark message as read', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }

    /**
     * Mark the message as delivered.
     */
    public function markAsDelivered(string $project, string $conversation, string $message): JsonResponse
    {
        try {
            $messageData = $this->messageApiClient->markAsDelivered(
                $project,
                $conversation,
                $message
            );

            return response()->json($messageData);
        } catch (GenitIoApiException $exception) {
            return $this->apiErrorResponse('Failed to mark message as delivered', $exception);
        } catch (\Throwable $exception) {
            return $this->unexpectedErrorResponse($exception);
        }
    }
}
