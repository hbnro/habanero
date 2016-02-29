<?php

// // core
// function run(\Closure $lambda = NULL)
// {
//   echo \Sauce\Base::initialize($lambda ?: function() {});
// }

// function option($get, $or = FALSE)
// {
//   return value(\Sauce\Config::all(), $get, $or);
// }

// function config($set = NULL, $value = NULL)
// {
//   if (func_num_args() === 0) {
//     return \Sauce\Config::all();
//   } elseif (func_num_args() === 2) {
//     \Sauce\Config::set($set, $value);
//   } else {
//     $tmp = array();

//     if ($set instanceof \Closure) {
//       $tmp = (object) $tmp;
//       $set($tmp);
//     } elseif (is_file($set)) {
//       $tmp = call_user_func(function () {
//           include func_get_arg(0);

//           return get_defined_vars();
//         }, $set);

//       $tmp = isset($tmp['config']) ? $tmp['config'] : $tmp;
//     }

//     \Sauce\Config::add((array) $tmp);
//   }
// }

// function value($from, $that, $or = FALSE)
// {
//   return \Staple\Helpers::fetch($from, $that, $or);
// }

// function params($key = NULL, $default = FALSE)
// {
//   static $set = array();

//   if ( ! func_num_args()) {
//     return $set;
//   } elseif (is_array($key)) {
//     $set = array_merge($set, $key);
//   } else {
//     return value($set, $key, $default);
//   }
// }

// // formatting
// function ln($input, array $params = array())
// {
//   return \Locale\Base::digest($input, $params);
// }

// function inspect($what)
// {
//   return \Symfony\Component\Yaml\Yaml::dump($what, 2, 2);
// }

// function partial($path, array $vars = array())
// {
//   return \Tailor\Base::render(\Tailor\Base::partial($path), $vars);
// }

// function render($file, array $vars = array())
// {
//   return \Tailor\Base::render($file, $vars);
// }

// function md($text)
// {
//   static $obj = NULL;

//   ($obj === NULL) && $obj = new \dflydev\markdown\MarkdownExtraParser;

//   return $obj->transformMarkdown($text);
// }

// function yaml($test)
// {
//   return \Symfony\Component\Yaml\Yaml::parse($test);
// }

// // filesystem
// function read($file)
// {
//   return \IO\File::read($file);
// }

// function write($file, $content, $append = FALSE)
// {
//   return \IO\File::write($file, $content, $append);
// }

// function findfile()
// {
//   return call_user_func_array('\\IO\\Dir::findfile', func_get_args());
// }

// function fmtsize()
// {
//   return call_user_func_array('\\IO\\Helpers::fmtsize', func_get_args());
// }

// function path()
// {
//   return call_user_func_array('\\IO\\Helpers::join', func_get_args());
// }

// function extn()
// {
//   return call_user_func_array('\\IO\\File::extn', func_get_args());
// }

// function ext($bytes)
// {
//   return call_user_func_array('\\IO\\File::ext', func_get_args());
// }
