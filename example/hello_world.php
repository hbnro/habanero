<?php

require dirname(__DIR__).'/library/initialize.php';

run(function()
{  
  
  import('tetl/server');
  import('tetl/router');
  
  route('/hi/:name', function()
  {
    echo 'Hi ', params('name'), '!';
    echo '<br/>&mdash;', ticks(BEGIN), 's';
  });
  
  route('*', function()
  {
    $js = "var url=this.href+prompt('Who are you?');document.location.href=url;return false;";
    
    echo '<a href="', link_to('hi/'), '" onclick="', $js, '">Are you talkin\' to me?</a>';
    echo '<br/>&mdash;', ticks(BEGIN), 's';
  });
  
});