<?php

  namespace Avs_Metabox_Wrapper;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  class Avs_Metabox{

    private $id = '';
    private $title = '';
    private $post_types = array();
    private $fields = array();

    function __construct($id, $title, $post_types){
      $this->id = $id;
      $this->title = $title;
      $this->post_types = $post_types;
      add_action('add_meta_boxes', array($this,'meta_box'));
      add_action('save_post', array($this,'meta_box_save'));
    }

    public function add_field($field_settings){

      if(isset($field_settings['clear_after']) && $field_settings['clear_after'] == true){
        $field_settings['clear_after'] = true;
      }else if(!isset($field_settings['clear_after'])){
        $field_settings['clear_after'] = false;
      }

      switch ($field_settings['type']) {
        case 'text':
          $this->fields[] = new Avs_Metabox_Field_Text(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'number':
          $this->fields[] = new Avs_Metabox_Field_Number(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'checkbox':
          $this->fields[] = new Avs_Metabox_Field_Checkbox(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'colorpicker':
          $this->fields[] = new Avs_Metabox_Field_Colorpicker(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'textarea':
          $this->fields[] = new Avs_Metabox_Field_Textarea(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'image':
          $this->fields[] = new Avs_Metabox_Field_Image(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'select':
          $this->fields[] = new Avs_Metabox_Field_Select(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc'],
            $field_settings['options']
          );
          break;
        case 'email':
          $this->fields[] = new Avs_Metabox_Field_Email(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'url':
          $this->fields[] = new Avs_Metabox_Field_Url(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
        case 'editor':
          $this->fields[] = new Avs_Metabox_Field_Editor(
            $field_settings['id'],
            $field_settings['col_width'],
            $field_settings['clear_after'],
            $field_settings['label'],
            $field_settings['desc']
          );
          break;
      }

    }

    public function meta_box(){
      add_meta_box( $this->id, $this->title, array($this,'meta_box_callback'), $this->post_types, 'advanced', 'default', null );
    }

    public function meta_box_callback(){
      echo '<div class="avs-metabox-cont avs-metabox-clearfix">';
      foreach ($this->fields as $field) {
        echo $field->render_field();
        echo ($field->get_clear_after()) ? '<div class="avs-metabox-clearfix"></div>' : '';
      }
      echo '</div>';
    }

    public function meta_box_save($post_id){

      foreach ($this->fields as $field) {
        $field_id = $field->get_field_id();
        $field_id_nonce = $field_id . '_nonce';

        if(isset($_POST['post_type']) && 'page' == $_POST['post_type']){
          if(!current_user_can( 'edit_page', $post_id )){
            return $post_id;
          }
        }else{
          if(!current_user_can( 'edit_post', $post_id )){
            return $post_id;
          }
        }

        if ( isset( $_POST[$field_id] ) && isset( $_POST[$field_id_nonce] ) && wp_verify_nonce( $_POST[$field_id_nonce], 'edit' ) && !defined( 'DOING_AUTOSAVE' ) ) {
          $field_value = $field->sanitize_field($_POST[$field_id]);
          update_post_meta( $post_id, $field_id, $field_value );
        }else{
          delete_post_meta( $post_id, $field_id );
        }

      }

    }

  }

?>
