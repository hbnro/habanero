<?php

tamal_helper::implement('php', function ($value) {
  return '<' . "?php $value ?>";
});
