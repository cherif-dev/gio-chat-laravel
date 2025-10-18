<?php

namespace GenitIo\Chat\Facades;

use Illuminate\Support\Facades\Facade;

class Contact extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chat';
    }
}
