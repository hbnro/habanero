<?php

/**
 * MVC base controller
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
   * @param  mixed Object|Array
   * @return void
   */
  public static function to_json($obj) {
    response(json_encode($obj), array(
      'headers' => array(
        'content-type' => 'application/json',
      ),
    ));
  }

}

/* EOF: ./stack/library/app/controller.php */
