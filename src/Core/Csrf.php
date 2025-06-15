<?php

namespace App\Core;

use App\Core\Session;

class Csrf
{
    public function generateToken()
    {
        if (empty(Session::get('csrf_token'))) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return Session::get('csrf_token');
    }

    public function validateToken($token)
    {
        $sessionToken = Session::get('csrf_token');
        if (empty($sessionToken) || $sessionToken !== $token) {
            return false;
        }
        return true;
    }
}
