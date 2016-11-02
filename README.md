Moesif Laravel Middlware
=================

This provides a Middleware for PHP Laravel (> 5.1) to send data to Moesif.

How To Install:
=================

Via Composer

```bash
$ composer require moesif/moesif-laravel
```
or add 'moesif/moesif-laravel' to your composer.json file accordingly.

How To Setup:
=============

### Add Service Provider

```php

// within config/app.php

'providers' => [
    //
    Moesif\Middleware\MoesifLaravelServiceProvider::class,
];
```

### Add to Middleware

If your entire website app are API's, you can just add to the core api.

```php

// within App/Http/Kernel.php

protected $middleware = [
    //
    \Moesif\Middleware\MoesifLaravel::class,
];

```

If you have an API route group, feel free to add to a route group like this:

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

Also, if you have only certain routes that are APIs you want to track, feel free
to use route specific middleware setup also.


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

The applicationId is required, you can obtain the applicationId from the settings for your application on Moesif's website.

For other configuration options, see below.

## Configuration options

You can defined these configuration options in the `config/moesif.php` file. Some of these configuration options are functions.

#### applicationId:

Required, a string that identifies your application.

#### apiVersion:

Optional, a string. Tags the data with an API version for better data over time.

#### maskRequestHeaders

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

Optional, same as above, but for Responses.

#### maskResponseBody

Optional, same as above, but for Responses.

#### identifyUserId

Optional, a function that takes a $request and $response and return a string for userId. This is in case your Laravel implementation uses non standard way of injecting user into $request. We try to obtain userId via $request->user()['id']

```php

// within config/moesif.php

$identifyUserId = function($request, $response) {
    // $user = $request->user();
    // return $user['id'];

    return 'yourcomputeduserId';
};
```

```php
return [
  //
  'identifyUserId' => $identifyUserId
];
```

#### identifySessionId

Optional, a function that takes a $request and $response and return a string for sessionId.

#### debug

Optional, a boolean if true, will print debug messages using Illuminate\Support\Facades\Log


Credits
========

- Queuing & sending the data with a forked process (non blocking) is based on Mixpanel's PHP open source client code.

License
========

Apache License, Version 2.0. Please see [License File](license.md) for more detail.
