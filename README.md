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

If you only want to add tracking for APIs under specific route group, add to your route group, but be sure to remove from the global
middleware stack from above global list.

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

__To support Laravel [configuration caching](https://laravel.com/docs/8.x/configuration#configuration-caching), some configuration options have moved into a separate config class for v2.
See [Migration Guide v1.x.x to v2.x.x](#migration-guide-v1xx-to-v2xx)__

You can define Moesif configuration options in the `config/moesif.php` file.

#### __`applicationId`__
Type: `String`
Required, a string that identifies your application.

#### __`debug`__
Type: `Boolean`
Optional, If true, will print debug messages using Illuminate\Support\Facades\Log

#### __`logBody`__
Type: `Boolean`
Optional, Default true, Set to false to remove logging request and response body to Moesif.

#### __`apiVersion`__
Type: `String`
Optional, a string to specify an API Version such as 1.0.1, allowing easier filters.

### __`configClass`__
Type: `String`
Optional, a string for the full path (including namespaces) to a class containing additional functions.
The class can reside in any namespace, as long as the full namespace is provided.

example:

```php
return [
    ...
    'configClass' => 'MyApp\\MyConfigs\\CustomMoesifConfig',
    ...
];
```

## Configuration class

Because configuration hooks and functions cannot be placed in the `config/moesif.php` file, these reside in a PHP class that you create.
Set the path to this class using the `configClass` option. You can define any of the following hooks:

#### __`identifyUserId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for userId. Moesif automatically obtains end userId via $request->user()['id'], In case you use a non standard way of injecting user into $request or want to override userId, you can do so with identifyUserId.

#### __`identifyCompanyId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for companyId.

#### __`identifySessionId`__
Type: `($request, $response) => String`
Optional, a function that takes a $request and $response and return a string for sessionId. Moesif automatically sessionizes by processing at your data, but you can override this via identifySessionId if you're not happy with the results.

#### __`getMetadata`__
Type: `($request, $response) => Associative Array`
Optional, a function that takes a $request and $response and returns $metdata which is an associative array representation of JSON.

#### __`maskRequestHeaders`__
Type: `$headers => $headers`
Optional, a function that takes a $headers, which is an associative array, and
returns an associative array with your sensitive headers removed/masked.

#### __`maskRequestBody`__
Type: `$body => $body`
Optional, a function that takes a $body, which is an associative array representation of JSON, and
returns an associative array with any information removed.

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

Example config class

```php
namespace MyApp\MyConfigs;

class CustomMoesifConfig
{
    public function maskRequestHeaders($headers) {
      $headers['header5'] = '';
      return $headers;
    }

    public function maskRequestBody($body) {
      return $body;
    }

    public function maskResponseHeaders($headers) {
      $headers['header2'] = 'XXXXXX';
      return $headers;
    }

    public function maskResponseBody($body) {
      return $body;
    }

    public function identifyUserId($request, $response) {
      if (is_null($request->user())) {
        return null;
      } else {
        $user = $request->user();
        return $user['id'];
      }
    }

    public function identifyCompanyId($request, $response) {
      return "67890";
    }

    public function identifySessionId($request, $response) {
      if ($request->hasSession()) {
        return $request->session()->getId();
      } else {
        return null;
      }
    }

    public function getMetadata($request, $response) {
      return array("foo"=>"a", "boo"=>"b");
    }

    public function skip($request, $response) {
      $myurl = $request->fullUrl();
      if (strpos($myurl, '/health') !== false) {
        return true;
      }
      return false;
    }
}
```

## Update a Single User

Create or update a user profile in Moesif.
The metadata field can be any customer demographic or other info you want to store.
Only the `user_id` field is required.

```php
use Moesif\Middleware\MoesifLaravel;

// Only userId is required.
// Campaign object is optional, but useful if you want to track ROI of acquisition channels
// See https://www.moesif.com/docs/api#users for campaign schema
// metadata can be any custom object
$user = array(
    "user_id" => "12345",
    "company_id" => "67890", // If set, associate user with a company object
    "campaign" => array(
        "utm_source" => "google",
        "utm_medium" => "cpc",
        "utm_campaign" => "adwords",
        "utm_term" => "api+tooling",
        "utm_content" => "landing"
    ),
    "metadata" => array(
        "email" => "john@acmeinc.com",
        "first_name" => "John",
        "last_name" => "Doe",
        "title" => "Software Engineer",
        "sales_info" => array(
            "stage" => "Customer",
            "lifetime_value" => 24000,
            "account_owner" => "mary@contoso.com"
        )
    )
);

$middleware = new MoesifLaravel();
$middleware->updateUser($user);
```

The `metadata` field can be any custom data you want to set on the user. The `user_id` field is required.

## Update Users in Batch

Similar to updateUser, but used to update a list of users in one batch.
Only the `user_id` field is required.

```php
use Moesif\Middleware\MoesifLaravel;

$userA = array(
    "user_id" => "12345",
    "company_id" => "67890", // If set, associate user with a company object
    "campaign" => array(
        "utm_source" => "google",
        "utm_medium" => "cpc",
        "utm_campaign" => "adwords",
        "utm_term" => "api+tooling",
        "utm_content" => "landing"
    ),
    "metadata" => array(
        "email" => "john@acmeinc.com",
        "first_name" => "John",
        "last_name" => "Doe",
        "title" => "Software Engineer",
        "sales_info" => array(
            "stage" => "Customer",
            "lifetime_value" => 24000,
            "account_owner" => "mary@contoso.com"
        )
    )
);

$userB = array(
    "user_id" => "12345",
    "company_id" => "67890", // If set, associate user with a company object
    "campaign" => array(
        "utm_source" => "google",
        "utm_medium" => "cpc",
        "utm_campaign" => "adwords",
        "utm_term" => "api+tooling",
        "utm_content" => "landing"
    ),
    "metadata" => array(
        "email" => "john@acmeinc.com",
        "first_name" => "John",
        "last_name" => "Doe",
        "title" => "Software Engineer",
        "sales_info" => array(
            "stage" => "Customer",
            "lifetime_value" => 24000,
            "account_owner" => "mary@contoso.com"
        )
    )
);

$users = array($userA);

$middleware = new MoesifLaravel();
$middleware->updateUsersBatch($users);
```

The `metadata` field can be any custom data you want to set on the user. The `user_id` field is required.

## Update a Single Company

Create or update a company profile in Moesif.
The metadata field can be any company demographic or other info you want to store.
Only the `company_id` field is required.

```php
use Moesif\Middleware\MoesifLaravel;

// Only companyId is required.
// Campaign object is optional, but useful if you want to track ROI of acquisition channels
// See https://www.moesif.com/docs/api#update-a-company for campaign schema
// metadata can be any custom object
$company = array(
    "company_id" => "67890",
    "company_domain" => "acmeinc.com", // If domain is set, Moesif will enrich your profiles with publicly available info
    "campaign" => array(
        "utm_source" => "google",
        "utm_medium" => "cpc",
        "utm_campaign" => "adwords",
        "utm_term" => "api+tooling",
        "utm_content" => "landing"
    ),
    "metadata" => array(
        "org_name" => "Acme, Inc",
        "plan_name" => "Free",
        "deal_stage" => "Lead",
        "mrr" => 24000,
        "demographics" => array(
            "alexa_ranking" => 500000,
            "employee_count" => 47
        )
    )
);

$middleware = new MoesifLaravel();
$middleware->updateCompany($company);
```

The `metadata` field can be any custom data you want to set on the company. The `company_id` field is required.

## Update Companies in Batch

Similar to update_company, but used to update a list of companies in one batch.
Only the `company_id` field is required.

```php
use Moesif\Middleware\MoesifLaravel;

$companyA = array(
    "company_id" => "67890",
    "company_domain" => "acmeinc.com", // If domain is set, Moesif will enrich your profiles with publicly available info
    "campaign" => array(
        "utm_source" => "google",
        "utm_medium" => "cpc",
        "utm_campaign" => "adwords",
        "utm_term" => "api+tooling",
        "utm_content" => "landing"
    ),
    "metadata" => array(
        "org_name" => "Acme, Inc",
        "plan_name" => "Free",
        "deal_stage" => "Lead",
        "mrr" => 24000,
        "demographics" => array(
            "alexa_ranking" => 500000,
            "employee_count" => 47
        )
    )
);

$companies = array($companyA);

$middleware = new MoesifLaravel();
$middleware->updateCompaniesBatch($companies);
```
The `metadata` field can be any custom data you want to set on the company. The `company_id` field is required.

## Credits for Moesif Laravel SDK

- Parts of queuing & sending data via forked non-blocking process is based on Mixpanel's PHP client code which is open sourced under Apache License, Version 2.0.

## Additional Tips:

- The forked (i.e. non-blocking way) of sending data is using exec() with a cURL command. The Php exec() command can be successful but the cURL itself may have 401 errors.  So after integration, if you don't see events and data show up in your Moesif Dash. Please turn on debug option, then the cURL command itself will logged. You can execute that cURL command and see what the issues are. The most common thing to check is if the Application ID is set correctly.

In case you've exec() as a disabled function, you could set configuration option `disableForking` to `true` to send data to Moesif using curl PHP extension.

## Troubleshooting

### The PHP Extension is Required
Make sure you install PHP with the JSON Extension enabled [More Info](https://stackoverflow.com/questions/7318191/enable-json-encode-in-php).

## Test Laravel App with Moesif Integrated

[Moesif Laravel Tests](https://github.com/Moesif/moesif-laravel-tests)

## An Example Laravel App with Moesif Integrated

[Moesif Laravel Example](https://github.com/Moesif/moesif-laravel-example)

### Laravel config cache

If you have trouble with `php artisan config:cache` in v1.x.x. please upgrade to new class based configuration scheme in v2.x.x.

Also, if you enabled config cache, after you update the configuration, please be sure to run `php artisan config:cache` again to ensure configuration is updated.

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

## Migration Guide v1.x.x to v2.x.x

v2.x.x now supports Laravel config caching. However, `config:cache` does not allow function closures in config files ([See issue on github](https://github.com/laravel/framework/issues/24103))
so the SDK configuration has changed in v2.x.x. To migrate, you will need to move any functions from your `config/moesif.php` into a separate class such as CustomMoesifConfig.
Then, reference this class using `configClass`.


For example, if you had these previously:

```php
$identifyUserId = function($request, $response) {
    // Your custom code that returns a user id string
    $user = $request->user();
    if ($request->user()) {
        return $user->id;
    }
    return NULL;
};

return [
  ...,
  'identifyUserId' => $identifyUserId,
]

```

In V2.X.X, you would do this:

- Create a new class like this:

```php
namespace MyApp\MyConfigs;

class CustomMoesifConfig
{
    public function identifyUserId($request, $response) {
      if (is_null($request->user())) {
        return null;
      } else {
        $user = $request->user();
        return $user['id'];
      }
    }

    // add other methods for closure based configs.
}
```

- In your `moesif.php` in the config folder:

```php

return [
  ...,
  'configClass' => 'MyApp\\MyConfigs\\CustomMoesifConfig',
]

```