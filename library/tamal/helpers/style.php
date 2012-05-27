<?php

tamal_helper::implement('style', function ($value) {
  return tag('style', array('type' => 'text/css'), "\n$value\n");
});
