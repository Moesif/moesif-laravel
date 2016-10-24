MoesifApi
=================

How To Configure:
=================
The generated code might need to be configured with your API credentials. To do that,
open the file "Configuration.php" and edit it's contents.

How To Build:
=============
The generated code has dependencies over external libraries. These dependencies
are defined in the `composer.json` file. To resolve these dependencies, we use
the *Composer* package manager. You will need internet access for this.

1. If you have not already installed Composer, [install the latest version](https://getcomposer.org/download/).
2. Once Composer is installed, from commandline, run `composer install`
    to install dependencies.

How To Use:
===========
For using this SDK do the following:

1. Use Composer to install the dependencies. See the section "How To Build".
2. See that you have configured your SDK correctly. See the section "How To Configure".
3. Depending on your project setup, you might need to include composer's autoloader
   in your PHP code to enable autoloading of classes.

   ```PHP
   require_once "vendor/autoload.php";
   ```
4. Import the SDK client in your project:

    ```PHP
    use MoesifApi\MoesifApiClient;
    ```
5. Instantiate the client. After this, you can now get the controllers and call the
    respective methods:

    ```PHP
    $client = new MoesifApiClient();
    $controller = $client->getApi();
    ```

How To Test:
============
Unit tests in this SDK can be run using PHPUnit.

1. First install the dependencies using composer including the `require-dev` dependencies.
2. Run `vendor\bin\phpunit --verbose` from commandline to execute tests. If you have
   installed PHPUnit globally, run tests using `phpunit --verbose` instead.

You can change the PHPUnit test configuration in the `phpunit.xml` file.
