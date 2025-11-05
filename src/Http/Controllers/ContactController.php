<?php

namespace GenitIo\Chat\Http\Controllers;

use GenitIo\Chat\DataTransferObjects\ContactData;
use GenitIo\Chat\Exceptions\GenitIoApiException;
use GenitIo\Chat\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Create a contact for the authenticated user in Genit IO.
     */
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check if user already has a contact
        if ($user->hasContact()) {
            return response()->json([
                'message' => 'User already has a contact.',
                'contact_id' => $user->getChatId(),
            ], 409);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'display_name' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:member,admin,agent',
            'is_agent' => 'nullable|boolean',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create contact data from user and request
            $contactData = ContactData::fromUser($user, $request->only([
                'phone',
                'display_name',
                'role',
                'is_agent',
                'custom_fields',
            ]));

            // Create the contact in both Genit IO and local DB
            $contact = $this->chatService->createContact($user->id, $contactData);

            return response()->json([
                'message' => 'Contact created successfully.',
                'contact_id' => $contact->contact_id,
            ], 201);
        } catch (GenitIoApiException $e) {
            return response()->json([
                'message' => 'Failed to create contact in Genit IO.',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get the authenticated user's contact information.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $contactId = $user->getChatId();

        if (!$contactId) {
            return response()->json([
                'message' => 'User does not have a contact.',
            ], 404);
        }

        return response()->json([
            'contact_id' => $contactId,
        ]);
    }

    /**
     * Update the authenticated user's contact information.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->hasContact()) {
            return response()->json([
                'message' => 'User does not have a contact.',
            ], 404);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'display_name' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:member,admin,agent',
            'is_agent' => 'nullable|boolean',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Create contact data from user and request
            $contactData = ContactData::fromUser($user, $request->only([
                'phone',
                'display_name',
                'role',
                'is_agent',
                'custom_fields',
            ]));

            // Update the contact in Genit IO
            $this->chatService->updateContact($user->id, $contactData);

            return response()->json([
                'message' => 'Contact updated successfully.',
                'contact_id' => $user->getChatId(),
            ]);
        } catch (GenitIoApiException $e) {
            return response()->json([
                'message' => 'Failed to update contact in Genit IO.',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Sync contact data from Genit IO.
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->hasContact()) {
            return response()->json([
                'message' => 'User does not have a contact.',
            ], 404);
        }

        try {
            $contactData = $this->chatService->syncContactFromGenitIo($user->id);

            return response()->json([
                'message' => 'Contact synced successfully.',
                'data' => $contactData,
            ]);
        } catch (GenitIoApiException $e) {
            return response()->json([
                'message' => 'Failed to sync contact from Genit IO.',
                'error' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Delete the authenticated user's contact.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if (!$user->hasContact()) {
            return response()->json([
                'message' => 'User does not have a contact.',
            ], 404);
        }

        try {
            $deleted = $this->chatService->deleteContact($user->id);

            if ($deleted) {
                return response()->json([
                    'message' => 'Contact deleted successfully.',
                ]);
            }

            return response()->json([
                'message' => 'Failed to delete contact.',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
