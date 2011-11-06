<?php

class app_task extends prototype {

  protected static $defs = array();

  final public function config(Closure $lambda) {
    $set = new stdClass;

    $lambda($set);

    static::$defs = (array) $set;
  }

}
