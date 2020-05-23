# simple-router

###Simple PHP Routing Library

###example

```php
use SimpleRouter\Router;
use SimpleRouter\handlers\IRequestHandler;

class ExampleHandler implements IRequestHandler {
   public function handler(Request $req) {
        var_dump($req);
   }
}
 
$router = Router::getInstance();
$router->get("/articles/{category?}/{id : integer}/{format? = html}", new ExampleHandler);
 
$router->handle();
```

