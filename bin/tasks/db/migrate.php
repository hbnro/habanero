<?php

return;

$model = arg('migrate');

$dsn = option('database.' . $model::CONNECTION);
$db = \Grocery\Base::connect($dsn);

$columns = $model::columns();
$indexes = $model::indexes();

if ( ! isset($db[$model::table()])) {
  $db[$model::table()] = $columns;
}

$table = $db[$model::table()];


\Grocery\Helpers::hydrate($table, $columns, $indexes);

echo "Done\n";
