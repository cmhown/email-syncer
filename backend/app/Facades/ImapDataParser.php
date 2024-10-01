<?php

namespace App\Facades;

use App\Services\ImapDataParser as ServicesImapDataParser;
use Illuminate\Support\Facades\Facade;

class ImapDataParser extends Facade
{

    protected static function getFacadeAccessor()
    {
        return ServicesImapDataParser::class;
    }
}
