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

foreach ($set as $method => $sub) {
  foreach ($sub as $one) {
    foreach (array('to', 'path', 'match') as $i) {
      isset($one[$i]) && (strlen($one[$i]) > $$i) && $$i = strlen($one[$i]);
    }
  }
}


if ( ! empty($set)) {
  info("\n  Routes:\n");

  foreach ($set as $method => $set) {
    $out []= "    \bwhite($method)\b";

    foreach ($set as $one) {
      @list($class, $action) = explode('#', $one['to']);
      $pad = str_pad($action, $to - strlen($class), ' ', STR_PAD_RIGHT);
      $path = ! empty($one['path']) ? " {$one['path']}" : '';

      $out []= sprintf("      \cgreen(%-{$match}s)\c  \cbrown($class)\c\clight_gray(#$pad)\c$path", $one['match']);
    }
    $out []= '';
  }

  writeln(colorize(join("\n", $out)));
} else {
  error("\n  No defined routes\n");
}
