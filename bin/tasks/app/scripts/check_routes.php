<?php

$set =
$out = array();
$old = Broil\Routing::all();

arg('g get') && isset($old['GET']) && $set['GET'] = $old['GET'];
arg('u put') && isset($old['PUT']) && $set['PUT'] = $old['PUT'];
arg('p post') && isset($old['POST']) && $set['POST'] = $old['POST'];
arg('d delete') && isset($old['DELETE']) && $set['DELETE'] = $old['DELETE'];

arg('g u p d get put post delete') OR $set = $old;

$to =
$path =
$match = 0;
$limit = 52;

foreach ($set as $method => $sub) {
  foreach ($sub as $one) {
    foreach (array('to', 'path', 'match') as $i) {
      if (isset($one[$i]) && is_scalar($one[$i]) && (strlen($one[$i]) > $$i)) {
        $$i = strlen($one[$i]) > $limit ? $limit : strlen($one[$i]);
      }
    }
  }
}

if ( ! empty($set)) {
  info("\n  Routes:\n");

  foreach ($set as $method => $set) {
    $top = '';
    $out []= "    \bwhite($method)\b";

    foreach ($set as $one) {
      if (is_scalar($one['to'])) {
        if (is_url($one['to'])) {
          $action = \Labourer\Web\Text::short($one['to'], floor($limit / 2), ceil($limit / 2), '...') . ' ';
          $class = '';
        } else {
          @list($class, $action) = explode('#', $one['to']);
        }
      } else {
        $class = '~';
        $action = '';
      }

      $sub = $one['prefix'] ?: $one['subdomain'];

      if ($sub && ($sub <> $top)) {
        $top = $sub;
        $out []= "      \cpurple($sub)\c";
      }

      $name = ! empty($one['path']) ? str_pad($one['path'], $path, ' ', STR_PAD_RIGHT) : '';
      $call = str_pad($action, $to - strlen($class), ' ', STR_PAD_RIGHT);
      $char = $class && $action ? '#' : '';


      $out []= sprintf("        \cgreen(%-{$match}s)\c  \cbrown($class)\c\clight_gray($char$call)\c $name", $one['match']);
    }
    $out []= '';
  }

  writeln(colorize(join("\n", $out)));
} else {
  error("\n  No defined routes\n");
}
