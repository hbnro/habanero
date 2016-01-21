<?php

// function arg()
// {
//   return call_user_func_array('\\Sauce\\Shell\\CLI::arg', func_get_args());
// }

// function flags()
// {
//   return \Sauce\Shell\CLI::flags();
// }

// function readln($input = '')
// {
//   return \Sauce\Shell\CLI::readln($input);
// }

// function writeln($input = '')
// {
//   return \Sauce\Shell\CLI::writeln($input);
// }

// function colorize($input)
// {
//   return \Sauce\Shell\CLI::format($input);
// }

// function choice()
// {
//   return call_user_func_array('\\Sauce\\Shell\\CLI::choice', func_get_args());
// }

// function prompt()
// {
//   return call_user_func_array('\\Sauce\\Shell\\CLI::prompt', func_get_args());
// }

// function menu()
// {
//   return call_user_func_array('\\Sauce\\Shell\\CLI::menu', func_get_args());
// }

// function ask()
// {
//   call_user_func_array('prompt', func_get_args());
// }

// function say($text)
// {
//   static $theme = array(
//             '/##\s+([^#:]+:)/m' => '\ccyan(\\1)\c',
//             '/#\s+([^#:]+:)/m' => '\cgreen(\\1)\c',
//             '/--[a-z][\w:-]+|OPTIONS/m' => '\clight_gray(\\0)\c', // --options -o OPTIONS
//             '/\s#\s|[[\]]|=[A-Z]+\b|\.{2,3}/' => '\cdark_gray(\\0)\c',
//             '/\{([\\\\\/.$~\w:-]+)\}/m' => '\cbrown(\\1)\c', // {placeholder}
//             '/<[\w:?-]+>/m' => '\cbrown(\\0)\c', // <params>
//             '/\*([*\w\/:-]+)\*/m' => '\cwhite(\\1)\c', // *bold*
//             '/\+([+\w\/:-]+)\+/m' => '\cwhite,black(\\1)\c', // +strong+
//             '/_([\w\/:-]+)_/m' => '\clight_gray(\\1)\c', // _opaque_
//           );

//   $php  = 'hs'; # TODO: basename($_SERVER['_']);

//   $text = str_replace('{@}', sprintf('\clight_gray(%s)\c', $php), $text);
//   $text = preg_replace(array_keys($theme), $theme, $text);

//   writeln(colorize($text));
// }

// function yes($text)
// {
//   return choice($text, 'yn', 'n') === 'y';
// }

// function help($cmd = NULL)
// {
//   \Sauce\Shell\Task::help($cmd);
// }

// function task($name, $desc, \Closure $fn)
// {
//   \Sauce\Shell\Task::task($name, array('desc' => $desc, 'exec' => $fn));
// }

// function error($text)
// {
//   \Sauce\Shell\CLI::error("\cred,black($text)\c");
// }

// function info($text)
// {
//   writeln(colorize("\ccyan($text)\c"));
// }

// function hi($text)
// {
//   writeln(colorize("\bwhite($text)\b"));
// }

// function notice($text)
// {
//   writeln(colorize("\cbrown($text)\c"));
// }

// function success($text)
// {
//   writeln(colorize("\cgreen($text)\c"));
// }

// function copy_file($to, $from, $perms = FALSE)
// {
//   status('copy', path(rtrim($to, DIRECTORY_SEPARATOR), basename($from)));

//   is_dir($to) OR mkdir($to, $perms ?: 0755, TRUE);
//   copy($from, $file = path($to, basename($from)));
//   $perms && chmod($file, $perms);
// }

// function create_file($path, $text = '', $perms = FALSE)
// {
//   status('create', $path);

//   is_dir($dir = dirname($path)) OR mkdir($dir, $perms ?: 0755, TRUE);

//   write($path, $text);
//   $perms && chmod($path, $perms);
// }

// function remove_file($path)
// {
//   status('remove', $path);
//   is_file($path) && unlink($path);
// }

// function create_dir($path, $perms = FALSE, $gitkeep = FALSE)
// {
//   status('create', $path);
//   is_dir($path) OR mkdir($path, $perms ?: 0755, TRUE);
//   $gitkeep && touch(path($path, '.gitkeep'));
// }

