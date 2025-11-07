<?php

namespace GenitIo\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Contact extends Model
{
    protected $table = 'genit_io_contacts';

    protected $fillable = [
        'contact_id',
        'project_slug',
        'user_id',
    ];

    protected $casts = [];

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'contact_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('chat.user_model', 'App\Models\User'));
    }
}
