<?php

/**
* Based upon the SanityPluginFramework
* 
* @author:  M. Boomaars
* @version: 1.1
*/

class COWPluginFramework 
{   
    var $file               = '';
    var $plugin             = '';       // Name of the plugin
    var $version            = '1.1';    // Framework version 
    var $name               = '';       // Name of your plugin
    var $slug               = '';       // A slug for your plugin. Heavily used in all sorts of ways
        
    var $data               = array();
    var $ezpp;
    var $nonce;
    
    var $action_links       = array();  // Extra links for plugin details section
    var $options            = array();  
    var $settings           = array();  // Plugin options retrieved from DB
    
    var $admin_css          = array();
    var $admin_js           = array();
    var $plugin_css         = array();
    var $plugin_js          = array();
    
    var $css_uri            = 'css';
    var $js_uri             = 'js';
    var $img_uri            = 'images';
    var $plugin_dir         = '';
    var $plugin_dir_name    = '';

    var $ajax_actions       = array(
        'admin' => array(),
        'plugin' => array()
    );
    
    
    /**
    * Constructor
    * 
    * @param string $here
    * @return COWPluginFramework
    */
    function __construct( $here = __FILE__ ) 
	{
        $this->add_ajax_actions();
        $this->file = basename( $here );
        $this->plugin = plugin_basename( $here );
        
        if ( empty( $this->plugin_dir ) ) {
            $this->plugin_dir = WP_PLUGIN_DIR. '/'. basename( dirname( $here ) );
        }

        $this->plugin_dir_name = basename( dirname( $here ) );    
        $this->css_uri = WP_PLUGIN_URL. '/'. $this->plugin_dir_name. '/assets/css/';
        $this->js_uri = WP_PLUGIN_URL. '/'. $this->plugin_dir_name. '/assets/js/';
        $this->img_uri = WP_PLUGIN_URL. '/'. $this->plugin_dir_name. '/assets/images/';

        $this->settings = get_option( $this->slug. '_settings' );
      
        add_action( 'wp_loaded', array( &$this, 'create_nonce' ) );

        if ( ! empty( $this->admin_css ) || ! empty( $this->admin_js ) ) {
            add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_scripts' ) );
        }
        if ( ! empty( $this->plugin_css ) || ! empty( $this->plugin_js ) ) {
            add_action( 'wp_enqueue_scripts', array( &$this, 'load_plugin_scripts' ) );
        }
        
        add_filter('plugin_action_links', array( &$this, 'register_action_links' ), 10, 2);
    }
    
    
    /**
    *   put your comment there...
    */
    function load_admin_scripts() 
	{
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-tabs');

        foreach( $this->admin_css as $css ) {
            wp_enqueue_style( $css, $this->css_uri. $css. '.css' );
        }
        foreach( $this->admin_js as $js ) {
            wp_enqueue_script( $js, $this->js_uri. $js. '.js' );
        }
    }

    
    /**
    *   put your comment there...
    */
	function load_plugin_scripts() 
	{
		foreach( $this->plugin_css as $css ) {
			wp_enqueue_style( $css, $this->css_uri. $css. '.css' );
		}
		foreach( $this->plugin_js as $js ) {
			wp_enqueue_script( $js, $this->js_uri. $js. '.js', array( 'jquery' ) );
		}
	}

    
	/*
	*	create_nonce()
	*	==============
	*	A security feature that COW presumes you should use. 
	*	Please refer to: http://codex.wordpress.org/WordPress_Nonces
	*/
	function create_nonce() 
	{
		$this->nonce = wp_create_nonce('cow-pfw-nonce');
	}
	
    
	/*
	*	Loops through $this->ajax_actions['admin'] and $this->ajax_actions['plugin'] and
	*	registers ajax actions. This makes the actions available in the client plugin.
	*/
	function add_ajax_actions() 
	{
		if (!empty($this->ajax_actions['admin'])) {
			foreach($this->ajax_actions['admin'] as $action) {
				add_action("wp_ajax_$action", array(&$this, $action));
			}
		}
		if (!empty($this->ajax_actions['plugin'])) {
			foreach($this->ajax_actions['plugin'] as $action) {
				add_action("wp_ajax_nopriv_$action", array(&$this, $action));
			}
		}				
	}

    
    /**
    *    Loads a view from within the /plugin/views folder. Keep in mind
    *    that any data you need should be passed through the $this->data array.
    *    A few examples:
    *
    *            Load /Plugin/views/example.php
    *            $this->render('example');
    *
    *            Load /Plugin/views/subfolder/example.php
    *            $this->render('subfolder/example);
    * 
    * @param string $view Name of the view to be loaded
    */
	function render( $view ) 
	{
		$template_path = $this->plugin_dir. '/views/'. $view;
		ob_start();
		include( $template_path );
		$output = ob_get_clean();
		return $output;
	}
    
    
    /**
    *   Register extra links in the plugin's settings of the plugin page
    * 
    *   @param array $links Array with all links belonging to the current plugin file
    *   @param string $plugin Name of the current plugin file
    */
    function register_action_links( $links, $plugin ) 
    {
        if ( ! empty( $this->action_links )) {
            if ( $this->plugin_dir_name. '/'. $this->file == $plugin ) {           
                foreach( $this->action_links as $link => $props ) {               
                    if ( isset( $props['href'] ) && substr( $props['href'], 0, 4 ) == 'http' ) {
                        $links[] = '<a href="'. $props['href']. '" target="_blank">'. $props['title']. '</a>';    
                    } else {
                        $links[] = '<a href="' . admin_url( $props['href'] ) . '">'. $props['title']. '</a>';     
                    }                                   
                } 
            }   
        }

        return $links;
    }

    
    function register_post_type( $name, $label, $args )
    {
        $name = ucfirst( $name );
        $label = ucfirst( $label );
        $labels = array(
            "name" => _x("{$label}s", "post type general name"),
            "singular_name" => _x("$label Item", "post type singular name"),
            "add_new" => _x("Add New", "$label item"),
            "add_new_item" => __("Add New $label", "easypage"),
            "edit_item" => __("Edit $label Item", "easypage"),
            "new_item" => __("New $label Item", "easypage"),
            "view_item" => __("View $label Item", "easypage"),
            "search_items" => __("Search {$label}s", "easypage"),
            "not_found" =>  __("Nothing found", "easypage"),
            "not_found_in_trash" => __("Nothing found in Trash", "easypage"),
            "parent_item_colon" => ""
        );
     
        $defaults = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'query_var' => true,
            'menu_icon' => null,
            'has_archive' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail')
        );
         
