<?php

function home_path(array $vars = array(), $abs = FALSE)
{
  return link_to('/home', $vars, $abs);
}
