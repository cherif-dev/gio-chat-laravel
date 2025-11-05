<?php

namespace GenitIo\Chat\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array createContact(array $data)
 * @method static array updateContact(string $contactId, array $data)
 * @method static array getContact(string $contactId)
 * @method static bool deleteContact(string $contactId)
 *
 * @see \GenitIo\Chat\Services\GenitIoApiClient
 */
class GenitIoApi extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'api.genit.io';
    }
}