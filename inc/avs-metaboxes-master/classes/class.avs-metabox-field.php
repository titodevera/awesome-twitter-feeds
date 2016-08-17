<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  abstract class Avs_Metabox_Field{

    private $field_id, $column_width, $clear_after, $field_title, $field_description;

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description = ''){
      $this->field_id = $field_id;
      $this->column_width = $column_width;
      $this->clear_after = $clear_after;
      $this->field_title = $field_title;
      $this->field_description = $field_description;
    }

    abstract function render_input($field_value);
    abstract function sanitize_field($field_value);

    public function get_instance(){
      return $this;
    }

    public function render_field(){
      $field_value = get_post_meta(get_the_ID(),$this->field_id,true);
      ?>
      <div class="avs-metabox-<?php echo $this->column_width;?> avs-metabox-field avs-metabox-clearfix">
        <label for="<?php echo $this->field_id;?>"><?php echo $this->field_title;?></label>
        <?php echo $this->render_input($field_value);?>
        <?php if($this->field_description != ''): ?>
                <p class="avs-metabox-field-desc description">
                  <?php echo $this->field_description;?>
                </p>
        <?php endif; ?>
        <?php wp_nonce_field( 'edit', $this->field_id.'_nonce' ); ?>
      </div>
      <?php
    }

    public function get_field_id(){
      return $this->field_id;
    }

    public function get_field_title(){
      return $this->field_title;
    }

    public function get_column_width(){
      return $this->column_width;
    }

    public function get_clear_after(){
      return $this->clear_after;
    }

  }

?>
