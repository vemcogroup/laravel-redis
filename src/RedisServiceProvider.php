<?php

namespace Vemcogroup\Redis;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\ServiceProvider;
use Vemcogroup\Redis\Connectors\VemRedisConnector;

class RedisServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        throw_if(!extension_loaded('redis'),
            'The redis extension is not installed. Please install the extension to enable ' . __CLASS__
        );

        Redis::extend('vredis', function () {
            return new VemRedisConnector;
        });
    }
}
