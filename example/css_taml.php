<?php

if ( ! empty($_GET['grid']))
{
  $width = $_GET['grid'];

  $image = imagecreatetruecolor($width * 2,1);

  $one   = imagecolorallocate($image, 230, 230, 230);
  $two   = imagecolorallocate($image, 210, 210, 210);

  imagefill($image, 0, 0, $one);
  imagerectangle($image, 0, 0, $width, 1, $two);

  header('Content-Type: image/png');
  imagepng($image);
  exit;
}


require dirname(__DIR__).'/library/initialize.php';

run(function()
{

  uses('tetl/css');
  uses('tetl/taml');
  uses('tetl/router');

  css::setup('path', __DIR__);

  $css = css::render(__DIR__.DS.'assets'.DS.'sample.css');

  taml::render(__DIR__.DS.'assets'.DS.'sample.taml', compact('css'));

});
