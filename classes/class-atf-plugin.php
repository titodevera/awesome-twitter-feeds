<?php
    /**
     * Plugin class
     *
     * @since 1.0
     */

    namespace Awesome_Twitter_Feeds;
    use Abraham\TwitterOAuth\TwitterOAuth;

    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

    final class ATF_Plugin{

        private $widget_instance = '';
        private static $cache_seconds = 60;

        function __construct(Atf_Widget $widget_instance){
            $this->widget_instance = $widget_instance;
            add_action('plugins_loaded', array($this,'load_textdomain'));
            add_action('init', array($this,'register_post_type'));
            add_action('wp_enqueue_scripts', array($this,'register_scripts'));
            add_action('wp_ajax_load_atf', array($this,'ajax_callback'));
            add_action('wp_ajax_nopriv_load_atf', array($this,'ajax_callback'));
            add_shortcode('awesome-twitter-feeds', array($this,'register_shortcode'));
            if(is_plugin_active('js_composer/js_composer.php')){
                add_action('vc_before_init', array($this,'vc_map_shortcode'));
            }
            add_action('widgets_init', function(){register_widget('\Awesome_Twitter_Feeds\ATF_Widget');});
        }

        /**
         * loads plugin text domain
         *
         * @since 1.0
         * @return void
         */
        public function load_textdomain() {
          load_plugin_textdomain( 'awesome-twitter-feeds', false, ATF_PATH . '/lang' );
        }


        /**
         * Configured options of a specific twitter feed
         *
         * Note: Options are post meta data
         *
         * @since 1.0
         * @param string $post_id
         * @return array
         */
        private function get_options_data($post_id){
            $result_array = array(
                "number_of_tweets" => '',
                "height" => '',
                "profile_img" => '',
                "follow" => '',
                "media" => '',
                "links" => '',
                "theme" => ''
            );

            $result_array["number_of_tweets"] = esc_html(get_post_meta($post_id, '_atf_number_of_tweets', true ));
            $result_array["height"] = esc_html(get_post_meta($post_id, '_atf_height', true ));
            $result_array["profile_img"] = esc_html(get_post_meta($post_id, '_atf_profile_img', true ));
            $result_array["follow"] = esc_html(get_post_meta($post_id, '_atf_follow', true ));
            $result_array["media"] = esc_html(get_post_meta($post_id, '_atf_media', true ));
            $result_array["links"] = esc_html(get_post_meta($post_id, '_atf_links', true ));
            $result_array["theme"] = esc_html(get_post_meta($post_id, '_atf_theme', true ));

            return $result_array;
        }


        /**
         * Configured twitter api access data of a specific twitter feed
         *
         * Note: Options are post meta data
         *
         * @since 1.0
         * @param string $post_id
         * @return array
         */
        private function get_api_data($post_id){
            $result_array = array(
                "api_key" => '',
                "api_secret" => '',
                "access_token" => '',
                "access_token_secret" => ''
            );
            $result_array["api_key"] = esc_html(get_post_meta($post_id, '_atf_api_key', true ));
            $result_array["api_secret"] = esc_html(get_post_meta($post_id, '_atf_api_secret', true ));
            $result_array["access_token"] = esc_html(get_post_meta($post_id, '_atf_access_token', true ));
            $result_array["access_token_secret"] = esc_html(get_post_meta($post_id, '_atf_access_token_secret', true ));
            return $result_array;
        }


        /**
         * Ajax callback for showing widgets
         *
         * @since 1.0
         * @link https://codex.wordpress.org/AJAX_in_Plugins
         * @return void
         */
        public function ajax_callback(){

            if(isset($_POST['post_id']) && $this->is_valid_feed_id($_POST['post_id'])){

                if($this->http_request_necessary($_POST['post_id']) || isset($_POST['max_id'])){

                    $options_data = $this->get_options_data($_POST['post_id']);
                    $api_data = $this->get_api_data($_POST['post_id']);
                    $connection = new TwitterOAuth($api_data["api_key"], $api_data["api_secret"], $api_data["access_token"], $api_data["access_token_secret"]);

                    if(is_numeric($options_data["number_of_tweets"]) && $options_data["number_of_tweets"]>0){
                        if(isset($_POST['max_id'])){
                            $content = $connection->get("statuses/user_timeline",["count" => $options_data["number_of_tweets"]+1, "exclude_replies" => true,"max_id" => $_POST['max_id']]);
                        }else{
                            $content = $connection->get("statuses/user_timeline",["count" => $options_data["number_of_tweets"], "exclude_replies" => true]);
                            if(!isset($content->errors)){
                                update_post_meta($_POST['post_id'], '_atf_cache', $content);
                                update_post_meta($_POST['post_id'], '_atf_cache_timestamp', strtotime(date('Y-m-d h:i:s')));
                            }
                        }
                        if(isset($content->errors)){
                            if($this->error_exists(88,$content->errors)){
                                echo $this->get_from_cache($_POST['post_id'],true);
                            }else{
                                foreach($content->errors as $error){
                                    echo '<div class="atf-error">ATF ERROR '.$error->code.': '.$error->message.'</div>';
                                }
                            }

                        }else{
                            $response_data = array(
                                'atf_options' => $this->get_options_data($_POST['post_id']),
                                'twitter_response' => $content,
                                'cache' => false
                            );
                            echo json_encode($response_data);
                        }
                    }else{
                        echo '<div class="atf-error">'.__('ATF ERROR: Number of tweets option is not valid. Enter a number greater than 0','awesome-twitter-feeds').'</div>';
                    }
                }else{
                    echo $this->get_from_cache($_POST['post_id']);
                }

            }else{
                echo '<div class="atf-error">'.__('ATF ERROR: The widget does not exists','awesome-twitter-feeds').'</div>';
            }
            die();
        }


        /**
         * Returns true if error exists (twitter api)
         *
         * @since 1.0
         * @return bool
         */
        private function error_exists($error_code,$errors){
            foreach($errors as $error){
                if($error->code == $error_code){
                    return true;
                }
            }
            return false;
        }


        /**
         * Returns true if is valid atfeeds
         *
         * @since 1.0
         * @return bool
         */
        private function is_valid_feed_id($id){
            if(get_post_status($_POST['post_id'])==='publish' && get_post_type($_POST['post_id']) === 'atfeeds'){
                return true;
            }else{
                return false;
            }
        }


        /**
         * Returns cached json
         *
         * @since 1.0
         * @return string|bool
         */
        private function get_from_cache($post_id,$is_error = false){
            $result = false;
            $atf_cache = get_post_meta($_POST['post_id'], '_atf_cache', true );
            if($atf_cache!=false){

                $response_data = array(
                    'atf_options' => $this->get_options_data($_POST['post_id']),
                    'twitter_response' => $atf_cache,
                    'cache' => true,
                    'is_error' => false
                );
                if($is_error){
                    $response_data['is_error'] = true;
                }
                $result = json_encode($response_data);

            }else{
                $result = '<div class="atf-error">ATF ERROR: Caché error</div>';
            }

            return $result;

        }


        /**
         * Returns true if is necessary to do the request
         *
         * Used for cache
         *
         * @since 1.0
         * @return bool
         */
        private function http_request_necessary($post_id){
            $result = true;
            $last_cache_update = get_post_meta($_POST['post_id'], '_atf_cache_timestamp', true );
            if($last_cache_update!=''){
                $seconds_diff = strtotime(date('Y-m-d h:i:s'))-$last_cache_update;
                if($seconds_diff<self::$cache_seconds){
                    $result = false;
                }
            }
            return $result;
        }


        /**
         * Registers and enqueues scripts and styles
         *
         * NOTE: For public access
         *
         * @since 1.0
         * @return void
         */
        public function register_scripts(){
            wp_register_script('atf_functions', plugins_url('awesome-twitter-feeds/assets/js/functions.js'), array('jquery'), ATF_CURRENT_VERSION, true);
            wp_enqueue_script('atf_functions');
            wp_localize_script('atf_functions','ajax_object',array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'translations' => array(
                    'like' => __('Like','awesome-twitter-feeds'),
                    'share' => __('Share','awesome-twitter-feeds'),
                    'reply' => __('Reply','awesome-twitter-feeds'),
                    'follow' => __('Follow','awesome-twitter-feeds'),
                    'more' => __('More','awesome-twitter-feeds'),
                    'load_more' => __('Load more','awesome-twitter-feeds')
                )
            ));

            wp_register_style('atf_theme_light', plugins_url('awesome-twitter-feeds/assets/css/themes/light.css'), array(), ATF_CURRENT_VERSION);
            wp_register_style('atf_theme_dark', plugins_url('awesome-twitter-feeds/assets/css/themes/dark.css'), array(), ATF_CURRENT_VERSION);
            wp_register_style('atf_theme_cake', plugins_url('awesome-twitter-feeds/assets/css/themes/cake.css'), array(), ATF_CURRENT_VERSION);

            //enqueue only used themes
            foreach($this->widget_instance->get_available_widgets() as $atfeed){
                $theme = get_post_meta($atfeed['post_id'], '_atf_theme', true );
                $this->load_css_theme($theme);
            }
        }


        /**
         * Enqueues a specific theme stylesheet
         *
         * @since 1.0
         * @return void
         */
        public function load_css_theme($theme){

            switch($theme){
                case 'light':
                    wp_enqueue_style("atf_theme_light");
                    break;
                case 'dark':
                    wp_enqueue_style("atf_theme_dark");
                    break;
                case 'cake':
                    wp_enqueue_style("atf_theme_cake");
                    break;
                case 'naked':
                    break;
            }
        }


        /**
         * Registers custom post type "atfeeds"
         *
         * @since 1.0
         * @return void
         */
        public function register_post_type(){
            $args = array(
                'labels' =>  array(
                    'name' => __('ATFeeds','awesome-twitter-feeds'),
                    'singular_name' => __('ATFeeds','awesome-twitter-feeds'),
                    'add_new' => __('Add new','awesome-twitter-feeds'),
                    'add_new_item' => __('Add new feed','awesome-twitter-feeds'),
                    'edit_item' => __('Edit feed','awesome-twitter-feeds'),
                    'new_item' => __('New feed','awesome-twitter-feeds'),
                    'view_item' => __('View feeds','awesome-twitter-feeds'),
                    'search_items' => __('Search','awesome-twitter-feeds'),
                    'not_found' => __('No feeds found','awesome-twitter-feeds'),
                    'not_found_in_trash' => __('No feeds found in trash','awesome-twitter-feeds'),
                    'all_items' => __('All feeds','awesome-twitter-feeds')
                ),
                'description' => 'Awesome Twitter Feeds',
                'public' => true,
                'exclude_from_search' => true,
                'publicly_queryable' => false,
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true,
                'show_in_admin_bar' => false,
                'menu_position' => 5,
                'menu_icon' => 'dashicons-twitter',
                'supports' => array('title'),
                'query_var' => false,
                'rewrite' => 'slug'
            );
            register_post_type('atfeeds',$args);
        }


        /**
         * Registers shortcode
         *
         * USAGE: [awesome-twitter-feeds id="10" title="My widget"]
         *
         * @since 1.0
         * @return mixed
         */
        public function register_shortcode($atts){
            //[awesome-twitter-feeds id="10" title="My widget"]
            $atts = shortcode_atts( array(
                'id' => '',
                'title' => ''
            ), $atts, 'awesome-twitter-feeds' );


            $instance = array(
                'title' => $atts['title'],
                'show_title' => ($atts['title']!='') ? true : false,
                'widget' => $atts['id']
            );

            ob_start();
            $follow_button = get_post_meta($instance['widget'], '_atf_follow', true );
            ($follow_button=='on') ? $follow_button_class = ' atf-followbtn' : $follow_button_class = '';
            $theme = get_post_meta($instance['widget'], '_atf_theme', true );
            $themeClass = 'atf-'.$theme;
            $height_content = get_post_meta($instance['widget'], '_atf_height', true );
            (!is_numeric($height_content)) ? $height_content = '320' : '';
            ?>
            <section class="atf-wrapper-parent">
                <?php echo ($instance['show_title']=='1') ? '<h3 class="atf-widget-title">'.$instance['title'].'</h3>' : '';?>
                <div class="atf-wrapper atf atf-<?php echo $instance['widget'] . ' ' . $themeClass . $follow_button_class;?>" data-atf-postid="<?php echo $instance['widget'];?>">
                    <div class="atf-content" style="height:<?php echo $height_content;?>px"></div>
                </div>
            </section>
            <?php
            return ob_get_clean();
        }


        /**
         * Maps shortcode (for visual composer plugin)
         *
         * @since 1.0
         * @link https://vc.wpbakery.com/
         * @return mixed
         */
        public function vc_map_shortcode(){
            vc_map(array(
              "name" => __( "ATFeeds", "awesome-twitter-feeds" ),
              "description" => __( "Custom feeds using Twitter API", "awesome-twitter-feeds" ),
              "base" => "awesome-twitter-feeds",
              "class" => "",
              "icon" => plugins_url('/awesome-twitter-feeds/assets/img/icon_twitter.png'),
              "category" =>  "Social",
              "params" => array(
                 array(
                    "type" => "textfield",
                    "holder" => "div",
                    "heading" => __( "Title", "awesome-twitter-feeds" ),
                    "param_name" => "title",
                    "description" => __( "Widget title (leave empty for none)", "awesome-twitter-feeds" )
                 ),
                array(
                  "type"        => "dropdown",
                  "heading" => __( "Feed id", "awesome-twitter-feeds" ),
                  "param_name"  => "id",
                  "admin_label" => true,
                  "value"       => $this->widget_instance->get_available_widgets()
                  )
                )


           ));
        }

    }
