
  function index()
  {
    $to = 1;
    $pg = Pallid\Paginate::build();

    $pg->set('count_page', $to);
    $pg->set('link_root', url_for('<?php echo $base; ?>'));

    $set  = <?php echo $model_class; ?>::get('<?php echo join("', '", array_keys($fields)); ?>');
    $from = $pg->offset(<?php echo $model_class; ?>::count(), params('p'));

    $this->view['<?php echo $base; ?>'] = $pg->bind($set->offset($from)->limit($to));
  }

  function show()
  {
    if ( ! ($row = <?php echo $model_class; ?>::find(params('id')))) {
      redirect_to('<?php echo $base; ?>', array('error' => 'The <?php echo $name; ?> was not found'));
    }
    $this->view['<?php echo $name; ?>'] = $row;
  }

  function create()
  {
    $this->view['error'] = array();

    if (params('save')) {
      if ($data = $this->validate(params('row'))) {
        <?php echo $model_class; ?>::create($data);
        redirect_to('<?php echo $base; ?>', array('success' => 'New <?php echo $name; ?> was created'));
      }
    }
  }

  function modify()
  {
    $this->view['error'] = array();

    if (params('update')) {
      if ($data = $this->validate(params('row'))) {
        <?php echo $model_class; ?>::update_all($data, array('<?php echo $pk;  ?>' => params('id')));
        redirect_to('<?php echo $base; ?>', array('success' => 'A <?php echo $name; ?> was updated'));
      }
    }

    $this->show();
  }

  function delete()
  {
    if (<?php echo $model_class; ?>::delete_all(array('<?php echo $pk; ?>' => params('id')))) {
      redirect_to('<?php echo $base; ?>', array('success' => 'A <?php echo $name; ?> was deleted'));
    }
    redirect_to('<?php echo $base; ?>', array('error' => 'The <?php echo $name; ?> was not found'));
  }

  function delete_all()
  {
    if (is_array($set = params('pk'))) {
      <?php echo $model_class; ?>::delete_all(array('<?php echo $pk; ?>' => $set));
      redirect_to('<?php echo $base; ?>', array('success' => 'All <?php echo $name; ?> things was deleted'));
    }
    redirect_to('<?php echo $base; ?>', array('notice' => 'No <?php echo $name; ?> things has found'));
  }


  private function validate($data)
  {
    Pallid\Validation::setup(array(
<?php foreach ($fields as $key => $val) { ?>
      '<?php echo $key; ?>' => array('The field <?php echo $key; ?> is required' => 'required'),
<?php } ?>
    ));

    if (Pallid\Validation::execute($data)) {
      return Pallid\Validation::data();
    }

    $this->view['error'] = Pallid\Validation::errors();
  }
