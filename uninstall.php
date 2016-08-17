<?php
  namespace Awesome_Twitter_Feeds;

  defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

  $atfeeds_posts = get_pages( array( 'post_type' => 'atfeeds' ) );
  foreach( $atfeeds_posts as $post ) {
    wp_delete_post( $post->ID, true);
  }
