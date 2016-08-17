jQuery.noConflict();

jQuery(document).ready(function($){

  var media_uploader = null;
  function open_media_uploader_image($clickedElement){
      media_uploader = wp.media({
          frame:    "post",
          state:    "insert",
          multiple: false
      });

      media_uploader.on("insert", function(){
          var json = media_uploader.state().get("selection").first().toJSON();
          var image_id = json.id;
          var image_url = json.url;
          var image_thumb = json.sizes.thumbnail;

          $clickedElementParent = $clickedElement.parent();
          $('input.avs-metabox-image-field',$clickedElementParent).val(image_id);
          $('.avs-metabox-image-preview',$clickedElementParent).html('<img src="'+image_thumb.url+'" width="'+image_thumb.width+'" height="'+image_thumb.height+'">');
      });

      media_uploader.open();
  }

  $( '.avs-metabox-cont .avs-metabox-colorpicker' ).wpColorPicker();

  $( '.avs-metabox-cont .avs-metabox-image').on('click',function(){
    open_media_uploader_image($(this));
  });

  $( '.avs-metabox-cont .avs-metabox-image-remove').on('click',function(){
    var $imageField = $(this).parents('.avs-metabox-field').find('input.avs-metabox-image-field');
    var $imagePreview = $(this).parents('.avs-metabox-field').find('.avs-metabox-image-preview');
    $imageField.val('');
    $imagePreview.html('No image selected');
  });

});
