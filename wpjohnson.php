<?php                            

/*
Plugin Name: WP Johnson
Plugin URI: 
Description: A WordPress plugin that will assist in creating beautiful Johnson boxes
Author: Mark Boomaars, Jan Evensen
Version: 1.0
Author URI: http://www.codingourweb.com/
*/

#-----------------------------------------------------------------
# Determine the current path and load up COW Plugin Framework
#-----------------------------------------------------------------  

define( 'WPJOHNSON_ADMIN_TEXTDOMAIN', 'wpjohnson' );

$plugin_path = dirname(__FILE__). '/';
if ( class_exists( 'COWPluginFramework' ) != true )
    require_once( $plugin_path. 'framework/cow_plugin_framework.php' );   

#-----------------------------------------------------------------
# = COWPluginFramework :: Extend the base class
#-----------------------------------------------------------------   

class WPJohnsonPlugin extends COWPluginFramework 
{    
    var $version = '1.1';
    var $name = 'WPJohnson';
    var $slug = 'wpjohnson';
    
    var $action_links = array(
        array( 
            'title' => 'Instructions', 
            'href' => 'options-general.php?page=wpjohnson&tab=wpj-docs' 
        )
    );
    
    var $plugin_css = array(
        'style', 
    );
    var $admin_css  = array( 

    ); 
    
    var $plugin_js  = array(
       
    );
    var $admin_js   = array(           

    ); 
    
    var $ajax_actions = array( 
        'admin' => array( 
            'activate',
            'wpj_preview_cb'         
        ) 
    );
    
    var $general_settings_key = 'wpj-general-options';
    var $documentation = 'wpj-docs';
    var $plugin_settings_tabs = array();
    
    function __construct() 
    {
        parent::__construct( __FILE__ );
        add_action( 'admin_init', array( &$this, 'register_documentation' ) );                  
    }

    function activate() 
    {           

    }
    
    /**
    * Code that is run upon plugin initialization
    * 
    * - Register custom post types
    * - Register taxonomies
    * - Retrieve all templates   
    */
    function initialize() 
    {   
        load_plugin_textdomain( WPJOHNSON_ADMIN_TEXTDOMAIN, true, $this->plugin_dir_name. '/assets/locale' );                                     
    }
       
    /**
    * Add stuff to the head section of the page
    * Also defines global variables for use in javascript
    * 
    */
    function page_header() 
    {
?>
    <script>
        <?php echo 'var plugin_uri = "'. $this->get_plugin_uri(). '"'; ?>
    </script>
<?php
    }
    
    /**
    * Code that is run upon initialization of the admin back-end
    * 
    * - Register custom metaboxes
    * - Add metaboxes to all custom post types that are not builtin    
    */
    function admin_init()
    {
        // Check if WP version is at least 3.0
        $this->requires_wordpress_version("3.0");
        
        // Add the donate metabox
        $this->register_custom_meta_box( 'Get WP Johnson Pro', 'post', 'getpro_metablock_cb', 'side' );
        $this->register_custom_meta_box( 'Get WP Johnson Pro', 'page', 'getpro_metablock_cb', 'side' );
    }
    
    function admin_menu()
    {
        $this->register_settings_page( 'menu_page', array( 'menu_title' => 'Johnson Boxes', 'callback' => 'wpj_options_cb' ) );     
    }
    
    /* Callbacks */
    
    function getpro_metablock_cb()
    {
        echo $this->render( '../getpro.php' );    
    }
        
    function wpj_preview_cb()
    {
        $content = $_POST['content'];        
        echo do_shortcode($content);
        exit();    
    }
            
    function wpj_options_cb()
    {              
        ?>
        <div class="wrap">
            <?php   echo $this->section_documentation(); ?>
        </div>
        <?php
    }

    function register_documentation() 
    {
        $this->plugin_settings_tabs[$this->documentation] = 'Documentation';        
    }

    function section_documentation() 
    { 
        // Retrieve documentation from some online location
    ?>
        <h2>User Documentation</h2>
        <iframe src="http://johnsonboxes.com/features/user-instructions/" width="100%" height="1000px"></iframe>
    <?php               
    }
}


#-----------------------------------------------------------------
# = Begin
#-----------------------------------------------------------------

$wpj = new WPJohnsonPlugin();

register_activation_hook( __FILE__, array( &$wpj, 'activate' ) );

// Register additional actions
add_action( 'init',         array( &$wpj, 'initialize' ) );
add_action( 'wp_head',      array( &$wpj, 'page_header' ) );
add_action( 'admin_head',   array( &$wpj, 'page_header' ) );
add_action( 'admin_init',   array( &$wpj, 'admin_init' ) );
add_action( 'admin_menu',   array( &$wpj, 'admin_menu' ) );

