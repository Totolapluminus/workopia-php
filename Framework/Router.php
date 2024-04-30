<?php

namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorize;

class Router {
    protected $routes = [];
    
    /**
     * Добавляет путь(route) в массив routes этого класса
     * Сюда передается HTTP метод, который определяется функциями ниже (get, post, put, delete), URI из файла routes.php
     * Переменная $action заполняется из файла routes а потом разделяется на 2 переменных функциями list и explode  
     *
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param array $middleware
     * @return void
     */
    public function registerRoute($method, $uri, $action, $middleware= []){
        list($controller, $controllerMethod) = explode('@', $action);
            $this->routes[] = [
                'method' => $method,
                'uri' => $uri,
                'controller' => $controller,
                'controllerMethod' => $controllerMethod,
                'middleware' => $middleware
            ];
    }

    /**
     * Add a GET route to array
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function get($uri, $controller, $middleware = []){
     $this -> registerRoute('GET', $uri, $controller, $middleware);
    }
         /**
     * Add a POST route to array
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

     public function post($uri, $controller, $middleware = []){
        $this -> registerRoute('POST', $uri, $controller, $middleware);
     }

         /**
     * Add a PUT route to array
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

     public function put($uri, $controller, $middleware = []){
        $this -> registerRoute('PUT', $uri, $controller, $middleware);
     }

         /**
     * Add a DELETE route to array
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */

     public function delete($uri, $controller, $middleware = []){
        $this -> registerRoute('DELETE', $uri, $controller, $middleware);
     }

    
     /**
     *  Route the request 
     * 
     * @param string $uri
     * @return void
     */ 

    public function route($uri){
        // Запрашиваем метод HTTP с помощью которого он перешел по адрессу
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        // Проверяем есть ли _method у скрытого инпута в хтмле (затычка для DELETE и POST запросов)
        if($requestMethod === 'POST' && isset($_POST['_method'])){
            //Override the request method with the value of _method
            $requestMethod = strtoupper($_POST['_method']);
        }
        //Перебираем массив путей для совпадения с адрессной строкой
        foreach($this->routes as $route) {

            // Разделяем URI который находится в адрессной строке у пользователя (trim убирает пробелы, а в данном случае убирает еще и /, а explode при этом делит по /)
            $uriSegments = explode('/', trim($uri,'/'));

            // Разделяем URI который находится в массиве $routes в текущем пробеге (trim убирает пробелы, а в данном случае убирает еще и /, а explode при этом делит по /)
            $routeSegments = explode('/', trim($route['uri'],'/'));

            $match = true;

            // Первый if проверяет соответствие методов HTTP и сверяет длинну путей
            if(count($uriSegments) === count($routeSegments) && strtoupper($route['method']) === $requestMethod){
                $params = [];
                $match = true;
                for ($i = 0; $i < count($uriSegments); $i++){
                    // If the uri's don't match and there is no param in {} (Пока что параметр id) Переходим в следующую итерацию цикла
                    if($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])){
                        $match = false;
                        break;
                    }
                    //Если есть параметр в фигурных скобках, то добавляем его в массив params в виде элемента ассоциативного массива matches в виде ["id"] => 1
                    if(preg_match('/\{(.+?)\}/', $routeSegments[$i], $matches)){
                    $params[$matches[1]] = $uriSegments[$i];
                    }
                }
                //Если все подходит, то вытаскиваем в переменные класс нужного контроллера и функцию в классе этого контроллера 
                if($match){
                foreach($route['middleware'] as $middleware){
                    (new Authorize())->handle($middleware);
                }

                $controller = 'App\\Controllers\\' . $route['controller'];
                $controllerMethod = $route['controllerMethod'];

                //Instantiate the controller (class) and call the method (Инициализируем объект класса нужного контроллера и завершаем цикл)
                $controllerInit = new $controller();
                $controllerInit->$controllerMethod($params);
                return;
                }
            }

            // if($route['uri'] === $uri && $route['method'] === $method) {
            //     //Extract controller and controller method
            //     $controller = 'App\\Controllers\\' . $route['controller'];
            //     $controllerMethod = $route['controllerMethod'];

            //     //Instantiate the controller (class) and call the method
            //     $controllerInit = new $controller();
            //     $controllerInit->$controllerMethod();
            //     return;
            // }
        }
      ErrorController::notFound();
    }
}