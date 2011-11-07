<?php

require __DIR__.DS.'initialize'.EXT;

i18n::load_path(__DIR__.DS.'locale', 'db');

app_generator::usage(ln('db.generator_title'), ln('db.generator_usage'));

app_generator::alias('db:status', 'db');
app_generator::alias('db:show_table', 'db:show show');
app_generator::alias('db:drop_table', 'db:drop drop');
app_generator::alias('db:rename_table', 'db:rename rename');
app_generator::alias('db:create_table', 'db:create table model');



// database status
app_generator::implement('db:status', function () {
  require __DIR__.DS.'scripts'.DS.'db_status'.EXT;
});


// show table
app_generator::implement('db:show_table', function ($table = '') {
  require __DIR__.DS.'scripts'.DS.'show_table'.EXT;
});


// drop table
app_generator::implement('db:drop_table', function ($table = '') {
  if (check_table($table)) {
    success(ln('db.table_dropping', array('name' => $table)));
    build_migration('drop_table', $table);
    done();
  }
});


// rename table
app_generator::implement('db:rename_table', function ($table = '', $to = '') {
  check_table($table);

  if ( ! $to) {
    error(ln('db.table_name_missing'));
  } elseif (in_array($to, db::tables())) {
    error(ln('db.table_already_exists', array('name' => $to)));
  } else {
    success(ln('db.renaming_table_to', array('from' => $table, 'to' => $to)));
    build_migration('rename_table', $table, $to);
    done();
  }
});


// create table
app_generator::implement('db:create_table', function ($table = '') {
  $args = array_slice(func_get_args(), 1);
  require __DIR__.DS.'scripts'.DS.'create_table'.EXT;
});


// add columns
app_generator::implement('db:add_column', function ($to = '') {
  require __DIR__.DS.'scripts'.DS.'add_column'.EXT;
});


// remove columns
app_generator::implement('db:remove_column', function ($from = '') {
  require __DIR__.DS.'scripts'.DS.'remove_column'.EXT;
});


// rename column name
app_generator::implement('db:rename_column', function ($from = '') {
  require __DIR__.DS.'scripts'.DS.'rename_column'.EXT;
});


// change column definition
app_generator::implement('db:change_column', function ($from = '') {
  require __DIR__.DS.'scripts'.DS.'change_column'.EXT;
});


// create table index
app_generator::implement('db:add_index', function ($to = '', $name = '') {
  require __DIR__.DS.'scripts'.DS.'add_index'.EXT;
});


// remove table index
app_generator::implement('db:remove_index', function ($from = '', $name = '') {
  require __DIR__.DS.'scripts'.DS.'remove_index'.EXT;
});


// database backups
app_generator::implement('db:backup', function () {
  require __DIR__.DS.'scripts'.DS.'backups'.EXT;
});


// execute migrations
app_generator::implement('db:migrate', function () {
  require __DIR__.DS.'scripts'.DS.'load_migrations'.EXT;
});



function check_table($name) {
  info(ln('db.verifying_structure'));

  if ( ! $name) {
    error(ln('db.table_name_missing'));
  } elseif ( ! in_array($name, db::tables())) {
    error(ln('db.table_not_exists', array('name' => $name)));
  } else {
    return TRUE;
  }
}

function check_column($type) {
  static $set = array(
            'primary_key',
            'text',
            'string',
            'integer',
            'numeric',
            'float',
            'boolean',
            'binary',
            'timestamp',
            'datetime',
            'date',
            'time',
          );

  return in_array($type, $set);
}

function build_migration($callback) {
  $args = array_slice(func_get_args(), 1);
  require __DIR__.DS.'scripts'.DS.__FUNCTION__.EXT;
  build_schema();
}

function build_schema() {
  require __DIR__.DS.'scripts'.DS.__FUNCTION__.EXT;
}

/* EOF: ./stack/library/db/generator.php */
