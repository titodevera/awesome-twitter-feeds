# avs-metaboxes
Developer's toolkit for building metaboxes for WordPress.

**Version:**        0.4

## How to
* Place "AVS Metaboxes" into your theme´s folder
* Copy the example below to the "functions.php"

## Example:

        require_once( 'avs-metaboxes/init.php' );

        $avs_metabox->add_field(array(
          'type' => 'editor',
          'id' => 'my_editor_field',
          'label' => 'My editor field:',
          'desc' => 'My editor field description',
          'col_width' => 'col12',
          'clear_after' => true
        ));
