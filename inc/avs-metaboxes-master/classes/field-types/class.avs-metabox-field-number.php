<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  final class Avs_Metabox_Field_Number extends Avs_Metabox_Field{

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description){
      parent::__construct($field_id, $column_width, $clear_after, $field_title, $field_description);
    }

    public function render_input($field_value){
      ob_start();
      ?>
      <input type="number" id="<?php echo parent::get_field_id();?>" name="<?php echo parent::get_field_id();?>" value="<?php echo $field_value;?>">
      <?php
      return ob_get_clean();
    }

    public function sanitize_field($field_value){
      $field_value = filter_var($field_value,FILTER_SANITIZE_NUMBER_INT);
      return $field_value;
    }

  }

?>