add_filter( 'widget_text', 'do_shortcode' );
add_filter( 'widget_title', 'do_shortcode' );

function wpj_add_button() 
{
    if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) )
        return;

    if ( get_user_option( 'rich_editing' ) == 'true' ) {
        add_filter( "mce_external_plugins", "wpj_add_plugin" );
        add_filter( 'mce_buttons', 'wpj_register_button' );
    }
}
add_action( 'init', 'wpj_add_button' );

function wpj_add_plugin( $plugin_array ) 
{
    global $wpj;
    
    $plugin_array['johnson'] = $wpj->get_plugin_uri().'/framework/admin/js/johnson.js';
    
    return $plugin_array;
}

function wpj_register_button( $buttons ) 
{
    array_push( $buttons, "johnson" );

    return $buttons;
}

function wpj_admin_notice_pro() 
{
    global $current_user, $pagenow ;
    $user_id = $current_user->ID;
    if ( $pagenow == 'plugins.php' && ! get_user_meta($user_id, 'wpj_ignore_notice_pro') ) {
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="updated" style="position:relative;"><p>';
            printf(__('Thank you for choosing our WP Johnson plugin!<br />You might be interested in learning about the <a target="_blank" href="http://johnsonboxes.com/features/">features</a> of the PRO version. Or maybe you just want a peek at the <a target="_blank" href="http://johnsonboxes.com/comparison/">differences</a>.<br /><span style="position:absolute;right:10px;top:7px;"><a href="admin.php?page=wpjohnson%1$s">Stop Nagging</a></span>'), '&wpj_nag_ignore_pro=0');
            echo "</p></div>";
        }
    }
} 
add_action( 'admin_notices', 'wpj_admin_notice_pro');


function wpj_nag_ignore_pro() 
{
    global $current_user;
    $user_id = $current_user->ID;
    if ( isset($_GET['wpj_nag_ignore_pro']) && '0' == $_GET['wpj_nag_ignore_pro'] ) {
        add_user_meta($user_id, 'wpj_ignore_notice_pro', 'true', true);
    }
}
add_action('admin_init', 'wpj_nag_ignore_pro');

#-----------------------------------------------------------------
# = SHORTCODES  - Used to include shortcodes
#-----------------------------------------------------------------

