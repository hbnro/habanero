<?php

require dirname(__DIR__).'/library/initialize.php';

run(function()
{

  uses('tetl/css');
  uses('tetl/taml');
  uses('tetl/router');

  css::setup('path', __DIR__);

  $css = css::parse(__DIR__.DS.'assets'.DS.'sample.css');

  taml::render(__DIR__.DS.'assets'.DS.'sample.taml', compact('css'));

});
