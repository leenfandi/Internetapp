<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class AuthenticateAspectFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'auth.aspect';
    }
}
