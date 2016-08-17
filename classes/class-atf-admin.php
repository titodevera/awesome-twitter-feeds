<?php
    /**
     * Plugin class for admin panel
     *
     * @since 1.0
     */

    namespace Awesome_Twitter_Feeds;
    use Avs_Metabox_Wrapper\Avs_Metabox;

    defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

    final class ATF_Admin{

        function __construct(){
            add_action('plugins_loaded', array($this,'register_metaboxes'));
            add_action('save_post_atfeeds',  array($this,'clear_cache'), 10, 3 );
            add_filter('manage_edit-atfeeds_columns', array($this,'post_type_columns'));
            add_action('manage_atfeeds_posts_custom_column', array($this,'post_type_columns_content'),10,2);
            add_filter('post_row_actions', array($this,'remove_quick_edit'), 10, 2 );
            add_action('admin_action_atf_duplicate_widget', array($this,'duplicate_widget'));
            if (in_array($GLOBALS['pagenow'], array('edit.php', 'post.php', 'post-new.php'))){
                add_filter('admin_footer_text', array($this,'custom_footer_text'));
                add_filter('update_footer', array($this,'custom_footer_r_text'), 11);
            }
            add_filter('post_row_actions',  array($this,'duplicate_widget_link'), 10, 2);
            add_action('admin_enqueue_scripts', array($this,'register_admin_scripts'));
        }

        /**
         * Change footer text on post list
         *
         * @since 1.0
         * @link https://developer.wordpress.org/reference/hooks/admin_footer_text/
         * @param string $text
         * @return string
         */
        public function custom_footer_text($text){

            global $current_screen;

            if($current_screen->post_type == 'atfeeds'){
                return sprintf(
                    '<span id="footer-thankyou">%s <a href="https://wordpress.org/support/plugin/awesome-twitter-feeds" target="__blank">%s</a></span>',
                    __('Do you need help?', 'awesome-twitter-feeds'),
                    __('Click me', 'awesome-twitter-feeds')
                );
            }else{
                return $text;
            }
        }

        /**
         * Change footer text (right part) on post list
         *
         * @since 1.0
         * @link https://developer.wordpress.org/reference/hooks/update_footer/
         * @param string $content The content that will be printed.
         * @return string
         */
        public function custom_footer_r_text($content){

            global $current_screen;

            if($current_screen->post_type == 'atfeeds'){
                echo '<p id="footer-upgrade" class="alignright">ATFeeds version '.ATF_CURRENT_VERSION.'</p>';
            }else{
                echo $content;
            }
        }

        /**
         * Clear cache when a feed is saved.
         *
         * @since 1.0
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
         * @param int $post_id The post ID.
         * @param post $post The post object.
         * @param bool $update Whether this is an existing post being updated or not.
         * @return void
         */
        public function clear_cache($post_id, $post, $update){
            update_post_meta($post_id, '_atf_cache_timestamp', 0);
        }


        /**
         * Removes quick edit link
         *
         * For custom post type "atfeeds" only
         *
         * @since 1.0
         * @return array
         */
        public function remove_quick_edit( $actions, $post ){
            global $current_screen;
            if( $current_screen->post_type != 'atfeeds' ) return $actions;
            unset($actions['inline hide-if-no-js']);
            return $actions;
        }


        /**
         * Registers and enqueues scripts and styles
         *
         * NOTE: For admin panel
         *
         * @since 1.0
         * @return void
         */
        public function register_admin_scripts($hook){
            $screen = get_current_screen();
            if ($hook == 'post.php' && $screen->post_type != 'atfeeds') {
                return;
            }
            wp_register_style('atf_styles_admin', plugins_url('awesome-twitter-feeds/assets/css/styles-admin.css'), array(), '1.0');
            wp_enqueue_style('atf_styles_admin');
        }


        /**
         * Custom columns for created custom post type
         *
         * @since 1.0
         * @param array $columns
         * @return array
         */
        public function post_type_columns($columns){
            $columns = array(
                'cb' => '<input type="checkbox" />',
                'id' => __('Id','awesome-twitter-feeds'),
                'title' => __('Title'),
                'theme' => __('Theme','awesome-twitter-feeds'),
                'date' => __('Date')
            );

            return $columns;
        }

        /**
         * Custom columns content for created custom post type
         *
         * @since 1.0
         * @param array $columns
         * @return int|void
         */
        public function post_type_columns_content($column, $post_id){
            global $post;

            switch( $column ) {
                case 'id':
                    echo $post_id;
                    break;
                case 'theme':
                    echo esc_html(get_post_meta($post_id, '_atf_theme', true ));;
                    break;
                default:
                    break;
            }
        }

        /*
         * Function creates post duplicate as a draft and redirects then to the edit post screen
         */
        public function duplicate_widget(){
            global $wpdb;
            if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
                wp_die('No post to duplicate has been supplied!');
            }

            /*
             * get the original post id
             */
            $post_id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
            /*
             * and all the original post data then
             */
            $post = get_post( $post_id );

            /*
             * if you don't want current user to be the new post author,
             * then change next couple of lines to this: $new_post_author = $post->post_author;
             */
            $current_user = wp_get_current_user();
            $new_post_author = $current_user->ID;

            /*
             * if post data exists, create the post duplicate
             */
            if (isset( $post ) && $post != null) {

                /*
                 * new post data array
                 */
                $args = array(
                    'comment_status' => $post->comment_status,
                    'ping_status'    => $post->ping_status,
                    'post_author'    => $new_post_author,
                    'post_content'   => $post->post_content,
                    'post_excerpt'   => $post->post_excerpt,
                    'post_name'      => $post->post_name,
                    'post_parent'    => $post->post_parent,
                    'post_password'  => $post->post_password,
                    'post_status'    => 'draft',
                    'post_title'     => $post->post_title.' (copy)',
                    'post_type'      => $post->post_type,
                    'to_ping'        => $post->to_ping,
                    'menu_order'     => $post->menu_order
                );

                /*
                 * insert the post by wp_insert_post() function
                 */
                $new_post_id = wp_insert_post( $args );

                /*
                 * get all current post terms ad set them to the new post draft
                 */
                $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
                foreach ($taxonomies as $taxonomy) {
                    $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
                    wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
                }

                /*
                 * duplicate all post meta just in two SQL queries
                 */
                $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
                if (count($post_meta_infos)!=0) {
                    $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
                    foreach ($post_meta_infos as $meta_info) {
                        $meta_key = $meta_info->meta_key;
                        $meta_value = addslashes($meta_info->meta_value);
                        $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
                    }
                    $sql_query.= implode(" UNION ALL ", $sql_query_sel);
                    $wpdb->query($sql_query);
                }


                /*
                 * finally, redirect to the edit post screen for the new draft
                 */
                wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
                exit;
            } else {
                wp_die('Post creation failed, could not find original post: ' . $post_id);
            }
        }

        /*
         * Add the duplicate link to action list for post_row_actions
         */
        public function duplicate_widget_link( $actions, $post ) {
            if ($post->post_type=='atfeeds' && current_user_can('edit_posts')) {
                $actions['duplicate'] = '<a href="admin.php?action=atf_duplicate_widget&amp;post=' . $post->ID . '" title="'.__('Duplicate this item','awesome-twitter-feeds').'" rel="permalink">'.__('Duplicate','awesome-twitter-feeds').'</a>';
            }
            return $actions;
        }


        /**
         * Registers metaboxes and metafield for custom post type "atfeeds"
         *
         * @since 1.0
         * @link https://github.com/titodevera/avs-metaboxes
         * @return void
         */
        public function register_metaboxes(){

            $prefix = '_atf_';

            // Twitter api access configuration
            $avs_metabox = new Avs_Metabox(
              'atf_metabox',
              __('API config','awesome-twitter-feeds'),
              array('atfeeds')
            );
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'account',
              'label' 				=> __('Twitter account name','awesome-twitter-feeds'),
              'desc' 					=> __('Twitter account without the at','awesome-twitter-feeds'),
              'col_width' 		=> 'col4'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'api_key',
              'label' 				=> __('API Key','awesome-twitter-feeds'),
              'desc' 					=> __('Twitter API Key','awesome-twitter-feeds'),
              'col_width' 		=> 'col4'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'api_secret',
              'label' 				=> __('API Secret','awesome-twitter-feeds'),
              'desc' 					=> __('Twitter API Secret','awesome-twitter-feeds'),
              'col_width' 		=> 'col4',
              'clear_after' 	=> true
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'access_token',
              'label' 				=> __('Access token','awesome-twitter-feeds'),
              'desc' 					=> __('Twitter access token','awesome-twitter-feeds'),
              'col_width' 		=> 'col4'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'access_token_secret',
              'label' 				=> __('Access token secret','awesome-twitter-feeds'),
              'desc' 					=> __('Twitter access token secret','awesome-twitter-feeds'),
              'col_width' 		=> 'col4',
              'clear_after' 	=> true
            ));

            // Other options
            $avs_metabox = new Avs_Metabox(
              'atf_metabox_options',
              __('ATF Options','awesome-twitter-feeds'),
              array('atfeeds')
            );
            $avs_metabox->add_field(array(
              'type'					=> 'select',
              'id' 						=> $prefix . 'theme',
              'label' 				=> __('Theme','awesome-twitter-feeds'),
              'desc' 					=> __('Select a theme','awesome-twitter-feeds'),
              'col_width' 		=> 'col4',
              'options'          => array(
                  'Light' => 'light',
                  'Dark'  => 'dark',
                  'Cake'  => 'cake',
                  'Naked ¡style it!' => 'naked'
              )
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'number_of_tweets',
              'label' 				=> __('Number of tweets','awesome-twitter-feeds'),
              'desc' 					=> __('Number of tweets which must be shown','awesome-twitter-feeds'),
              'col_width' 		=> 'col4',
              'default'       => '10'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'text',
              'id' 						=> $prefix . 'height',
              'label' 				=> __('Feed height','awesome-twitter-feeds'),
              'desc' 					=> __('Height in pixels Ej.: 320','awesome-twitter-feeds'),
              'col_width' 		=> 'col4',
              'default'       => '320',
              'clear_after' 	=> true
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'checkbox',
              'id' 						=> $prefix . 'profile_img',
              'label' 				=> __('Show profile image','awesome-twitter-feeds'),
              'desc' 					=> __('Show profile image for each tweet','awesome-twitter-feeds'),
              'col_width' 		=> 'col6'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'checkbox',
              'id' 						=> $prefix . 'follow',
              'label' 				=> __('Show follow button','awesome-twitter-feeds'),
              'desc' 					=> __('Show follow button','awesome-twitter-feeds'),
              'col_width' 		=> 'col6',
              'clear_after' 	=> true
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'checkbox',
              'id' 						=> $prefix . 'media',
              'label' 				=> __('Show media content','awesome-twitter-feeds'),
              'desc' 					=> __('Show media content for each tweet','awesome-twitter-feeds'),
              'col_width' 		=> 'col6'
            ));
            $avs_metabox->add_field(array(
              'type'					=> 'checkbox',
              'id' 						=> $prefix . 'links',
              'label' 				=> __('Show urls as links','awesome-twitter-feeds'),
              'desc' 					=> __('Show urls, mentions and hashtags as links','awesome-twitter-feeds'),
              'col_width' 		=> 'col6',
              'clear_after' 	=> true
            ));

        }

    }
