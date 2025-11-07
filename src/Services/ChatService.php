<?php

namespace GenitIo\Chat\Services;

use GenitIo\Chat\Models\Contact;
use GenitIo\Chat\DataTransferObjects\IContact;
use GenitIo\Chat\Exceptions\GenitIoApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use GenitIo\Chat\Services\GenitIoApiClient;

class ChatService
{
    protected GenitIoApiClient $apiClient;

    public function __construct(GenitIoApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Get a contact for the user.
     */
    public function getContact(int $userId): ?Contact
    {
        return Contact::where('user_id', $userId)->first();
    }

    /**
     * Create a contact for the user in both local DB and Genit IO.
     *
     * @param int $userId
     * @param IContact $contact
     * @return Contact
     * @throws GenitIoApiException
     */
    public function createContact($user): Contact
    {
        $userId = $user->id;

        return DB::transaction(function () use ($userId, $user) {
            // Create contact in Genit IO API first
            $userContacData = $user->toContactData();
            $genitIoResponse = $this->apiClient->createContact($userContacData);
            // Extract contact_id from Genit IO response
            $createdContact = $genitIoResponse['contact'];
            $contact_uuid = $createdContact['uuid'] ?? null;
            $project_slug = $createdContact['project']['slug'] ?? null;
            if (!$contact_uuid) {
                throw new GenitIoApiException('Genit IO API did not return a contact ID');
            }
            // Store contact locally
            return Contact::create([
                'user_id' => $userId,
                'project_slug' => $project_slug,
                'contact_id' => $contact_uuid,
            ]);
        });
    }

    /**
     * Get or create a contact for the user.
     *
     * @param int $userId
     * @param IContact $contact
     * @return Contact
     * @throws GenitIoApiException
     */
    public function getOrCreateContact(int $userId, IContact $contact): Contact
    {
        $userContact = $this->getContact($userId);
        if ($userContact) {
            return $userContact;
        }
        return $this->createContact($userId, $contact);
    }

    /**
     * Update a contact in both local DB and Genit IO.
     *
     * @param int $userId
     * @param IContact $contact
     * @return Contact
     * @throws GenitIoApiException
     */
    public function updateContact(int $userId, IContact $contact): Contact
    {
        $userContact = $this->getContact($userId);

        if ($userContact) {
            throw new \RuntimeException("Contact not found for user ID: {$userId}");
        }

        // Update in Genit IO API
        $this->apiClient->updateContact($userContact->contact_id, $contact->toArray());

        return $userContact;
    }

    /**
     * Get user's contact ID (UUID).
     */
    public function getContactId(int $userId): ?string
    {
        $contact = $this->getContact($userId);
        return $contact?->contact_id;
    }

    /**
     * Sync contact data from Genit IO API.
     *
     * @param int $userId
     * @return array|null
     * @throws GenitIoApiException
     */
    public function syncContactFromGenitIo(int $userId): ?array
    {
        $contact = $this->getContact($userId);

        if (!$contact) {
            return null;
        }

        try {
            return $this->apiClient->getContact($contact->contact_id);
        } catch (GenitIoApiException $e) {
            Log::warning("Failed to sync contact from Genit IO for user {$userId}: {$e->getMessage()}");
            throw $e;
        }
    }

    /**
     * Delete user's contact from both local DB and Genit IO.
     *
     * @param int $userId
     * @param bool $deleteFromGenitIo Whether to delete from Genit IO API as well
     * @return bool
     */
    public function deleteContact(int $userId, bool $deleteFromGenitIo = true): bool
    {
        $contact = $this->getContact($userId);

        if (!$contact) {
            return false;
        }

        return DB::transaction(function () use ($contact, $deleteFromGenitIo) {
            // Delete from Genit IO API first if requested
            if ($deleteFromGenitIo) {
                try {
                    $this->apiClient->deleteContact($contact->contact_id);
                } catch (GenitIoApiException $e) {
                    Log::warning("Failed to delete contact from Genit IO: {$e->getMessage()}");
                    // Continue with local deletion even if Genit IO deletion fails
                }
            }

            // Delete from local database
            return $contact->delete();
        });
    }

    /**
     * Get conversation for a contact with a specific participant.
     *
     * @param string $contactId Contact UUID
     * @param string|null $participantId Participant UUID
     * @return array Conversation data from Genit IO API
     * @throws GenitIoApiException
     */
    public function getConversation(string $contactId, string $participantId): array
    {
        return $this->apiClient->getConversation($contactId, $participantId);
    }
}
