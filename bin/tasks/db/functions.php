<?php

function __set($val = NULL, array $vars = array())
{
  static $set = array();

  if (func_num_args() === 0) {
    return $set;
  } elseif (is_array($vars)) {
    $set = array_merge($set, $vars);
  }

  return $val;
}

function db($for = 'default')
{
  $dsn = option("database.$for");
  $db = \Grocery\Base::connect($dsn);

  return $db;
}

function mongo($for = 'mongodb')
{
  $dsn = option("database.$for");
  $collection = substr($dsn, strrpos($dsn, '/') + 1);
  $mongo = $dsn ? new \Mongo($dsn) : new \Mongo;
  $db = $mongo->{$collection ?: 'default'};

  return $db;
}

function field_for($type, $key = NULL)
{
  static $set = array(
            'primary_key' => array('type' => 'hidden'),
            'text' => array('type' => 'textarea'),
            'string' => array('type' => 'text'),
            'integer' => array('type' => 'number'),
            'numeric' => array('type' => 'number'),
            'float' => array('type' => 'number'),
            'boolean' => array('type' => 'checkbox'),
            'binary' => array('type' => 'file'),
            'timestamp' => array('type' => 'datetime'),
            'datetime' => array('type' => 'datetime'),
            'date' => array('type' => 'date'),
            'time' => array('type' => 'time'),
            'object' => array('type' => 'object'), // native-dummy on mongo
            'array' => array('type' => 'array'), // TODO: support for postgres
            'hash' => array('type' => 'hash'),
            'enum' => array('type' => 'enum'),
            'json' => array('type' => 'json'),
            'set' => array('type' => 'set'),
          );

  if ( ! empty($set[$type])) {
    if (! $key) {
      return TRUE;
    }

    $out = $set[$type];
    $out['title'] = titlecase($key);

    return $out;
  }

  return FALSE;
}

function adjust_tags($from, $layout = FALSE)
{
  static $open = '/\s*<(script|style)[^>]*?>.*?<\/\\1>\s*/s',
         $close = '/\s*<(meta|link)[^>]*?\/?>\s*/s',
         $header = '/<(head)[^<>]*>(.+?)<\/\\1>/s',
         $descript = '/<title>(.+?)<\/title>/s';

  if (preg_match('/^(?:<html|["\'\[{])/', $from)) {
    return $from;
  }

  $separator = option('separator') ?: ' - ';
  $top_title = option('title');
  $sub_title = yield('title');

  $raw   =
  $head  =
  $body  =
  $title =
  $stack = array();

  $collect = function ($match)
    use(&$stack) {
    $stack []= array(
      'tag' => $match[1],
      'full' => trim($match[0]),
    );
  };

  $from = preg_replace_callback($header, function ($match)
    use(&$raw) {
      $raw []= $match[2];
    }, $from);

  preg_match($descript, $layout, $match) && $top_title = $match[1];
  preg_match($descript, $from, $match) && $sub_title = $match[1];

  $top_title && $title []= trim($top_title);
  $sub_title && $title []= trim($sub_title);

  $layout  = preg_replace($descript, '', $layout);
  $from = preg_replace($descript, '', $from);

  $from = preg_replace_callback($open, $collect, $from);
  $from = preg_replace_callback($close, $collect, $from);

  foreach ($stack as $one) {
    $one['tag'] === 'script' ? $body []= $one['full'] : $head []= $one['full'];
  }

  while ($head []= array_shift($raw));

  $from = preg_replace('/<(body)([^<>]*?)>/', "<\\1\\2>\n$from", $layout);
  $from = str_replace('</head>', sprintf("%s\n</head>", join("\n", $head)), $from);
  $from = str_replace('</body>', sprintf("%s\n</body>", join("\n", $body)), $from);
  $from = str_replace('</head>', sprintf("<title>%s</title>\n</head>", join($separator, $title)), $from);

  return $from;
}
