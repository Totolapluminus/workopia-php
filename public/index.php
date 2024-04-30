<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';
require '../helpers.php';

use Framework\Router;

//Простой автозагрузчик
// spl_autoload_register(function ($class) {
//     $path = basePath('Framework/' . $class . '.php');
//     if (file_exists($path)){
//         require $path;
//     }
// });

//Объявляем объект класса роутера который находится в Router.php
$router = new Router();

//Заносим дороги в переменную (потом из этой переменной в массив роутера) (В файде routes.php вызываются функции класса Router.php которые заносят дороги в массив Router.php)
$routes = require basePath('routes.php');

//Get current URI (сохраняем URI(путь в адрессной строке) на котором в данный момент находится пользователь )
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

//Вызываем метод route объекта класса Router который мы объявляем выше (подробнее о методе в файле класса)
$router->route($uri);