<?php

function xpath() {
  $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, join('/', func_get_args()));
  $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
  $absolutes = array();
  foreach ($parts as $part) {
      if ('.'  == $part) continue;
      if ('..' == $part) {
          array_pop($absolutes);
      } else {
          $absolutes[] = $part;
      }
  }
  $path=implode(DIRECTORY_SEPARATOR, $absolutes);
  return $path;
}

call_user_func(function () {

    // $xargs = \Sauce\Shell\CLI::values();
    // $command = array_shift($xargs);
    // $status = 0;

  // $paths = [];
  // $paths []= path(dirname(dirname(__DIR__)), 'tasks');

  var_dump(path('a', 'b', 'c'), xpath('a', 'b', 'c'));
  var_dump(path('a/b/c', '..', 'd'), xpath('a/b/c', '..', 'd'));
  var_dump(path('a/b/c', '../../d/e/../f'), xpath('a/b/c', '../../d/e/../f'));

    // \IO\Dir::open($paths, function ($file) {
    //     if (is_dir($file)) {
    //       require path($file, 'initialize.php');
    //     } else {
    //       require $file;
    //     }
    //   });

    // if (! $command) {
    //   help(arg('help'));
    // } else {
    //   writeln(colorize(sprintf('Environment: \cyellow(%s)\c', APP_ENV)));

    //   try {
    //     \Sauce\Shell\Task::exec($command, $xargs);

    //     success('Done, without errors.');
    //   } catch (\Exception $e) {
    //     $status = 1;

    //     error("\n  \cred,black({$e->getMessage()})\c\n");
    //     notice('Aborted due warnings.');
    //   }
    // }

    // exit($status);
});
