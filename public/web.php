<?php
require dirname(__DIR__) . '/boot.php';
define('APP_WWW_PATH', ROOT_PATH .'/app/www');

$path = \Lib\Request::path();
$path = explode('/', $path);

$controller = 'Index';
$action = 'Index';
if (!empty($path[0])) {
    $controller = str_replace('-', '', ucwords($path[0], '-'));
}
if (!empty($path[1])) {
    $action = str_replace('-', '', ucwords($path[1], '-'));
}

$controller_class = $controller .'Controller';
$class_file = APP_WWW_PATH . '/controller/' . $controller_class . '.php';
if (!is_file($class_file)) {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
    echo '404 not found';
    exit;
}
include APP_WWW_PATH . '/controller/ControllerAbstract.php';
include $class_file;
$class = new $controller_class();
if (!method_exists($class, $action)) {
    header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
    echo '404 action not found';
    exit;
}

try {
    $class->$action();
} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo '500 Internal Server Error <!--'. $e->getMessage() .'-->';
    if (DEBUG) {
        echo '<pre>';
        print_r($e);
    }
}
