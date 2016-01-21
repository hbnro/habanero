<?php

call_user_func(function () {

    // $xargs = \Sauce\Shell\CLI::values();
    // $command = array_shift($xargs);
    // $status = 0;

  $paths = [];
  $paths []= path(dirname(dirname(__DIR__)), 'tasks');

  var_dump($paths);

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
