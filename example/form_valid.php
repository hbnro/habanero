<?php

require dirname(__DIR__).'/library/initialize.php';

run(function()
{

  uses('tetl/server');
  uses('tetl/router');
  
  route('POST /create', function()
  {
    uses('tetl/valid');
    uses('tetl/session');
    
    valid::setup(array(
      'row[address]' => 'required is_email|is_url|is_ip',
      'row[pass]' => 'required =re_pass',
      'row[hex]' => 'is_hex',
      'row[alnum]' => 'is_alnum',
      'row[upper]' => 'required is_upper',
      'row[time]' => 'required is_time',
      'row[date]' => 'is_date',
      'row[timestamp]' => 'is_timestamp',
      'row[slug]' => 'required is_slug',
      'row[money]' => 'is_money',
      'row[json]' => 'is_json',
    ));
    
    dump(is_safe(), TRUE);
    
    
    dump(valid::done($_POST), TRUE);
    
    dump(valid::error(), TRUE);
    
    dump(valid::data(), TRUE);
    
  });
  
  route('*', function()
  {
    uses('tetl/form');
    
    echo form::to('POST /create', function()
    {
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Email address, URL or IP',
        'name' => 'row[address]',
      ));
      
      echo form::field(array(
        'type' => 'password',
        'label' => 'Password',
        'name' => 'row[pass]',
      ));
      
      echo form::field(array(
        'type' => 'password',
        'label' => '(repeat password)',
        'name' => 're_pass',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is HEX',
        'name' => 'row[hex]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Alphanumeric',
        'name' => 'row[alnum]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is UPPER',
        'name' => 'row[upper]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is time',
        'name' => 'row[time]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is date',
        'name' => 'row[date]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is timestamp',
        'name' => 'row[timestamp]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is slug',
        'name' => 'row[slug]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is money',
        'name' => 'row[money]',
      ));
      
      echo form::field(array(
        'type' => 'text',
        'label' => 'Is JSON',
        'name' => 'row[json]',
      ));
      
      
      echo form::submit('send');
      
    });
    
  });
  
});
