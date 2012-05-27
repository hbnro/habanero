<?php

tamal_helper::implement('escape', function ($value) {
  return htmlspecialchars($value);
});
