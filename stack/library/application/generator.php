<?php

i18n::load_path(__DIR__.DS.'locale', 'app');

app_generator::usage(ln('app.generator_usage'));

app_generator::alias('create', 'new');
app_generator::alias('status', 'st s');
app_generator::alias('config', 'conf c');
app_generator::alias('execute', 'exec run x');
app_generator::alias('generate', 'make mk gen g');


// create application
app_generator::implement('create', function () {
  info(ln('app.verifying_installation'));

  if (is_file(CWD.DS.'initialize'.EXT) && ! cli::flag('force')) {
    notice(ln('app.application'));

    $tmp = dir2arr(CWD, '*', DIR_RECURSIVE | DIR_EMPTY);
    $map = function ($tree, $self, $depth = 0) {
      foreach ($tree as $key => $val) {
        $pre = str_repeat(' ', $depth);

        if (is_array($val)) {
          cli::writeln("$pre  \clight_gray,black($key/)\c");
          $self($val, $self, $depth + 2);
        } else {
          $size = fmtsize(filesize($val));
          $val  = basename($val);

          cli::writeln("$pre  \bwhite($val)\b \clight_gray($size)\c");
        }
      }
    };

    $map($tmp, $map);

    error(ln('app.directory_must_be_empty'));
  } else {
    require __DIR__.DS.'scripts'.DS.'create_application'.EXT;
    done();
  }
});


// application status
app_generator::implement('status', function () {
  require __DIR__.DS.'scripts'.DS.'app_status'.EXT;
});


// script generation
app_generator::implement('generate', function($what = '', $name = '') {
  config(CWD.DS.'config'.DS.'application'.EXT);

  if ( ! in_array($what, array(
    'controller',
    'action',
    'model',
  ))) {
    error(ln('missing_arguments'));
  } else {
    info(ln('app.verifying_generator'));

    if ( ! $name) {
      error(ln("app.missing_{$what}_name"));
    } else {
      switch ($what) {
        case 'controller';
        case 'action';
        case 'model';
          require __DIR__.DS.'scripts'.DS."create_$what".EXT;
        break;
        default;
        break;
      }
    }
    done();
  }
});


// configuration status
app_generator::implement('config', function () {
  require __DIR__.DS.'scripts'.DS.'configuration'.EXT;
});


// task execution
app_generator::implement('execute', function ($name = '') {
  require __DIR__.DS.'scripts'.DS.'execute_task'.EXT;
});

/* EOF: ./stack/library/application/generator.php */
