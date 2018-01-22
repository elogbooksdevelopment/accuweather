<?php

require_once 'Accuweather.php';

$password = null;
switch (true) {
    case isset($_SERVER['HTTP_X_PASSWORD']):
        $password = $_SERVER['HTTP_X_PASSWORD'];
        break;

    case isset($_GET['password']):
        $password = $_GET['password'];
        unset($_GET['password']);
        break;
}

$requestUri = $_SERVER['REQUEST_URI'];
$paths = explode('?', $requestUri);

$accuweather = new Accuweather($paths[0], $_GET, $password);

