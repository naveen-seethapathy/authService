<?php

namespace App\Http\Services;

use App\User;
use Illuminate\Support\Str;

class LoginService
{
    public static function generateToken()
    {
        return base64_encode(Str::random(40).time());
    }

    public static function cacheAuthToken($token, $userId)
    {
        $cache = new RedisService();
        $expiry = 8*60*60; //8 hours
        $key = sprintf('auth:user:token:%s', $token);
        $cache->setData($key, $userId, $expiry);
    }

    public static function setAuthToken(User $user)
    {
        // Create random Auth token for API Access
        $token = self::generateToken();
        self::cacheAuthToken($token, $user->id);
        return $token;
    }
}
