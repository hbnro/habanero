<?php

@list($name) = array_shift($params);

if ( ! $name) {
  error("\n  Missing model name\n");
} else {
  info("\n  Model schema:\n");

  // TODO: mongo support
  $path = arg('p', 'path') ?: 'models';
  $klass = arg('c', 'class') ?: $name;

  /*$dsn = option('database.' . $klass::CONNECTION);
  $db = \Grocery\Base::connect($dsn);

  $columns = $klass::columns();
  $indexes = $klass::indexes();

  if ( ! isset($db[$klass::table()])) {
    $db[$klass::table()] = $columns;
  }

  $table = $db[$klass::table()];

  \Grocery\Helpers::hydrate($table, $columns, $indexes);*/

  #writeln(preg_replace('//', '', print_r($db)));

}
