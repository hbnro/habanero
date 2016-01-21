<?php

declare(ticks = 1);

error_reporting(-1);
date_default_timezone_set('America/Mexico_City');

call_user_func(function () use ($argv) {
  $top_vendor = join(DIRECTORY_SEPARATOR, [dirname(dirname(dirname(__DIR__))), 'autoload.php']);
  $this_vendor = join(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'vendor', 'autoload.php']);

  require is_file($top_vendor) ? $top_vendor : $this_vendor;

  $cli = new \Clipper\Shell($argv);

  $cli->colors->alias('debug', 'c:brown');

  $cli->params->parse([
    'showHelp' => ['h', 'help', \Clipper\Params::PARAM_NO_VALUE, 'Display this help'],
  ], true);

  if ($cli->params->showHelp || (sizeof($argv) <= 1)) {
    $cmd = $cli->params->getCommand();
    $cmd = $cmd === 'bin/hs' ? $cmd : 'hs';
    $cli->writeln("\nUsage: $cmd [options] <folders|files>\n");
    $cli->writeln($cli->params->usage());
    $cli->writeln();
    exit;
  }
});
