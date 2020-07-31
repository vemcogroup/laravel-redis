# Laravel Redis

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vemcogroup/laravel-redis.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-redis)
[![Total Downloads](https://img.shields.io/packagist/dt/vemcogroup/laravel-redis.svg?style=flat-square)](https://packagist.org/packages/vemcogroup/laravel-redis)
![tests](https://github.com/vemcogroup/laravel-redis/workflows/tests/badge.svg)

## Description

Enhancements to redis driver, such as enabling a serializer and/or compression


## Installation

You can install the package via composer:

```bash
composer require vemcogroup/laravel-redis
```

## Usage

Start by selection the new driver `vredis` in you `.env` file:

```php
REDIS_CLIENT=vredis
```

*Compression*  

To use compression you have to set the type in `database.php` for your redis connection:

```php
'default' => [
    'url' => env('REDIS_URL'),
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'password' => env('REDIS_PASSWORD', null),
    'port' => env('REDIS_PORT', '6379'),
    'database' => env('REDIS_DB', '0'),
    'options' => [
        'compression' => Redis::COMPRESSION_NONE,
    ],
],
```

You can use any of the Redis compressions available from you installation:  
`Redis::COMPRESSION_NONE`, `Redis::COMPRESSION_ZSTD`, `Redis::COMPRESSION_LZ4`

*Serializer*  

To use serialization you have to set the type in `database.php` for your redis connection:

```php
'default' => [
    'url' => env('REDIS_URL'),
    'host' => env('REDIS_HOST', '127.0.0.1'),
    'password' => env('REDIS_PASSWORD', null),
    'port' => env('REDIS_PORT', '6379'),
    'database' => env('REDIS_DB', '0'),
    'options' => [
        'serializer' => Redis::SERIALIZER_NONE,
    ],
],
```

You can use any of the Redis serializers available from you installation:    
`Redis::SERIALIZER_NONE`, `Redis::SERIALIZER_PHP`, `Redis::SERIALIZER_IGBINARY`, `Redis::SERIALIZER_MSGPACK`, `Redis::SERIALIZER_JSON`
 



