<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FileFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'file.service';
    }
}
