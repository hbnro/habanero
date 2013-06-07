
  function __construct()
  {
    $this->error = array();
  }

  function index()
  {
    $result = \<?php echo $model_class; ?>::get('<?php echo join("', '", array_keys($fields)); ?>');
    $this-><?php echo $base; ?> = paginate_to(url_for('<?php echo $base; ?>'), $result, params('p'), 33);
  }

  function show()
  {
    if ( ! ($row = \<?php echo $model_class; ?>::find(params('id')))) {
      return redirect_to('<?php echo $base; ?>', array('error' => 'The <?php echo $name; ?> was not found'));
    }
    $this-><?php echo $name; ?> = $row;
  }

  function create()
  {
    if (params('save')) {
      $<?php echo $name; ?> = \<?php echo $model_class; ?>::create(params('row'));

      if ($<?php echo $name; ?>->is_valid()) {
        return redirect_to('<?php echo $base; ?>', array('success' => 'New <?php echo $name; ?> was created'));
      }

      $this->error = $<?php echo $name; ?>->errors;
    }
  }

  function modify()
  {
    if (params('update')) {
      $<?php echo $name; ?> = \<?php echo $model_class; ?>::first(params('id'));

      if ($<?php echo $name; ?>->update(params('row'))) {
        return redirect_to('<?php echo $base; ?>', array('success' => 'A <?php echo $name; ?> was updated'));
      }

      $this->error = $<?php echo $name; ?>->errors;
    }

    $this->show();
  }

  function delete()
  {
    if (\<?php echo $model_class; ?>::delete_all(array('<?php echo $pk; ?>' => params('id')))) {
      return redirect_to('<?php echo $base; ?>', array('success' => 'A <?php echo $name; ?> was deleted'));
    }

    return redirect_to('<?php echo $base; ?>', array('error' => 'The <?php echo $name; ?> was not found'));
  }

  function delete_all()
  {
    if (is_array($set = params('pk')) && array_filter($set)) {
      \<?php echo $model_class; ?>::delete_all(array('<?php echo $pk; ?>' => array_filter($set)));

      return redirect_to('<?php echo $base; ?>', array('success' => 'All <?php echo $name; ?> things was deleted'));
    }

    return redirect_to('<?php echo $base; ?>', array('notice' => 'No <?php echo $name; ?> things has found'));
  }
