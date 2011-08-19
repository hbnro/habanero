<?php

ob_start();

printf("\n%s\n--\n", ln('includes'));
dump(get_included_files(), TRUE);


if (isset($backtrace))
{
  printf("\n\n%s\n--\n", ln('backtrace'));
  dump($backtrace, TRUE);
}


printf("\n\n%s\n--\n", ln('config'));
dump(config(), TRUE);


if (isset($env))
{
  printf("\n\n%s\n--\n", ln('environment'));
  dump($env, TRUE);
}


printf("\n\n%s\n--\n", ln('application'));
dump(array(
  'user' => "$user@$host",
  'route' => $route,
  'params' => function_exists('params') ? params() : array(),
  'bootstrap' => APP_LOADER,
), TRUE);

echo sprintf("\n\n%s\n--\n", ln('error')), ents($message, TRUE);

$content = preg_replace('/^/m', '  ', ob_get_clean());

echo "\n => {\n", $content, "\n}";
echo '(', ticks(defined('BEGIN') ? BEGIN : 0), ")\n\n";