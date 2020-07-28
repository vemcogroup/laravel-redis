<?php

namespace Vemcogroup\Redis\Tests;

use Redis;
use Illuminate\Redis\RedisManager;
use Illuminate\Foundation\Application;
use Vemcogroup\Redis\RedisServiceProvider;
use Vemcogroup\Redis\Connectors\VemRedisConnector;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function tearDown(): void
    {
        foreach ($this->connections() as $redis) {
            $redis->flushdb();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [RedisServiceProvider::class];
    }

    public function connections(): array
    {
        $connections = [];

        $host = env('REDIS_HOST', '127.0.0.1');
        $port = env('REDIS_PORT', 6379);
        $driver = 'vredis';

        $connections[] = new RedisManager(new Application, $driver, [
            'cluster' => false,
            'default' => [
                'host' => $host,
                'port' => $port,
                'database' => 6,
                'options' => ['prefix' => 'laravel:'],
                'timeout' => 0.5,
                'persistent' => true,
                'persistent_id' => 'laravel',
            ],
        ]);

        $connections['compression'] = new RedisManager(new Application, $driver, [
            'cluster' => false,
            'default' => [
                'host' => $host,
                'port' => $port,
                'database' => 8,
                'options' => ['compression' => Redis::COMPRESSION_NONE],
                'timeout' => 0.5,
            ],
        ]);

        $connections[] = new RedisManager(new Application, $driver, [
            'cluster' => false,
            'options' => ['serializer' => 3],
            'default' => [
                'host' => $host,
                'port' => $port,
                'database' => 7,
                'options' => ['serializer' => Redis::SERIALIZER_JSON],
                'timeout' => 0.5,
            ],
        ]);

        if (defined('Redis::SERIALIZER_IGBINARY')) {
            $connections[] = new RedisManager(new Application, $driver, [
                'cluster' => false,
                'options' => ['serializer' => 3],
                'default' => [
                    'host' => $host,
                    'port' => $port,
                    'database' => 7,
                    'options' => ['serializer' => Redis::SERIALIZER_IGBINARY],
                    'timeout' => 0.5,
                ],
            ]);
        }

        $connectionsEstablished = [];
        foreach ($connections as $key => $connection) {
            $connection->extend('vredis', function () {
                return new VemRedisConnector;
            });

            if (is_int($key)) {
                $connectionsEstablished[] = $connection->connection();
            } else {
                $connectionsEstablished[$key] = $connection->connection();
            }

        }

        return $connectionsEstablished;
    }
}
