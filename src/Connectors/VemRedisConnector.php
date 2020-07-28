<?php

namespace Vemcogroup\Redis\Connectors;

use Redis;
use RedisCluster;
use LogicException;
use Illuminate\Support\Arr;
use Vemcogroup\Redis\Connections\VemRedisConnection;
use Illuminate\Redis\Connectors\PhpRedisConnector;
use Illuminate\Support\Facades\Redis as RedisFacade;

class VemRedisConnector extends PhpRedisConnector
{
    public function connect(array $config, array $options): VemRedisConnection
    {
        $connector = function () use ($config, $options) {
            return $this->createClient(
                array_merge(
                    // local $config always overrules global $options
                    $options,
                    $config,
                    Arr::pull($config, 'options', []),
                )
            );
        };

        return new VemRedisConnection($connector(), $connector, $config);
    }

    protected function createClient(array $config): Redis
    {
        return tap(new Redis, function ($client) use ($config) {
            if ($client instanceof RedisFacade) {
                throw new LogicException(
                    extension_loaded('redis')
                        ? 'Please remove or rename the Redis facade alias in your "app" configuration file in order to avoid collision with the PHP Redis extension.'
                        : 'Please make sure the PHP Redis extension is installed and enabled.'
                );
            }

            $this->establishConnection($client, $config);

            if (!empty($config['password'])) {
                $client->auth($config['password']);
            }

            if (isset($config['database'])) {
                $client->select((int) $config['database']);
            }

            if (!empty($config['prefix'])) {
                $client->setOption(Redis::OPT_PREFIX, $config['prefix']);
            }

            if (!empty($config['read_timeout'])) {
                $client->setOption(Redis::OPT_READ_TIMEOUT, $config['read_timeout']);
            }

            if (!empty($config['scan'])) {
                $client->setOption(Redis::OPT_SCAN, $config['scan']);
            }

            if (!empty($config['serializer'])) {
                $client->setOption(Redis::OPT_SERIALIZER, $config['serializer']);
            }

            if (!empty($config['compression'])) {
                $client->setOption(Redis::OPT_COMPRESSION, $config['compression']);

                if (!empty($config['compression_level'])) {
                    $client->setOption(Redis::OPT_COMPRESSION_LEVEL, $config['compression_level']);
                }
            }
        });
    }

    protected function createRedisClusterInstance(array $servers, array $options): RedisCluster
    {
        $parameters = [
            null,
            array_values($servers),
            $options['timeout'] ?? 0,
            $options['read_timeout'] ?? 0,
            isset($options['persistent']) && $options['persistent'],
        ];

        if (version_compare(phpversion('redis'), '4.3.0', '>=')) {
            $parameters[] = $options['password'] ?? null;
        }

        return tap(new RedisCluster(...$parameters), function ($client) use ($options) {
            if (!empty($options['prefix'])) {
                $client->setOption(RedisCluster::OPT_PREFIX, $options['prefix']);
            }

            if (!empty($options['scan'])) {
                $client->setOption(RedisCluster::OPT_SCAN, $options['scan']);
            }

            if (!empty($options['failover'])) {
                $client->setOption(RedisCluster::OPT_SLAVE_FAILOVER, $options['failover']);
            }

            if (!empty($options['serializer'])) {
                $client->setOption(RedisCluster::OPT_SERIALIZER, $options['serializer']);
            }

            if (!empty($options['compression'])) {
                $client->setOption(Redis::OPT_COMPRESSION, $options['compression']);

                if (!empty($options['compression_level'])) {
                    $client->setOption(Redis::OPT_COMPRESSION_LEVEL, $options['compression_level']);
                }
            }
        });
    }
}

