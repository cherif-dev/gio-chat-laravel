<?php

namespace GenitIo\Chat\Traits;

use GenitIo\Chat\Models\Contact;
use GenitIo\Chat\Services\ChatService;
use GenitIo\Chat\Services\GenitIoApiClient;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

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
    public function getOrCreateContactUuid(): ?string
    {
        try {
            $c = null;
            if (!$this->hasContact()) {
                $c = app(ChatService::class)->createContact($this);
                if (!$c) {
                    throw new \Exception('Contact ID is null');
                }
                Log::info('Contact created', ['contact_id' => $c->contact_id]);
                return $c->contact_id;
            } else {
                $c = $this->contact()->first();
                if (!$c) {
                    Log::warning('Contact expected but not found', ['user_id' => $this->getKey(), 'project' => $this->genit_io_project()]);
                    return null;
                }
                Log::info('Contact found', ['contact_id' => $c->contact_id]);
                return $c->contact_id;
            }
        } catch (\Throwable $th) {
            Log::error('Error getting or creating contact: ', [$th]);
            return null;
        }
    }
}