// function copy_dir($to, $from)
// {
//   status('copy', path(rtrim($to, DIRECTORY_SEPARATOR), basename($from)));
//   \IO\Dir::cpfiles($from, path($to, basename($from)), '*', TRUE);
// }

// function template($from, array $vars = array())
// {
//   return call_user_func(function () {
//       ob_start();
//       extract(func_get_arg(1));
//       require func_get_arg(0);

//       return ob_get_clean();
//     }, $from, $vars);
// }

// function append_file($path, $content, $unless = FALSE)
// {
//   status('append', $path);

//   if ($unless) {
//     if (preg_match($unless, read($path))) {
//       return FALSE;
//     }
//   }

//   return write($path, $content, TRUE);
// }

// function prepend_file($path, $content, $unless = FALSE)
// {
//   status('prepend', $path);

//   if ($unless) {
//     if (preg_match($unless, read($path))) {
//       return FALSE;
//     }
//   }

//   return write($path, $content . read($path));
// }

// function gsub_file($path, $regex, \Closure $replace)
// {
//   if ( ! is_file($path)) {
//     return FALSE;
//   }

//   $content = read($path);

//   if (preg_match($regex, $content, $match, PREG_OFFSET_CAPTURE)) {
//     ob_start();
//     $content = $replace($match, $content);
//     $buffer = ob_get_clean();

//     return $content === NULL ? $buffer : $content;
//   }

//   return FALSE;
// }

// function inject_into_file($path, $content, array $params = array())
// {
//   if ( ! empty($params['unless'])) {
//     if (preg_match($params['unless'], read($path))) {
//       return FALSE;
//     }
//   }

//   $regex = ! empty($params['replace']) ? $params['replace'] : '/^.+?$/s';
//   $regex = ! empty($params['before']) ? $params['before'] : $regex;
//   $regex = ! empty($params['after']) ? $params['after'] : $regex;

//   $replace = $content instanceof \Closure ? $content() : (string) $content;
//   $position = ! empty($params['before']) ? -1 : ( ! empty($params['after']) ? 1 : 0);

//   return gsub_file($path, $regex, function ($match, $content)
//     use ($path, $params, $replace, $position) {
//       $offset = array_pop($match[0]);
//       $old = array_pop($match[0]);

//       $lft = substr($content, 0, $offset);
//       $rgt = substr($content, strlen($old) + $offset);

//       switch ($position) {
//         case -1; // before

//           return write($path, "$lft$replace$old$rgt");
//         case 1; // after

//           return write($path, "$lft$old$replace$rgt");
//         case 0; // replace
//         default;

//           return write($path, $replace);
//       }
//     });
// }

// function add_class($path, $name, $parent = '', array $methods = array(), array $properties = array(), array $constants = array())
// {
//   status('create', $path);

//   $ns     = '';
//   $type   = $parent ? " extends $parent" : '';
//   $props  =
//   $consts =
//   $method = '';

//   if (strpos($name, '\\') !== FALSE) {
//     $parts = explode('\\', $name);
//     $name = array_pop($parts);
//     $ns = join('\\', $parts);
//     $ns = "\n\nnamespace $ns;";
//   }

//   if ( ! empty($constants)) {
//     $test = array('');

//     foreach ($constants as $one => $val) {
//       $one = strtoupper($one);
//       $test []= "  const $one = '$val';";
//     }
//     $test []= '';
//     $consts = join("\n", $test);
//   }

//   if ( ! empty($methods)) {
//     $test = array('');

//     foreach ($methods as $one) {
//       $prefix = '';

//       if (strpos($one, ' ') !== FALSE) {
//         $set = array_filter(explode(' ', $one));
//         $one = array_pop($set);

//         $prefix = join(' ', $set) . ' ';
//       }

//       $test []= "  {$prefix}function $one()\n  {\n  }\n";
//     }
//     $test []= '';
//     $method = join("\n", $test);
//   }

//   if ( ! empty($properties)) {
//     $test = array();

//     foreach ($properties as $key => $val) {
//       $prefix = 'public';

//       if (strpos($key, ' ') !== FALSE) {
//         $parts  = explode(' ', $key);
//         $key    = array_pop($parts);
//         $prefix = join(' ', $parts);
//       }

