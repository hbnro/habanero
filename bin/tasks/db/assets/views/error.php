- if $error
  ul.error { data => array(errors => 'true') }
    - foreach $error as $field => $text
      li { data => compact('field') } = "$field $text"

- if $flash = flash()
  ul.status
  - foreach $flash as $type => $text
    li { data => compact('type') } = "$type $text"
