# Winged Framework
The Winged Framework is a framework built in PHP. Built and designed to streamline some everyday processes for a variety of projects with small or large proportions. In addition, it tries to facilitate the writing of methods found in other frameworks such as Yii, Laravel 5. The project is in progress a long time, but needs adjustments and tips.

## Let's begin

In the Winged Framework we can create routes and controllers, all through the URL. The order of the mechanism works as follows:

The first step is to try to find a computable route with URI provided in the request. If the route is not found a 404 error is written to memory, then the controller will try to be found. If it also is not found a response with 404 will be played in response.

### Begin with Controller

Assuming the URI call is http://example.com/home/ we will have the following controller.

```php
<?php

use Winged\Controller\Controller; 

/*
* Class HomeController
* Location: ./controllers/
*/

class HomeController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    //This function respond to index action when action not found in URI
    function actionIndex()
    {
        //Try to find file home.php inside views folder and include it
        $this->renderHtml('home');
    }
}
```

Any render function other than renderAnyfile tries to find the file in the views folder according to the settings. If the $PARENT_FOLDER_MVC variable in WingedConfig is true, the function will always try to look up the controllers folder of the current controller in the parallel views folder. If WingedConfig is false the function fetches the path within the views folder of the project root.

### Begin with Route

The routes must be registered within the routes.php file that will be inside the routes folders. Each project folder can contain a routes folder with its respective route file inside it. For this to occur in this way, the $PARENT_FOLDER_MVC property must be true, otherwise the path file to be included will always be the file found in the root folder of the project.

```php
<?php

/*
* Location: ./routes/ 
*/

use Winged\Route\Route;

//Route that responds to URI users/limit, in which case limit is optional and may contain any value.
Route::get('./users/{limit?}/', function(){
    /*
    At this point in the callback anything can be done, whether or not to return something here will cause the server to complete the request.
    Here we can virtualize another route or controller
    If the return of this callback is an array it will be converted according to the client's request, the default is json for rest and html for normal calls.
    */
});

//We can create a token to give an even higher level of security, this token comes with a validity of 3600 seconds by default but can be configured without problems. To create a token, just the second parameter passed to any Route :: function is an array.
Route::post('./create/token/', []);

//If the session method is called in sequence, the route will require an X-Auth-Token in the client headers, which can be acquired through a call to another URI that responds with a valid token.
Route::get('./users/admin/{limit?}/', function(){})->session();

//Below a route that is characterized as restful since it needs basic authorization via HTTP protocol
Route::get('./users/normal/{limit?}/', function(){})->credentials('matheusprador@gmail.com', 'basic');

//You can combine token usage with basic authentication
Route::get('./users/all/{limit?}/', function(){})->credentials('matheusprador@gmail.com', 'basic')->session();

//You can also validate the URI directly by the route object, if the URI fails validation the route in question will be marked with a 502 error.
Route::get('./users/all/{limit?}/', function(){})->credentials('matheusprador@gmail.com', 'basic')->session()->where([
    'limit' => '\d' //Only integer for limit
]);
```