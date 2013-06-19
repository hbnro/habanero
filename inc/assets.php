<?php

// urls
function asset_path($for)
{
  return \Sauce\App\Assets::asset_path($for);
}

function asset_url($path)
{
  return \Sauce\App\Assets::asset_url($path);
}

// setup
function after_body()
{
  return \Sauce\App\Assets::after();
}

function before_body()
{
  return \Sauce\App\Assets::before();
}

function javascript_for($name)
{
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'scripts_dir')), 'body', TRUE);
}

function prepend_js($test, $to = 'head')
{
  \Sauce\App\Assets::prepend($test, $to);
}

function append_js($test, $to = 'head')
{
  \Sauce\App\Assets::append($test, $to);
}

function stylesheet_for($name)
{
  \Sauce\App\Assets::inline(join("\n", \Sauce\App\Assets::build($name, 'styles_dir')), 'head', TRUE);
}

function prepend_css($test, $to = 'head')
{
  \Sauce\App\Assets::prepend($test, $to);
}

function append_css($test, $to = 'head')
{
  \Sauce\App\Assets::append($test, $to);
}

// blocks
function csrf_meta_tag()
{
  return \Labourer\Web\Html::meta('csrf-token', \Labourer\Web\Session::token());
}

function image_tag($src, $alt = NULL, array $attrs = array())
{
  if (is_array($alt)) {
    $attrs = $alt;
    $alt   = $src;
  }

  if ( ! $alt OR ($alt === $src)) {
    $ext = \IO\File::ext($src, TRUE);
    $alt = titlecase(basename($src, $ext));
  }

  $attrs['alt'] = $attrs['title'] = $alt;

  try {
    $img = \Tailor\Helpers::image($src);

    $attrs['width'] = $img['dims'][0];
    $attrs['height'] = $img['dims'][1];

    $attrs['src'] = asset_url($src);
  } catch (\Exception $e) {
    $attrs['src'] = $src;
  }

  return \Labourer\Web\Html::tag('img', $attrs);
}

function tag_for($src)
{
  return \Sauce\App\Assets::tag_for($src);
}
