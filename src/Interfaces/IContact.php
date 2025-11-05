<?php

namespace GenitIo\Chat\Interfaces;

interface IContact
{
    /**
     * Convert the User model to a Genit IO Contact data object.
     */
    public function toContactData(): array;
}
