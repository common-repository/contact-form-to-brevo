<?php
namespace CFB_PSD\Base;

class CFB_PSD_Integration{

    public function __construct(){

    }
    public function register(){
    
        /**
         * check for contact form 7
         */
        add_action('init', array($this,'cfb_psd_plugin_dependencies'));
        add_action( 'admin_enqueue_scripts',array($this,'cfb_psd_register_backend_assets') );
        add_action('init', array(&$this,'init'));
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array($this,'cfb_psd_pro_plugin_action_links') );
    }
    public function init(){
        load_plugin_textdomain('contactform-to-brevo', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    public function cfb_psd_plugin_dependencies() {

        if (!class_exists('WPCF7')) {
            add_action('admin_notices',  array($this, 'cf7s_admin_notices'));
        }
    }

    //Registering of backend js and css
    public function cfb_psd_register_backend_assets() {
        wp_enqueue_script( 'cfb-psd-admin-js', CFB_PSD_URL.'assets/admin.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'cfb-psd-admin-css', CFB_PSD_URL.'assets/admin.css', array(), '1.0', false);   
    }

    public function cf7s_admin_notices() {
        $class = 'notice notice-error';
        $message = __('Contact Form to Brevo requires Contact form 7 to be installed and active.', 'contactform-to-brevo');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }

    function cfb_psd_pro_plugin_action_links( $links ) {
     
        $links[] = '<a href="https://www.linkedin.com/in/sagar-giri-5bb771130/" target="_blank" style="color:#05c305; font-weight:bold;">'.esc_html__('Go Pro','contactform-to-brevo').'</a>';
        return $links;
    }
}