//       $max = strlen("$prefix$$key") + 3;
//       $val = preg_replace('/^/m', str_repeat(' ', $max), var_export($val, TRUE));

//       $test []= "  $prefix $$key = $val;\n";
//     }

//     $props = "\n" . join("\n", $test) . "\n";
//     $props = preg_replace('/=\s+/m', '= ', $props);
//     $props = preg_replace('/\d+\s=>\s+/m', '', $props);
//   }

//   $base_dir = dirname($path);
//   is_dir($base_dir) OR mkdir($base_dir, 0755, TRUE);

//   return write($path, "<?php$ns\n\nclass $name$type\n{\n$consts$props$method}\n");
// }

// function add_route($from, $to, $path = '', $method = 'get')
// {
//   status('update', 'config/routes.php');

//   $path OR $path = "{$from}_$to";

//   $text = "\n$method('/$from', '$to', array('path' => '$path'));";
//   $from = preg_quote($from, '/');

//   $config_dir = path(APP_PATH, 'config');
//   is_dir($config_dir) OR mkdir($config_dir, 0755, TRUE);

//   return inject_into_file(path($config_dir, 'routes.php'), $text, array(
//     'unless' => "/$method\s*\(\s*'\/$from'/",
//     'after' => '/;[^;]*?$/',
//   ));
// }

// function add_controller($name, $empty = FALSE)
// {
//   $out_file = path(APP_PATH, 'app', 'controllers', "$name.php");

//   $base = classify(basename(APP_PATH));
//   $ucname = classify($name);
//   $base_class = "\\$base\\App\\Base";

//   add_class($out_file, $ucname, $base_class, $empty ? array() : array('index'));
// }

// function add_model($name, $table = '', array $columns = array(), array $indexes = array(), $parent = 'database', $connection = 'default')
// {
//   static $set = array(
//             'database' => '\\Servant\\Mapper\\Database',
//             'mongodb' => '\\Servant\\Mapper\\MongoDB',
//           );

//   $table = $table ?: underscore($name);
//   $ucname = classify($name);

//   $fields['static columns'] = $columns;
//   $fields['static indexes'] = $indexes;

//   $connect = compact('table', 'connection');
//   $out_file = path(APP_PATH, 'app', 'models', "$name.php");

//   isset($set[$parent]) && $parent = $set[$parent];

//   add_class($out_file, $ucname, $parent, array(), $fields, $connect);
// }

// function add_view($parent, $name, $text = '')
// {
//   status('create', "views/$parent/$name");

//   $views_dir = path(APP_PATH, 'app', 'views', $parent);
//   is_dir($views_dir) OR mkdir($views_dir, 0755, TRUE);

//   return write(path($views_dir, $name), $text);
// }

// function add_action($parent, $action)
// {
//   $out_file = path(APP_PATH, 'app', 'controllers', "$parent.php");

//   if (inject_into_file($out_file, "  function $action()\n  {\n  }\n\n", array(
//     'unless' => "/\bfunction\s+$action\s*\(/",
//     'before' => '/\}[^{}]*?$/',
//   ))) {
//     status('update', "controllers/$parent.php");

//     return TRUE;
//   }
// }

// function action($format, $text, $what)
// {
//   $prefix = str_pad("\c$format($text)\c", 20 + strlen($format), ' ', STR_PAD_LEFT);
//   $text   = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', "\clight_gray($what)\c");

//   writeln(colorize("$prefix  $text"));
// }

// function status($type, $text = '')
// {
//   switch ($type) {
//     case 'write';
//     case 'create';
//     case 'prepare';
//       action('green', $type, $text);
//     break;
//     case 'empty';
//     case 'remove';
//     case 'warning';
//       action('red', $type, $text);
//     break;
//     case 'move';
//     case 'rename';
//       action('cyan', $type, $text);
//     break;
//     case 'copy';
//     case 'update';
//     case 'hydrate';
//       action('brown', $type, $text);
//     break;
//     default;
//       $text = str_replace(APP_PATH.DIRECTORY_SEPARATOR, '', $text);
//       $prefix = str_pad("\bwhite($type)\b", 25, ' ', STR_PAD_LEFT);

//       writeln(colorize("$prefix  $text"));
//     break;
//   }
// }
