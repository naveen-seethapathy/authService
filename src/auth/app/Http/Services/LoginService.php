<?php

namespace App\Http\Services;

use App\User;
use Illuminate\Support\Str;

class LoginService
{
    /**
     * Generate api toekn
     *
     * @return string
     */
    public static function generateToken()
    {
        return base64_encode(Str::random(40).time());
    }

    /**
     * Cache token in redis
     *
     * @param $token
     * @param $userId
     */
    public static function cacheAuthToken($token, $userId)
    {
        $cache = new RedisService();
        $expiry = 8*60*60; //8 hours
        $key = sprintf('auth:user:token:%s', $token);
        $cache->setData($key, $userId, $expiry);
    }

    /**
     * Set auth token after register / login
     *
     * @param User $user
     *
     * @return string
     */
    public static function setAuthToken(User $user)
    {
        // Create random Auth token for API Access
        $token = self::generateToken();
        self::cacheAuthToken($token, $user->id);
        return $token;
    }
}
