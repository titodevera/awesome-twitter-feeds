<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  final class Avs_Metabox_Field_Select extends Avs_Metabox_Field{

    private $options;

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description, $options = array()){
      parent::__construct($field_id, $column_width, $clear_after, $field_title, $field_description);
      $this->options = $options;
    }

    public function render_input($field_value){
      ob_start();
      ?>
      <select name="<?php echo parent::get_field_id();?>" id="<?php echo parent::get_field_id();?>">
        <?php foreach ($this->options as $key => $value) : ?>
                <option value="<?php echo $value;?>" <?php selected( $field_value, $value ); ?>><?php echo $key;?></option>
        <?php endforeach; ?>
      </select>
      <?php
      return ob_get_clean();
    }

    public function sanitize_field($field_value){
      $field_value =  wp_strip_all_tags($field_value);
      return $field_value;
    }

  }

?>
