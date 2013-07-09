<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

$app['debug'] = true;

$app->get('/', function ()  {
   $str = "Current";
   return $str;
});

$app->get('/add', function ()  {
   $str = "Add";

   return $str;
});

$app->get('/history', function ()  {
   $str = "history";

   return $str;
});

// definitions
$app->run();