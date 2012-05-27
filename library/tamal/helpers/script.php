<?php

tamal_helper::implement('script', function ($value) {
  return tag('script', array('type' => 'text/javascript'), "\n$value\n");
});
