<?php

/**
 * Facebook functions
 */

fb::implement('friends_using', function ()
{
  $me  = fb::me();
  $uid = ! empty($me['id']) ? $me['id'] : -1;

  $args = func_get_args();
  $what = $args ? join(',', $args) : 'name';

  return fb::query("SELECT $what FROM user WHERE has_added_app=1 and uid IN (SELECT uid2 FROM friend WHERE uid1=$uid)");
});

fb::implement('page_likes', function ()
{
  $me  = fb::me();
  $uid = ! empty($me['id']) ? $me['id'] : -1;

  $args = func_get_args();
  $what = $args ? join(',', $args) : 'name';

  return fb::query("SELECT $what FROM page WHERE page_id IN(SELECT page_id FROM page_fan WHERE uid=$uid)");
});

fb::implement('fql_query', function ($fql, $callback = '')
{
  return fb::api(array(
    'callback' => $callback,
    'method' => 'fql.query',
    'query' => $fql,
  ));
});

/* EOF: ./library/facebook/helpers.php */
