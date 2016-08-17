<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  final class Avs_Metabox_Field_Editor extends Avs_Metabox_Field{

    function __construct($field_id, $column_width, $clear_after, $field_title, $field_description){
      parent::__construct($field_id, $column_width, $clear_after, $field_title, $field_description);
    }

    public function render_input($field_value){
      ob_start();
      wp_editor( $field_value, parent::get_field_id() );
      return ob_get_clean();
    }

    public function sanitize_field($field_value){
      return $field_value;
    }

  }

?>
