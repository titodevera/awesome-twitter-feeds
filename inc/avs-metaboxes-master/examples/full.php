<?php

  $avs_metabox = new Avs_Metabox_Wrapper\Avs_Metabox('pages-metabox','Pages Metabox',array('page','post'));

  //Adds input text field
  $avs_metabox->add_field(array(
  	'type'					=> 'text',
  	'id' 						=> 'my_text_field',
  	'label' 				=> 'My text field:',
  	'desc' 					=> 'My text field description',
  	'col_width' 		=> 'col4'
  ));

  //Adds input number field
  $avs_metabox->add_field(array(
  	'type'					=> 'number',
  	'id' 						=> 'my_number_field',
  	'label' 				=> 'My number field:',
  	'desc' 					=> 'My number field description',
  	'col_width' 		=> 'col4'
  ));

  //Adds input email field
  $avs_metabox->add_field(array(
    'type'					=> 'email',
    'id' 						=> 'my_email_field',
    'label' 				=> 'My email field:',
    'desc' 					=> 'My email field description',
    'col_width' 		=> 'col4',
    'clear_after' 	=> true
  ));

  //Adds input checkbox field
  $avs_metabox->add_field(array(
    'type'					=> 'checkbox',
    'id' 						=> 'my_checkbox_field',
    'label' 				=> 'My checkbox field:',
    'desc' 					=> 'My checkbox field description',
    'col_width' 		=> 'col3'
  ));

  //Adds input url field
  $avs_metabox->add_field(array(
    'type'					=> 'url',
    'id' 						=> 'my_url_field',
    'label' 				=> 'My url field:',
    'desc' 					=> 'My url field description',
    'col_width' 		=> 'col9',
    'clear_after' 	=> true
  ));

  //Adds textarea field
  $avs_metabox->add_field(array(
    'type'					=> 'textarea',
    'id' 						=> 'my_textarea_field',
    'label' 				=> 'My textarea field:',
    'desc' 					=> 'My textarea field description',
    'col_width' 		=> 'col12',
    'clear_after' 	=> true
  ));

  //Adds image select field
  $avs_metabox->add_field(array(
    'type'					=> 'image',
    'id' 						=> 'my_image_field',
    'label' 				=> 'My image field:',
    'desc' 					=> 'My image field description',
    'col_width' 		=> 'col3'
  ));

  //Adds select field
  $avs_metabox->add_field(array(
    'type'					=> 'select',
    'id' 						=> 'my_select_field',
    'label' 				=> 'My select field:',
    'desc' 					=> 'My select field description',
    'col_width' 		=> 'col6',
    'options'				=> array(
      'option-1' => 'option-1-value',
      'option-2' => 'option-2-value'
    )
  ));

  //Adds WordPress colorpicker field
  $avs_metabox->add_field(array(
  	'type'					=> 'colorpicker',
  	'id' 						=> 'my_colorpicker_field',
  	'label' 				=> 'My colorpicker field:',
  	'desc' 					=> 'My colorpicker field description',
  	'col_width' 		=> 'col3',
  	'clear_after' 	=> true
  ));


  //Adds TyniMCE editor
  $avs_metabox->add_field(array(
  	'type'					=> 'editor',
  	'id' 						=> 'my_editor_field',
  	'label' 				=> 'My editor field:',
  	'desc' 					=> 'My editor field description',
  	'col_width' 		=> 'col12',
  	'clear_after' 	=> true
  ));

?>
