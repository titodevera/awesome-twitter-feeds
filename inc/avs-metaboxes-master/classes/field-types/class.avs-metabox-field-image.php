<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  final class Avs_Metabox_Field_Image extends Avs_Metabox_Field{

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description){
      parent::__construct($field_id, $column_width, $clear_after, $field_title, $field_description);
    }

    public function render_input($field_value){
      ob_start();
      ?>
      <input type="text" class="avs-metabox-image-field" id="<?php echo parent::get_field_id();?>" name="<?php echo parent::get_field_id();?>" value="<?php echo $field_value;?>" >
      <a href="#" class="button avs-metabox-image">Select image</a>
      <div>
        <div class="avs-metabox-image-preview">
          <?php
            $image_preview = wp_get_attachment_image( $field_value, 'thumbnail' );
            if(!empty($image_preview)){
              echo $image_preview;
            }else{
              echo 'No image selected';
            }
          ?>
          <span class="dashicons dashicons-no avs-metabox-image-remove" title="Remove this image"></span>
        </div>
      </div>
      <?php
      return ob_get_clean();
    }

    public function sanitize_field($field_value){
      $field_value = filter_var($field_value,FILTER_SANITIZE_NUMBER_INT);
      return $field_value;
    }

  }

?>
