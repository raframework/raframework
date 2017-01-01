# raframework
A RESTful API framework for the PHP language. 


## Usage

1.  Create a raframework project name `raproj`

    ```bash
    $ mkdir raproj && cd raproj
    ```

2.  Create a `composer.json` file with the following contents:
    ```json
    {
      "require": {
        "raframework/raframework": "0.0.6"
      },
      "autoload": {
        "psr-4": {
          "App\\": "App/"
        }
      }
    }
    ```
3.  Install the raframework by [Composer](https://getcomposer.org/)
    ```bash
    $ composer install
    ```

4.  Make the resource classes directory
    ```bash
    $ mkdir -p App/Resource
    ```

5.  Create an `App/Resource/Users.php` file with the following contents:
    ```php
    <?php
    
    namespace App\Resource;
    
    
    use Ra\Http\Request;
    use Ra\Http\Response;
    
    // Define resource class.
    class Users
    {
        // Define the resource action.
        public function lis(Request $request, Response $response)
        {
            $data = 'List users...';
            $response->withStatus(200)->write($data);
        }
    }
    ```

6.  Create an index.php file with the following contents:

    ```php
    <?php
    
    require 'vendor/autoload.php';
    
    // Define the routes.
    $uriPatterns = [
        '/users' => ['GET'], // uri pattern => supported methods
    ];
    
    // Create a raframework app with the routes given.
    $app = new Ra\App($uriPatterns);
    
    // Match the route, and set the resource's action correctly.
    $app->matchUriPattern();
    
    // Call the resouce's action.
    // You should call MatchUriPattern() before this.
    $app->callResourceAction();
    
    // Send the response to the client.
    $app->respond();
    ```

7.  You may quickly test this using the built-in PHP server:
    ```bash
    $ php -S localhost:8000
    ```
    
    Going to http://localhost:8000/users will now display "List users...".
