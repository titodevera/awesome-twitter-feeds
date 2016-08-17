<?php
    /**
     * Widget class
     *
     * @since 0.4
     */

    namespace Awesome_Twitter_Feeds;

    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

    class ATF_Widget extends \WP_Widget {

        function __construct() {

            $params = array(
                'description' => __('Add a customized Twitter feed','awesome-twitter-feeds'),
                'name' => __('ATFeeds','awesome-twitter-feeds')
            );
            parent::__construct('ATF_Widget', '', $params);

        }

        /* @see WP_Widget::widget */
        public function widget($args, $instance) {

            ob_start();

              extract( $args );

              $show_title = ! empty( $instance['show_title'] ) ? '1' : '0';

              if($show_title){
                echo do_shortcode('[awesome-twitter-feeds id="'.$instance['widget'].'" title="'.$instance['title'].'"]');
              }else {
                echo do_shortcode('[awesome-twitter-feeds id="'.$instance['widget'].'"]');
              }

            echo ob_get_clean();

        }

        /* @see WP_Widget::update */
        public function update($new_instance, $old_instance) {
            $instance = $old_instance;
            $new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '', 'show_title' => '', 'widget' => '') );
            $instance['title'] = sanitize_text_field( $new_instance['title'] );
            $instance['show_title'] = $new_instance['show_title'] ? 1 : 0;
            $instance['widget'] = $new_instance['widget'];

            return $instance;
        }

        /**
         * Get all published widgets
         *
         * Note: Custom post type is "atfeeds"
         *
         * @since 0.4
         * @return type array
         */
        public function get_available_widgets(){
            $args = 'post_type=atfeeds&order=desc&posts_per_page=-1&post_status=publish';
            $the_query = new \WP_Query($args);
            $result = array();
            if($the_query->have_posts()){
                foreach($the_query->posts as $post){
                    array_push($result,array(
                        'post_id' => $post->ID,
                        'post_title' => $post->post_title
                    ));
                }
            }
            wp_reset_postdata();

            return $result;
        }


        /* @see WP_Widget::form */
        public function form($instance) {
            $available_widgets = $this->get_available_widgets();
            if(sizeof($available_widgets)>0){
                $title = '';
                $show_title = '';
                $widget = '';
                if(isset($instance['title'])){
                    $title = esc_attr($instance['title']);
                }
                if(isset($instance['show_title'])){
                    $show_title = esc_attr($instance['show_title']);
                }
                if(isset($instance['widget'])){
                    $widget = esc_attr($instance['widget']);
                }

                include(ATF_TEMPLATES_DIR.'/template-widget-admin.php');
            }else{
                echo '<p>'.__('No widgets available. ¡Create one!','awesome-twitter-feeds').'</p>';
            }
        }

    }
