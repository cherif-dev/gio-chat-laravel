<?php

namespace GenitIo\Chat\Services;

use GenitIo\Chat\Models\Contact;

class ChatService
{
    /**
     * Get or create a chat for the user.
     * Each user can have only one chat.
     */
    public function getContact(int $userId): Contact
    {
        return Contact::where('user_id', $userId)->first();
    }

    /**
     * Create a chat for the user.
     */
    public function createContact(int $userId, ?string $chatId = null): Contact
    {
        return Contact::create(
            [
                'user_id' => $userId,
                'chat_id' => $chatId,
            ]
        );
    }

    /**
     * Get user's chat ID (UUID).
     */
    public function getContactId(int $userId): ?string
    {
        $chat = Contact::where('user_id', $userId)->first();
        return $chat?->chat_id;
    }


    /**
     * Delete user's chat and all associated messages.
     */
    public function deleteContact(int $userId): bool
    {
        $chat = Contact::where('user_id', $userId)->first();

        if ($chat) {
            return $chat->delete();
        }

        return false;
    }
}
