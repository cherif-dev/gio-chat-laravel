<?php

namespace GenitIo\Chat\Traits;

use GenitIo\Chat\Models\Contact;
use GenitIo\Chat\Services\ChatService;
use GenitIo\Chat\Services\GenitIoApiClient;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasContact
{

    public function genit_io_project(): string
    {
        return config('chat.project');
    }

    /**
     * Get the user's contact.
     */
    public function contact(): HasOne
    {
        $project = $this->genit_io_project();
        return $this->hasOne(Contact::class, 'user_id')->where('project_slug', $project);
    }

    /**
     * Get the user's contact ID (UUID).
     */
    public function getContactUuid(): ?string
    {
        return $this->contact?->contact_id;
    }

    /**
     * Check if user has a contact.
     */
    public function hasContact(): bool
    {
        return $this->contact()->exists();
    }

    /**
     * Get or create the user's contact UUID.
     */
    public function getOrCreateContactUuid()
    {
        $contact = null;
        if (!$this->hasContact()) {
            $contact = app(ChatService::class)->createContact($this);
        } else {
            $contact = $this->contact;
        }
        if (!$contact) {
            throw new \Exception('User has no contact');
        }
        return $contact->contact_id;
    }
}
