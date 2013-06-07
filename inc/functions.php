<?php

// hypertext
function tag($name)
{
  return call_user_func_array("\\Labourer\\Web\\Html::$name", array_slice(func_get_args(), 1));
}

function e($text)
{
  return \Labourer\Web\Html::ents($text, TRUE);
}

function plain($text)
{
  return \Labourer\Web\Text::plain(\Labourer\Web\Html::unents($text));
}

// inflections
function plural($test)
{
  return \Doctrine\Common\Inflector\Inflector::pluralize($test);
}

function singular($test)
{
  return \Doctrine\Common\Inflector\Inflector::singularize($test);
}

// formatting
function camelcase()
{
  return call_user_func_array('\\Staple\\Helpers::camelcase', func_get_args());
}

function underscore()
{
  return call_user_func_array('\\Staple\\Helpers::underscore', func_get_args());
}

function parameterize()
{
  return call_user_func_array('\\Staple\\Helpers::parameterize', func_get_args());
}

function titlecase()
{
  return call_user_func_array('\\Staple\\Helpers::titlecase', func_get_args());
}

function classify()
{
  return call_user_func_array('\\Staple\\Helpers::classify', func_get_args());
}

function slugify($text)
{
  return join('/', array_map('parameterize', explode('/', plain($text))));
}

// datetime
function mdate()
{
  return call_user_func_array('\\Locale\\Datetime::format', func_get_args());
}

function sdate()
{
  return call_user_func_array('\\Locale\\Datetime::simple', func_get_args());
}

function distance()
{
  return call_user_func_array('\\Locale\\Datetime::distance', func_get_args());
}

function duration()
{
  return call_user_func_array('\\Locale\\Datetime::duration', func_get_args());
}

// registry
function fetch()
{
  return call_user_func_array('\\Staple\\Registry::fetch', func_get_args());
}

function exists()
{
  return call_user_func_array('\\Staple\\Registry::exists', func_get_args());
}

function remove()
{
  return call_user_func_array('\\Staple\\Registry::delete', func_get_args());
}

function assign()
{
  return call_user_func_array('\\Staple\\Registry::assign', func_get_args());
}
