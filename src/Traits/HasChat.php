<?php

namespace GenitIo\Chat\Traits;

use GenitIo\Chat\Models\Contact;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasContact
{
    /**
     * Get the user's contact.
     */
    public function contact(): HasOne
    {
        return $this->hasOne(Contact::class, 'user_id');
    }

    /**
     * Create the user's contact.
     */
    public function createContact(?string $title = null, ?string $contactId = null): Contact
    {
        return Contact::create([
            'user_id' => $this->id,
            'contact_id' => $contactId,
        ]);
    }

    /**
     * Get the user's contact ID (UUID).
     */
    public function getChatId(): ?string
    {
        return $this->chat?->chat_id;
    }

    /**
     * Check if user has a contact.
     */
    public function hasContact(): bool
    {
        return $this->contact()->exists();
    }
}
