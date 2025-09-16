<?php

namespace App\Exceptions;

use Exception;

class UserNotAuthenticatedException extends Exception
{
    protected $message = 'User not authenticated. Please log in in order to use this method.';
}
