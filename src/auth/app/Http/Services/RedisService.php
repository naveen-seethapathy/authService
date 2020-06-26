<?php

namespace App\Http\Services;

use Predis\Client;
use Illuminate\Support\Facades\Config;

class RedisService
{
    public $client = [];

    public $type;

    public function __construct($type = 'cache')
    {
        $this->client[$type] = $this->getClient($type);
        $this->type = $type;
    }

    public function getClient($type)
    {
        $client = false;
        if (empty($this->client[$type])) {
            switch ($type) {
                case 'cache':
                default:
                    try {
                        $client = new Client(['scheme' => 'tcp',
                            'host' => env('REDIS_HOST', 'localhost'),
                            'port' => env('REDIS_PORT', 6379),
                            'password' => env('REDIS_PASSWORD', null),
                            'timeout' => 30]);
                    }
                    catch
                    (\Exception $e) {
                        return false;
                    }
                    break;
            }
        }
        return $client;
    }

    public function getData($key)
    {
        return $this->client[$this->type]->get($key);
    }

    public function getMultipleData($keys = [])
    {
        $responses = [];
        if (!empty($keys)) {
            $responses = $this->client[$this->type]->pipeline(function ($pipe) use ($keys) {
                foreach ($keys as $key) {
                    $pipe->get($key);
                }
            });
        }
        return $responses;
    }

    public function setData($key, $value, $expiry = 0)
    {
        $return = $this->client[$this->type]->set($key, $value);
        if ($expiry > 0) {
            $this->client[$this->type]->expire($key, $expiry);
        }
        return $return;
    }

    public function expire($key, $ttl = 0)
    {
        if ($ttl > 0) {
            $this->client[$this->type]->expire($key, $ttl);
        }
    }

    public function delData($key)
    {
        return $this->client[$this->type]->del($key);
    }

    public function addSetMember($set, $member)
    {
        return $this->client[$this->type]->sadd($set, $member);
    }

    public function getSetMembers($set)
    {
        return $this->client[$this->type]->smembers($set);
    }

    public function checkSetMember($set, $member)
    {
        return $this->client[$this->type]->sismember($set, $member);
    }

    public function getSortedSetByScore($set, $min, $max)
    {
        return $this->client[$this->type]->zrangebyscore($set, $min, $max);
    }

    public function delSortedSetByScore($set, $min, $max)
    {
        return $this->client[$this->type]->zremrangebyscore($set, $min, $max);
    }

    public function pushToList($list, $record)
    {
        return $this->client[$this->type]->lpush($list, $record);
    }

    public function popFromList($list)
    {
        return $this->client[$this->type]->lpop($list);
    }

    public function getListData($list)
    {
        return $this->client[$this->type]->lrange($list, 0 , -1);
    }

    public function delFromList($list, $record)
    {
        return $this->client[$this->type]->lrem($list, 0, $record);
    }

    public function getHashData($key, $field)
    {
        return $this->client[$this->type]->hget($key, $field);
    }

    public function isHashExists($key, $field)
    {
        return $this->client[$this->type]->hexists($key, $field);
    }

    public function getAllHashData($key)
    {
        return $this->client[$this->type]->hgetall($key);
    }

    public function hashIncrBy($key, $field, $increment)
    {
        return $this->client[$this->type]->hincrby($key, $field, $increment);
    }

    public function setHashData($key, $field, $value)
    {
        return $this->client[$this->type]->hset($key, $field, $value);
    }

    public function setMultiHashData($key, $values)
    {
        return $this->client[$this->type]->hmset($key, $values);
    }

    public function delHashData($key, $field)
    {
        return $this->client[$this->type]->hdel($key, $field);
    }

    public function getMultipleHashData($hashes = [])
    {
        $responses = [];
        if (!empty($hashes)) {
            $responses = $this->client[$this->type]->pipeline(function ($pipe) use ($hashes) {
                foreach ($hashes as $hash) {
                    $pipe->hgetall($hash);
                }
            });
        }

        return $responses;
    }

    public function setMultipleHashData($hashes = [])
    {
        $responses = [];
        if (!empty($hashes)) {
            $responses = $this->client[$this->type]->pipeline(function ($pipe) use ($hashes) {
                foreach ($hashes as $hash) {
                    $pipe->hset($hash['key'], $hash['field'], $hash['value']);
                }
            });
        }

        return $responses;
    }

    public function delMultipleHashData($hashes = [])
    {
        $responses = [];
        if (!empty($hashes)) {
            $responses = $this->client[$this->type]->pipeline(function ($pipe) use ($hashes) {
                foreach ($hashes as $hash) {
                    $pipe->del($hash);
                }
            });
        }

        return $responses;
    }

    /**
     * return value of redis key along with its ttl
     * @param $key
     * @return mixed
     */
    public function getDataWithTTL($key)
    {
        $responses = $this->client[$this->type]->pipeline(function ($pipe) use ($key) {
            $pipe->get($key);
            $pipe->ttl($key);
        });
        return $responses;
    }
}
