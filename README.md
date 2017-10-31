# Winged Framework
The Winged Framework is a framework built in PHP. Built and designed to streamline some everyday processes for a variety of projects with small or large proportions. In addition, it tries to facilitate the writing of methods found in other frameworks such as Yii, Laravel 5. The project is in progress a long time, but needs several adjustments, tips and repairs.

## Let's begin

In the Winged Framework we can create routes, controllers or execute a pure file, all through the URL. The order of the mechanism works as follows:

There is a file for the pure file mechanism, if it exists it will be called and this will end the request, otherwise the next step is to find a controller and if the controller also does not exist the next mechanism to be executed is the one of routes.

## Begin with Pure File

Assuming the calling URI is http://example.com/home/ pure file merge will try to find a file named home. ***php**, ***html**, ***htm**, ***xml**, or ***json**. If no file within these conditions is found, it will attempt to find the controller. If there are files with the same name but with different extensions, the priority will occur as described previously.

## Begin with Controller

Assuming the URI call is http://example.com/home/ we will have the following controller.

```php
class HomeController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    //this function respond to index action when action not found in URI
    function actionIndex()
    {
        //try to find file home.php inside views folder and include it
        //any render 
        $this->renderHtml('home');
    }
}
```

Any render function other than renderAnyfile tries to find the file in the views folder according to the settings. If the $ PARENT_FOLDER_MVC variable in WingedConfig is true, the function will always try to look up the controllers folder of the current controller in the parallel views folder. If WingedConfig is false the function fetches the path within the views folder of the project root.

