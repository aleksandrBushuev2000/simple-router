# simple-router

## Simple PHP Routing Library

### example

```php
use SimpleRouter\Router;
use SimpleRouter\handlers\IRequestHandler;
use SimpleRouter\response\impl\JsonResponse;
use SimpleRouter\request\Request;

class ExampleHandler implements IRequestHandler {

   public function handle(Request $req) : JsonResponse {
       return new JsonResponse(["status" => "OK"]);
   }
}
 
$router = Router::getInstance();
$router->get("/articles/{category?}/{id : integer}/{format? = html}", new ExampleHandler());
 
$router->handle();
```
