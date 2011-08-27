<?php

require dirname(dirname(dirname(__DIR__))).'/library/initialize.php';

config(dirname(__DIR__).'/config/application.php');

run(function()
{

  route('/', 'module#index');

  route('/page', 'module#action');

}, array(
  'middleware' => array(
    require dirname(dirname(dirname(__DIR__))).'/example/mvc_router.php',
  ),
));
