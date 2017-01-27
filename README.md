# Moesif Laravel Middlware

[Source Code on GitHub](https://github.com/moesif/moesif-laravel)

A Middleware for PHP Laravel (> 5.1) automatically captures HTTP traffic with just a few lines of code.

## How To Install

Via Composer

```bash
$ composer require moesif/moesif-laravel
```
or add 'moesif/moesif-laravel' to your composer.json file accordingly.

## How To Setup

### Add Service Provider

```php

// within config/app.php

'providers' => [
    //
    Moesif\Middleware\MoesifLaravelServiceProvider::class,
];
```

### Add to Middleware

If website root is your API, add to the root level:

```php

// within App/Http/Kernel.php

protected $middleware = [
    //
    \Moesif\Middleware\MoesifLaravel::class,
];

```

If API under specific route group, add to your route group:

```php
// within App/Http/Kernel.php

protected $middlewareGroups = [
    //
    'api' => [
        //
        \Moesif\Middleware\MoesifLaravel::class,
    ],
];
```

To track only certain routes, use route specific middleware setup.


### Publish the package config file

```bash
$ php artisan vendor:publish --provider="Moesif\Middleware\MoesifLaravelServiceProvider"
```

### Setup config

Edit `config/moesif.php` file.

```php

// within config/moesif.php

return [
    //
    'applicationId' => 'YOUR APPLICATION ID',
];
```

The applicationId is required, you can obtain your applicationId by logging onto [www.moesif.com](https://www.moesif.com) underneath settings

For other configuration options, see below.

## Configuration options

You can define Moesif configuration options in the `config/moesif.php` file. Some of these fields are functions.

#### applicationId:
Type: `String`
Required, a string that identifies your application.

#### identifyUserId
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for userId. Moesif automatically obtains end userId via $request->user()['id'], In case you use a non standard way of injecting user into $request or want to override userId, you can do so with identifyUserId.

```php

// within config/moesif.php

$identifyUserId = function($request, $response) {
    // $user = $request->user();
    // return $user['id'];

    return 'end_user_id';
};
```

```php
return [
  //
  'identifyUserId' => $identifyUserId
];
```

#### identifySessionId
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for sessionId. Moesif automatically sessionizes by processing at your data, but you can override this via identifySessionId if you're not happy with the results.

#### apiVersion:
Type: `String`
Optional, a string to specifiy an API Version such as 1.0.1, allowing easier filters.

#### maskRequestHeaders
Type: `$headers => $headers`
Optional, a function that takes a $headers, which is an associative array, and
returns an associative array with your sensitive headers removed/masked.

```php
// within config/moesif.php

$maskRequestHeaders = function($headers) {
    $headers['password'] = '****';
    return $headers;
};

return [
  //
  'maskRequestHeaders' => $maskRequestHeaders
];
```

#### maskRequestBody
Type: `$body => $body`
Optional, a function that takes a $body, which is an associative array representation of JSON, and
returns an associative array with any information removed.

```php

// within config/moesif.php

$maskRequestBody = function($body) {
    // remove any sensitive information.
    return $body;
};

return [
  //
  'maskRequestBody' => $maskRequestBody
];
```

#### maskResponseHeaders
Type: `$headers => $headers`
Optional, same as above, but for Responses.

#### maskResponseBody
Type: `$body => $body`
Optional, same as above, but for Responses.

#### debug
Type: `Boolean`
Optional, If true, will print debug messages using Illuminate\Support\Facades\Log

## Credits for Moesif Laravel SDK

- Parts of queuing & sending data via forked non-blocking process is based on Mixpanel's PHP client code which is open sourced under Apache License, Version 2.0.

## Other Integrations

To view more more documenation on integration options, please visit __[the Integration Options Documentation](https://www.moesif.com/docs/getting-started/integration-options/).__ 

