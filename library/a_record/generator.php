<?php

require __DIR__.DS.'initialize'.EXT;

app_generator::usage(ln('ar.generator_title'), ln('ar.generator_usage'));

app_generator::alias('ar:console', 'console c');
app_generator::alias('ar:backup', 'backup');
app_generator::alias('ar:model', 'model');


// models
app_generator::implement('ar:model', function ($name = '') {
  @list($name, $table) = explode(':', $name);

  if ( ! $name) {
    error(ln('ar.missing_model_name'));
  } else {
    $out_file = mkpath(APP_PATH.DS.'models').DS.$name.EXT;

    if (is_file($out_file)) {
      error(ln('ar.model_already_exists', array('name' => $name)));
    } else {
      success(ln('ar.model_class_building', array('name' => $name)));

      $type   = cli::flag('parent') ?: 'db_model';
      $parent = $table ? "\n  public static \$table = '$table';" : '';
      $code   = "<?php\n\nclass $name extends $type"
              . "\n{{$parent}\n}\n";

      write($out_file, $code);
    }
  }
});

// backups
app_generator::implement('ar:backup', function ($model = '') {
  @list($model, $name) = explode(':', $model);

  $model_file = APP_PATH.DS.'models'.DS.$model.EXT;
  $name = $name ? "{$model}_$name": sprintf("{$model}_%s", date('YmdHis'));

  if ( ! is_file($model_file)) {
    error(ln('ar.missing_model_file', array('name' => $model)));
  } else {
    $php_file = mkpath(APP_PATH.DS.'database'.DS.'backup').DS.$name.EXT;
    $path = str_replace(APP_PATH.DS, '', $php_file);

    if (cli::flag('import')) {
      info(ln('ar.verifying_import'));

      if ( ! $model) {
        error(ln('ar.import_model_missing'));
      } else {
        if ( ! is_file($php_file)) {
          error(ln('ar.import_file_missing', array('path' => $path)));
        } else {
          success(ln('ar.importing', array('path' => $path)));

          cli::flag('delete-all') && $model::delete_all();

          $data = include $php_file;

          foreach ($data as $one) {
            $model::count($one) OR $model::create($one);
          }
          done();
        }
      }
    } else {
      info(ln('ar.verifying_export'));

      if ( ! $name) {
        error(ln('ar.export_model_missing'));
      } else {
        if (is_file($php_file)) {
          error(ln('ar.export_already_exists'));
        } else {
          success(ln('ar.exporting', array('path' => $path)));

          $data = array();

          foreach ($model::all() as $one) {
            $data []= $one->fields();
          }

          write($php_file, sprintf('<' . "?php return %s;\n", var_export($data, TRUE)));
          done();
        }
      }
    }
  }
});


// inspect records
app_generator::implement('ar:console', function () {
  import('a_record');

  $scope = new stdClass;

  cli::main(function ()
    use($scope) {

    $test = cli::readln('>>> ');

    if (in_array($test, array('exit', 'quit'))) {
      cli::quit();
    } else {
      // TODO: implement more expressions!
      if (preg_match('/^(\w+\.\w+)(?:\s+(.*?))?$/', $test, $match)) {
        $test = explode('.', $match[1]);
        $args = explode(' ', ! empty($match[2]) ? trim($match[2]) : '');

        $out  = @$test[0]::apply($test[1], $args);

        pretty(function ()
          use($out) {
          if (is_array($out)) {
            $max = sizeof($out) - 1;
            foreach ($out as $i => $one) {
              echo "\bcyan(>>>)\b $one\n";
              ($i < $max) && print("\n");
            }
          } else {
            ! is_null($out) ? print("\bcyan(>>>)\b $out\n") : print(">>> $out\n");
          }
        });
      }
    }
  });
});

/* EOF: ./library/a_record/generator.php */
