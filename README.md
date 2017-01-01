# raframework
A RESTful API framework for the PHP language. 


## Usage

Create a raframework project name `raproj`

```bash
$ mkdir raproj && cd raproj
```

Install the raframework by [Composer](https://getcomposer.org/)
```bash
$ composer require raframework/raframework
```

Make the resource classes directory
```bash
$ mkdir -p App/Resource
```

Create an `App/Resource/Users.php` file with the following contents:
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

Create an index.php file with the following contents:

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

Going to http://localhost:8000/users will now display "List users...".