<?php

namespace Sauce\App;

class Controller
{

  public $head = array();

  public $title = '';

  public $layout = 'default';

  public $responds_to = array('html', 'json');


  public function as_json($data, array $params = array())
  {
    return array(200, array('Content-Type' => 'application/json'), json_encode($data));
  }

}
