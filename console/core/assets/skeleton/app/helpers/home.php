<?php

function home_path(array $vars = array(), $abs = FALSE)
{
  return url_for('/home', $vars, $abs);
}