        $args = wp_parse_args( $args, $defaults );
        
        register_post_type( $name , $args );                    
    }
    

    function register_taxonomy( $name, $label, $post_type, $args = array() )
    {   
        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name' => _x( "{$label}s", 'taxonomy general name' ),
            'singular_name' => _x( $label, 'taxonomy singular name' ),
            'search_items' =>  __( 'Search '. $label. 's' ),
            'all_items' => __( 'All '. $label. 's' ),
            'parent_item' => __( 'Parent '. $label ),
            'parent_item_colon' => __( 'Parent '. $label. ':' ),
            'edit_item' => __( 'Edit '. $label ), 
            'update_item' => __( 'Update '. $label ),
            'add_new_item' => __( 'Add New '. $label ),
            'new_item_name' => __( 'New '. $label. ' Name' ),
            'menu_name' => __( "{$label}s" ),
        );     
        
        $default = array(
            'hierarchical' => false,  
            'labels' => $labels,
            'query_var' => true, 
            'rewrite' => true
        );
        $args = wp_parse_args( $args, $default );
        
        register_taxonomy( $name, $post_type, $args ); 
    }
    
    
    function register_custom_meta_box( $name, $post_type, $cb, $location = 'advanced' )
    {
        add_meta_box( $this->slug . '-'. strtolower( str_replace( ' ', '-', $name ) ), __( $name, $this->slug ), array( $this, $cb ), $post_type, $location );            
    }
   

    /**
    *   Registers a menu page in the WP backend
    *   Valid menu types are:
    * 
    *       - menu_page         : Add a top level menu page
    *       - object_page       : Add a top level menu page in the 'objects' section
    *       - utility_page      : Add a top level menu page in the 'utility' section
    *       - submenu_page      : Add a sub menu page
    *       - management_page   : Add sub menu page to the tools main menu
    *       - options_page      : Add sub menu page to the options main menu 
    *       - theme_page        : Add sub menu page to the themes main menu
    *       - plugins_page      : Add sub menu page to the plugins main menu
    *       - users_page        : Add sub menu page to the Users/Profile main menu
    *       - dashboard_page    : Add sub menu page to the Dashboard main menu
    *       - posts_page        : Add sub menu page to the posts main menu
    *       - media_page        : Add sub menu page to the media main menu
    *       - links_page        : Add sub menu page to the links main menu
    *       - pages_page        : Add sub menu page to the pages main menu
    *       - comments_page     : Add sub menu page to the comments main menu
    * 
    *   @param string $type Type of menu item to create
    *   @param array $args Arguments for creation of menu item
    */
    function register_settings_page( $type = 'options_page', $args = array()  )
    {
        $default = array(
            'parent_slug' => '',
            'page_title' => $this->name,
            'menu_title' => $this->name,
            'capability' => 'manage_options',
            'menu_slug' => $this->slug,
            'callback' => $this->slug. '_settings_page_cb',
            'icon_url' => '',
            'position' => null    
        );
        $args = wp_parse_args( $args, $default );
        
        switch( $type ) {
            case 'menu_page':
                add_menu_page( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], array( $this, $args['callback']), $args['icon_url'], $args['position'] );
                break;
            case 'object_page':
                call_user_func_array( 'add_'. $type, array( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], array( $this, $args['callback']), $args['icon_url']) );
                break;            
            case 'utility_page':
                call_user_func_array( 'add_'. $type, array( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], array( $this, $args['callback']), $args['icon_url']) );
                break;
            case 'submenu_page':
                add_submenu_page( $args['parent_slug'], $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], array( $this, $args['callback'] ) );
                break;
            default:
                call_user_func_array( 'add_'. $type, array( $args['page_title'], $args['menu_title'], $args['capability'], $args['menu_slug'], array( $this, $args['callback'] ) ) );
                break;
        }   
    }  

    
    /**
    * Checks the version of the current WordPress installation and compares
    * this to a given minimum. If the installed version is lower than the
    * minimum the plugin is deactivated and a message is presented
    * 
    * @param mixed $min_version
    */
    function requires_wordpress_version( $min_version = "3.3" ) 
    {
        global $wp_version;
        
        $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '//'. $this->plugin, false );

        if ( version_compare($wp_version, $min_version, "<" ) ) {
            if( is_plugin_active( $this->plugin ) ) {
                deactivate_plugins( $this->plugin );
                wp_die( "'". $plugin_data['Name']. "' requires WordPress $min_version or higher, and has been deactivated! Please upgrade WordPress and try again.<br /><br />Back to <a href='".admin_url()."'>WordPress admin</a>." );
            }
        }
    }

    
    /**
    * put your comment there...
    * 
    */
    function get_plugin_uri()
    {
        return WP_PLUGIN_URL. '/'. $this->plugin_dir_name;
    }
    
        
    /**
    * put your comment there...
    * 
    * @param mixed $group
    * @param mixed $property
    */
    function get_setting( $group, $property )
    {      
        if ( isset( $this->settings[$group][$property] ) ) {
            if ( isset( $this->settings[$group][$property]['value'] ) ) {
                return $this->settings[$group][$property]['value'];    
            } 
            else {
                return $this->settings[$group][$property]['std'];
            }            
        }
        return false;    
    }    
    
    
    /**
    * put your comment there...
    * 
    * @param mixed $content
    * @param mixed $excerpt_length
    */
    function get_excerpt( $content, $excerpt_length ) 
    {
        $content = strip_shortcodes( $content );
        $content = str_replace( ']]>', ']]&gt;', $content );
        $content = strip_tags( $content );
        $words = explode( ' ', $content, $excerpt_length + 1 );
        if ( count( $words ) > $excerpt_length ) :
            array_pop( $words );
            array_push( $words, '...' );
            $content = implode( ' ', $words );
        endif;
        return '<p>' . $content . '</p>';
    }  
    
    
    /**
    * 
    * 
    * @param mixed $src
    * @param mixed $w
    * @param mixed $h
    * @param mixed $title
    * @param mixed $alt
    * @param mixed $a
    */
    function timthumb_image( $src, $w, $h, $title = '', $alt = '', $a = 't', $f = '' ) 
    {         
        if ( stristr( trim( $src ), 'http://' ) != 0 ) {
            $src = get_bloginfo( 'url' ). trim( $src ); 
        }

        $tt_src = $this->get_plugin_uri(). '/framework/timthumb.php'; 
        
        if( is_ssl() ) 
            $tt_src = str_replace( 'http://', 'https://', $tt_src );
    
        $img_src = $tt_src.
                    '?src='. $src.
                    '&amp;w='. $w.
                    '&amp;h='. $h.
                    '&amp;a='. $a.
                    '&amp;f='. $f.
                    '" alt="'. $alt.
                    '" title="'. $title.
                    '"';
        return $img_src;
    }
    
    
    function cow_get_attachment_id_from_src( $image_src )
    {
        $image_src = esc_url( $image_src );
        
        global $wpdb;
        
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var( $query );
        
        return $id;
    } 
    
    /**
    * Excellent Crop Image function adopted from Karma theme on ThemeForest !!!
    * 
    * @param mixed $thumb
    * @param mixed $image_path
    * @param mixed $width
    * @param mixed $height
    */
    function cow_crop_image( $attach_id = null, $image_path = null, $width, $height, $title = '', $crop = 't' ) 
    { 
        // first try, assuming image is internal.
        $image_output = array();
        $image_output = $this->vt_resize( null, $image_path, $width, $height, true );
        $image_src = (string) $image_output['url'];
         
        // second try, if there is no image_src returned from first try, we assume is external
        if ( empty( $image_src ) ) { 
            $extensions = get_loaded_extensions();
            
            if ( ! in_array( 'curl', $extensions ) ) {
                return;
            }  
     
            // check for gd extension, if not installed disable script
            if ( ! in_array( 'gd', $extensions ) ) {
                return;
            }
     
            // construct the timthumb url for image_src    
            if ( is_multisite() ) {    
                if ( ! empty( $image_path ) ) {
                    global $blog_id;
                    
                    if ( isset( $blog_id ) && $blog_id > 0 ) {
                        $imageParts = explode( '/files/', $image_path );
                        if ( isset( $imageParts[1] ) ) {
                            $theImageSrc = '/blogs.dir/' . $blog_id . '/files/' . $imageParts[1];
                        }
                    }      
                     // check whether image is internal, using GD image library's function getimagesize()
                    $size = @getimagesize( $theImageSrc );
                    if ( ! empty( $size ) ) {
                        // internal image.
                        $image_src = $this->timthumb_image($theImageSrc, $width, $height, $title, $title, $crop);
                    } 
                    else {
                        // external image.           
                        $image_src = $this->timthumb_image($image_path, $width, $height, $title, $title, $crop);           
                    }
                }
            } 
            else {
                if ( ! empty( $image_path ) ) {
                    $image_src = $this->timthumb_image($image_path, $width, $height, $title, $title, $crop);
                }
            }
        }

        return $image_src;     
    }

    /*
     * Resize images dynamically using wp built in functions
     * Original script by Victor Teixeira
     * See http://core.trac.wordpress.org/ticket/15311
     * Requires php 5.2+
     *
     * This function is called by cow_crop_image()
     * Do not use this function directly!
     *
     * Example usage:
     * 
     * <?php 
     * $thumb = get_post_thumbnail_id(); 
     * $image = vt_resize( $thumb, '', 140, 110, true );
     * ?>
     * <img src="<?php echo $image[url]; ?>" width="<?php echo $image[width]; ?>" height="<?php echo $image[height]; ?>" />
     *
     * @param int $attach_id
     * @param string $img_url
     * @param int $width
     * @param int $height
     * @param bool $crop
     * @return array
     */     
    function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) 
    {    
        //initialise variable to prevent wp_debug mode error.
        $file_path = '';
        $image_src[0] = '';
        $image_src[1] = '';
        $image_src[2] = '';
            
        if ( $attach_id ) {       
            $image_src = wp_get_attachment_image_src( $attach_id, 'full' );
            $file_path = get_attached_file( $attach_id );
        } else if ( $img_url ) {
            $attachment_id = $this->cow_get_attachment_id_from_src($img_url);
            $image_src_ed = wp_get_attachment_image_src( $attachment_id, 'full' );
            $get_file_path = get_attached_file( $attachment_id );
            $file_path = $get_file_path;

            $orig_size = $image_src_ed;
            
            $image_src[0] = $orig_size[0];
            $image_src[1] = $orig_size[1];
            $image_src[2] = $orig_size[2];                                        
        }
        
        $file_info = pathinfo( $file_path );
        $file_info['extension'] = '';
        $file_info['dirname'] = '';
        $extension = '.'. $file_info['extension'];

        // the image path without the extension
        $no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];
        $cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;

        // checking if the file size is larger than the target size
        // if it is smaller or the same size, stop right here and return
        if ( $image_src[1] > $width || $image_src[2] > $height ) {
            // the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
            if ( file_exists( $cropped_img_path ) ) {
                $cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
                
                $vt_image = array (
                    'url' => $cropped_img_url,
                    'width' => $width,
                    'height' => $height
                );
                
                return $vt_image;
            }

            if ( $crop == false ) {
                // calculate the size proportionaly
                $proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
                $resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;            

                // checking if the file already exists
                if ( file_exists( $resized_img_path ) ) {              
                    $resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );

                    $vt_image = array (
                        'url' => $resized_img_url,
                        'width' => $proportional_size[0],
                        'height' => $proportional_size[1]
                    );
                    
                    return $vt_image;
                }
            }

            // no cache files - let's finally resize it
            $new_img_path = image_resize( $file_path, $width, $height, $crop );
            $new_img_size = getimagesize( $new_img_path );
            $new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );

            // resized output
            $vt_image = array (
                'url' => $new_img,
                'width' => $new_img_size[0],
                'height' => $new_img_size[1]
            );
            
            return $vt_image;
        }

        // default output - without resizing
        $vt_image = array (
            'url' => $image_src[0],
            'width' => $image_src[1],
            'height' => $image_src[2]
        );

        return $vt_image;
    }    
    
}