/**
* The shortcode that makes it all work!
* 
* @param mixed $atts
* @param mixed $content
* @param mixed $code
*/    
function cow_sc_johnson( $atts, $content = null, $code )
{
    global $wpj;

    // Rebuild our attributes in case of AJAX preview
    if ($atts[0]) {
        $new_atts = array();
        $remove = array("\\", "\"", "'", "%20");
        foreach($atts as $k => $v) {
            $temp = explode('=', $v);
            $new_atts[$temp[0]] = str_replace($remove, '', urldecode($temp[1]));    
        }
        $atts = $new_atts;    
    }
    
    extract( shortcode_atts( array( 
        'general_outerspacing' => '15',
        'general_innerspacing' => '15',
        'general_float' => 'left',
        'general_clear' => 'both',
        'general_width' => null,
        'general_link' => null,
        'general_bgcolor' => null,
        'general_bgcolor_to' => null,
        'general_color' => '#222222',
        'general_shadowcolor' => null,
        'general_font' => 'Arial',
        'general_fontsize' => 14,
        'general_lineheight' => 20,
        'general_gradientstyle' => 'diagonal_down'
        
    ), $atts ) );
    
    $style = $import = '';
    
    if (is_array($wpj->general_settings['fonts'])) {
        $import .= "<style>";
        if ( in_array( $general_font, $wpj->general_settings['fonts'] ) )
            $import .= "@import url('http://fonts.googleapis.com/css?family=". str_replace( " ", "+", $general_font ). "&text=1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz%20%27%22');";        
        $import .= "</style>";     
    }
    
    $style .= "padding:{$general_innerspacing}px;";
    $style .= "margin-bottom:{$general_outerspacing}px;";
    
    if ($general_float && $general_float != 'center') {
        $style .= "float:$general_float;";
        if ($general_float == 'left') {
            $style .= "margin-right:{$general_outerspacing}px;";    
        } elseif ($general_float == 'right') {
            $style .= "margin-left:{$general_outerspacing}px;";
        }
    }     
    elseif ($general_float == 'center') {
        $style .= "margin-left:auto;margin-right:auto;";    
    } 
        
    if ($general_width)
        $style .= "width:$general_width"."px;";

    // Content Text
    
    if ($general_color) 
        $style .= 'color:'. $general_color. '!important;';
    
    if ($general_shadowcolor) 
        $style .= 'text-shadow:1px 1px '. $general_shadowcolor. '!important;';   
    
    if ($general_font) {
        $style .= 'font-family:"'. $general_font. '";';
    }
                 
    if ($general_fontsize) 
        $style .= 'font-size:'. $general_fontsize. 'px!important;';
    
    if ($general_lineheight) 
        $style .= 'line-height:'. $general_lineheight. 'px!important;';
     
    // Background   
    if ($general_bgcolor && !$general_bgcolor_to) { 
        $style .= 'background:'.trim($general_bgcolor).';';
    } 
    elseif ($general_bgcolor && $general_bgcolor_to && $general_gradientstyle == 'horizontal') {
        $style .= "background: $general_bgcolor;";
        $style .= "background: -moz-linear-gradient(left, $general_bgcolor 0%, $general_bgcolor_to 100%);";
        $style .= "background: -webkit-gradient(linear, left top, right top, color-stop(0%,$general_bgcolor), color-stop(100%,$general_bgcolor_to));";
        $style .= "background: -webkit-linear-gradient(left, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -o-linear-gradient(left, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -ms-linear-gradient(left, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: linear-gradient(to right, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$general_bgcolor', endColorstr='$general_bgcolor_to',GradientType=1 );";
    }
    elseif ($general_bgcolor && $general_bgcolor_to && $general_gradientstyle == 'vertical') {  // Vertical             
        $style .= "background: $general_bgcolor;"; 
        $style .= "background: -moz-linear-gradient(top, $general_bgcolor 0%, $general_bgcolor_to 100%);";
        $style .= "background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,$general_bgcolor), color-stop(100%,$general_bgcolor_to));";
        $style .= "background: -webkit-linear-gradient(top, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -o-linear-gradient(top, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -ms-linear-gradient(top, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: linear-gradient(to bottom, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$general_bgcolor', endColorstr='$general_bgcolor_to',GradientType=0 );";
    }
    elseif ($general_bgcolor && $general_bgcolor_to && $general_gradientstyle == 'diagonal_down') {
        $style .= "background: $general_bgcolor;";
        $style .= "background: -moz-linear-gradient(-45deg, $general_bgcolor 0%, $general_bgcolor_to 100%);";
        $style .= "background: -webkit-gradient(linear, left top, right bottom, color-stop(0%,$general_bgcolor), color-stop(100%,$general_bgcolor_to));";
        $style .= "background: -webkit-linear-gradient(-45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -o-linear-gradient(-45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -ms-linear-gradient(-45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: linear-gradient(135deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$general_bgcolor', endColorstr='$general_bgcolor_to',GradientType=1 );";    
    }
    elseif ($general_bgcolor && $general_bgcolor_to && $general_gradientstyle == 'diagonal_up') {
        $style .= "background: $general_bgcolor;"; 
        $style .= "background: -moz-linear-gradient(45deg, $general_bgcolor 0%, $general_bgcolor_to 100%);";
        $style .= "background: -webkit-gradient(linear, left bottom, right top, color-stop(0%,$general_bgcolor), color-stop(100%,$general_bgcolor_to));";
        $style .= "background: -webkit-linear-gradient(45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -o-linear-gradient(45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -ms-linear-gradient(45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: linear-gradient(45deg, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$general_bgcolor', endColorstr='$general_bgcolor_to',GradientType=1 );";  
    }
    elseif ($general_bgcolor && $general_bgcolor_to && $general_gradientstyle == 'radial') {
        $style .= "background: $general_bgcolor;";
        $style .= "background: -moz-radial-gradient(center, ellipse cover, $general_bgcolor 0%, $general_bgcolor_to 100%);";
        $style .= "background: -webkit-gradient(radial, center center, 0px, center center, 100%, color-stop(0%,$general_bgcolor), color-stop(100%,$general_bgcolor_to));";
        $style .= "background: -webkit-radial-gradient(center, ellipse cover, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -o-radial-gradient(center, ellipse cover, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: -ms-radial-gradient(center, ellipse cover, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "background: radial-gradient(ellipse at center, $general_bgcolor 0%,$general_bgcolor_to 100%);";
        $style .= "filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='$general_bgcolor', endColorstr='$general_bgcolor_to',GradientType=1 );";   
    }
                                         
    $result = "$import<div class='cow_johnson' style='$style'>";

    $result .= do_shortcode($content);    

    // Link
    if ($general_link)
        $result .= '<a href="'. $general_link. '" target="_blank"><span></span></a>';

    $result .= '</div>';
    
    if ($general_clear)
        $result .= "<div style=\"clear:$general_clear;\"></div>";

    return stripslashes($result);    
}
add_shortcode( 'cow_johnson', 'cow_sc_johnson' );
add_shortcode( 'cow_johnson', 'cow_sc_johnson' );