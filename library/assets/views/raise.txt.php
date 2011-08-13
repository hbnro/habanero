<?php

ob_start();

echo ents($message, TRUE), "\n";

echo "\nApplication\n-----------\n";  
dump(array(
  'user' => "$user@$host",
  'route' => $route,
  'params' => params(),
  'bootstrap' => APP_LOADER,
), TRUE);

echo "\n\nConfig\n------\n";  
dump(config(), TRUE);

if (isset($env))
{
  echo "\n\nEnvironment\n-----------\n";  
  dump($env, TRUE);
}

if (isset($global))
{
  echo "\n\nGlobals\n-------\n";  
  dump($global, TRUE);
}

if (isset($headers))
{
  echo "\n\nHeaders\n-------\n";  
  dump($headers, TRUE);
}

if (isset($constants))
{
  echo "\n\nConstants\n---------\n";  
  dump($constants, TRUE);
}

if (isset($backtrace))
{
  echo "\n\nBacktrace\n---------\n";  
  dump($backtrace, TRUE);
}


echo "\n\nIncluded files\n--------------\n";  
dump(get_included_files(), TRUE);

$content = preg_replace('/^/m', '  ', ob_get_clean());

echo "\n Error => {\n", $content, "\n}";
echo '(', ticks(defined('BEGIN') ? BEGIN : 0), ")\n\n";