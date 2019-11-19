# Moesif Laravel Middlware

[![Built For][ico-built-for]][link-built-for]
[![Latest Version][ico-version]][link-package]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]][link-license]
[![Source Code][ico-source]][link-source]

[Source Code on GitHub](https://github.com/moesif/moesif-laravel)

Middleware for PHP Laravel (> 5.1) to automatically log API Calls and 
sends to [Moesif](https://www.moesif.com) for API analytics and log analysis

### Laravel 4.2
  A [Moesif SDK](https://github.com/Moesif/moesif-laravel4.2) is available for Laravel 4.2. Credit for creating this goes to [jonnypickett](https://github.com/jonnypickett/).
  
## How to install

Via Composer

```bash
$ composer require moesif/moesif-laravel
```
or add 'moesif/moesif-laravel' to your composer.json file accordingly.

## How to use

### Add Service Provider

```php

// In config/app.php

'providers' => [
  /*
   * Application Service Providers...
   */
    Moesif\Middleware\MoesifLaravelServiceProvider::class,
];
```

### Add to Middleware

If website root is your API, add to the root level:

```php

// In App/Http/Kernel.php

protected $middleware = [
  /*
   * The application's global HTTP middleware stack.
   *
   * These middleware are run during every request to your application.
   */
   \Moesif\Middleware\MoesifLaravel::class,
];

```

If API under specific route group, add to your route group:

```php
// In App/Http/Kernel.php

protected $middlewareGroups = [
  /**
   * The application's API route middleware group.
   */
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

// In config/moesif.php

return [
    //
    'applicationId' => 'Your Moesif Application Id',
    'logBody' => true,
];
```

Your Moesif Application Id can be found in the [_Moesif Portal_](https://www.moesif.com/).
After signing up for a Moesif account, your Moesif Application Id will be displayed during the onboarding steps. 

You can always find your Moesif Application Id at any time by logging 
into the [_Moesif Portal_](https://www.moesif.com/), click on the top right menu,
and then clicking _Installation_.

For other configuration options, see below.

## Configuration options

You can define Moesif configuration options in the `config/moesif.php` file. Some of these fields are functions.

#### __`applicationId`__
Type: `String`
Required, a string that identifies your application.

#### __`identifyUserId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for userId. Moesif automatically obtains end userId via $request->user()['id'], In case you use a non standard way of injecting user into $request or want to override userId, you can do so with identifyUserId.

```php

// In config/moesif.php

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

#### __`identifyCompanyId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for companyId.

```php

// In config/moesif.php

$identifyCompanyId = function($request, $response) {
    return '12345';
};
```

```php
return [
  //
  'identifyCompanyId' => $identifyCompanyId
];
```

#### __`identifySessionId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for sessionId. Moesif automatically sessionizes by processing at your data, but you can override this via identifySessionId if you're not happy with the results.

#### __`getMetadata`__
Type: `($request, $response) => Associative Array`
Optional, a function that takes a $request and $response and returns $metdata which is an associative array representation of JSON.

```php

// In config/moesif.php

$getMetadata = function($request, $response) {
  return array("foo"=>"laravel example", "boo"=>"custom data");
};

return [
  //
  'getMetadata' => $getMetadata
];

```

#### __`apiVersion`__
Type: `String`
Optional, a string to specifiy an API Version such as 1.0.1, allowing easier filters.

#### __`maskRequestHeaders`__
Type: `$headers => $headers`
Optional, a function that takes a $headers, which is an associative array, and
returns an associative array with your sensitive headers removed/masked.

```php
// In config/moesif.php

$maskRequestHeaders = function($headers) {
    $headers['password'] = '****';
    return $headers;
};

return [
  //
  'maskRequestHeaders' => $maskRequestHeaders
];
```

#### __`maskRequestBody`__
Type: `$body => $body`
Optional, a function that takes a $body, which is an associative array representation of JSON, and
returns an associative array with any information removed.

```php

// In config/moesif.php

$maskRequestBody = function($body) {
    // remove any sensitive information.
    return $body;
};

return [
  //
  'maskRequestBody' => $maskRequestBody
];
```

#### __`maskResponseHeaders`__
Type: `$headers => $headers`
Optional, same as above, but for Responses.

#### __`maskResponseBody`__
Type: `$body => $body`
Optional, same as above, but for Responses.

#### __`skip`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and returns true if
this API call should be not be sent to Moesif.

#### __`debug`__
Type: `Boolean`
Optional, If true, will print debug messages using Illuminate\Support\Facades\Log

#### __`logBody`__
Type: `Boolean`
Optional, Default true, Set to false to remove logging request and response body to Moesif.

## updateUser

If you are updating the [user profile](https://www.moesif.com/docs/getting-started/users/) to get visibility. You can use the `updateUser` method. This method is attached to the moesif middleware object to update the user profile or metadata.

```php
use Moesif\Middleware\MoesifLaravel;

$user = array(
        "user_id" => "12345",
        "company_id" => "67890",
        "metadata" => array(
            "email" => "johndoe@acmeinc.com",
            "string_field" => "value_1",
            "number_field" => 0,
            "object_field" => array(
                "field_a" => "value_a",
                "field_b" => "value_b"
            )
        ),
        "campaign" => array(
            "utm_source" => "Newsletter",
            "utm_medium" => "Email"
        ),
    );
$middleware = new MoesifLaravel();
$middleware->updateUser($user);
// the user_id field is required.

```

The `metadata` field can be any custom data you want to set on the user. The `user_id` field is required.

## updateUsersBatch

If you are updating the [user profile](https://www.moesif.com/docs/getting-started/users/) to get visibility. You can use the `updateUsersBatch` method. This method is attached to the moesif middleware object to update the users profile or metadata in batch.

```php
use Moesif\Middleware\MoesifLaravel;

$metadata = array(
            "email" => "johndoe@acmeinc.com",
            "string_field" => "value_1",
            "number_field" => 0,
            "object_field" => array(
                "field_a" => "value_a",
                "field_b" => "value_b"
              )
            );

$userA = array(
    "user_id" => "12345",
    "company_id" => "67890",
    "metadata" => $metadata,
);

$userB = array(
    "user_id" => "1234",
    "company_id" => "6789",
    "metadata" => $metadata,
);

$users = array();
$users[] = $userA;
$users[] = $userB;

$middleware = new MoesifLaravel();
$middleware->updateUsersBatch($users);
// the user_id field is required.

```

The `metadata` field can be any custom data you want to set on the user. The `user_id` field is required.

## updateCompany

If you are updating the [company profile](https://www.moesif.com/docs/getting-started/companies/) to get visibility. You can use the `updateCompany` method. This method is attached to the moesif middleware object to update the company profile or metadata.

```php
use Moesif\Middleware\MoesifLaravel;

$company = array(
        "company_id" => "12345",
        "company_domain" => "acmeinc.com",
        "metadata" => array(
            "email" => "johndoe@acmeinc.com",
            "string_field" => "value_1",
            "number_field" => 0,
            "object_field" => array(
                "field_a" => "value_a",
                "field_b" => "value_b"
            )
        ),
        "campaign" => array(
            "utm_source" => "Adwords",
            "utm_medium" => "Twitter"
        ),
    );
$middleware = new MoesifLaravel();
$middleware->updateCompany($company);
// the company_id field is required.

```

The `metadata` field can be any custom data you want to set on the company. The `company_id` field is required.

## updateCompaniesBatch

If you are updating the [company profile](https://www.moesif.com/docs/getting-started/companies/) to get visibility. You can use the `updateCompaniesBatch` method. This method is attached to the moesif middleware object to update the companies profile or metadata in batch.

```php
use Moesif\Middleware\MoesifLaravel;

$metadata = array(
            "email" => "johndoe@acmeinc.com",
            "string_field" => "value_1",
            "number_field" => 0,
            "object_field" => array(
                "field_a" => "value_a",
                "field_b" => "value_b"
              )
            );

$companyA = array(
    "company_id" => "12345",
    "company_domain" => "nowhere.com",
    "metadata" => $metadata,
);

$companyB = array(
    "company_id" => "67890",
    "company_domain" => "acmeinc.com",
    "metadata" => $metadata,
);

$companies = array();
$companies[] = $companyA;
$companies[] = $companyB;

$middleware = new MoesifLaravel();
$middleware->updateCompaniesBatch($companies);
// the company_id field is required.

```
The `metadata` field can be any custom data you want to set on the company. The `company_id` field is required.

## Credits for Moesif Laravel SDK

- Parts of queuing & sending data via forked non-blocking process is based on Mixpanel's PHP client code which is open sourced under Apache License, Version 2.0.

## Additional Tips:

- The forked (i.e. non-blocking way) of sending data is using exec() with a cURL command. The Php exec() command can be successful but the cURL itself may have 401 errors.  So after integration, if you don't see events and data show up in your Moesif Dash. Please turn on debug option, then the cURL command itself will logged. You can execute that cURL command and see what the issues are. The most common thing to check is if the Application ID is set correctly.

## Test Laravel App with Moesif Integrated

[Moesif Laravel Tests](https://github.com/Moesif/moesif-laravel-tests)

## An Example Laravel App with Moesif Integrated

[Moesif Laravel Example](https://github.com/Moesif/moesif-laravel-example)

## Other integrations

To view more documentation on integration options, please visit __[the Integration Options Documentation](https://www.moesif.com/docs/getting-started/integration-options/).__

[ico-built-for]: https://img.shields.io/badge/built%20for-laravel-blue.svg
[ico-version]: https://img.shields.io/packagist/v/moesif/moesif-laravel.svg
[ico-downloads]: https://img.shields.io/packagist/dt/moesif/moesif-laravel.svg
[ico-license]: https://img.shields.io/badge/License-Apache%202.0-green.svg
[ico-source]: https://img.shields.io/github/last-commit/moesif/moesif-laravel.svg?style=social

[link-built-for]: http://laravel.com
[link-package]: https://packagist.org/packages/moesif/moesif-laravel
[link-downloads]: https://packagist.org/packages/moesif/moesif-laravel
[link-license]: https://raw.githubusercontent.com/Moesif/moesif-laravel/master/LICENSE
[link-source]: https://github.com/moesif/moesif-laravel
