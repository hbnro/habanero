<?php

/**
 * Application base controller
 */

class app_controller extends prototype
{// TODO: implement... what stuff?

  /**#@+
   * @ignore
   */

  // view instance vars
  public static $view = array();

  // head hooks?
  public static $head = array();

  // default title
  public static $title = '';

  // default assets
  public static $source = '';

  // default layout
  public static $layout = 'default.html';

  // output response
  public static $response = array(
    'status' => 200,
    'headers' => array(
      'content-type' => 'text/html',
    ),
  );

  /**#@-*/



  /**
   * JSON output
   *
   * @param  mixed   Object|Array
   * @param  integer Response status
   * @return void
   */
  public static function to_json($obj, $status = 200) {
    return array($status, json_encode($obj), array(
      'content-type' => 'application/json',
    ));
  }

}

/* EOF: ./library/application/app_controller.php */
