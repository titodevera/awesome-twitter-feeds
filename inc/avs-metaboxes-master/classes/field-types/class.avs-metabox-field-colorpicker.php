<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  final class Avs_Metabox_Field_Colorpicker extends Avs_Metabox_Field{

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description){
      parent::__construct($field_id, $column_width, $clear_after, $field_title, $field_description);
    }

    public function render_input($field_value){
      ob_start();
      ?>
      <input type="text" class="avs-metabox-colorpicker" id="<?php echo parent::get_field_id();?>" name="<?php echo parent::get_field_id();?>" value="<?php echo $field_value;?>">
      <?php
      return ob_get_clean();
    }

    public function sanitize_field($field_value){
      if ( !preg_match( '/^#[a-f0-9]{6}$/i', $field_value ) ) { // if user insert a HEX color with #
        $field_value = '#ffffff';
      }
      return $field_value;
    }

  }

?>
