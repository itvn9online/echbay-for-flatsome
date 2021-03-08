<?php
/**
 * Plugin Name: EchBay For Flatsome
 * Description: Plugin này nhằm hỗ trợ các bạn sử dụng theme của EchBay.com trên nền tảng Flatsome có thể tùy biến thêm một số vấn đề liên quan đến HTML một cách nhanh chóng, dễ dàng...
 * Plugin URI: https://www.facebook.com/groups/wordpresseb
 * Plugin Facebook page: https://www.facebook.com/webgiare.org
 * Author: Dao Quoc Dai
 * Author URI: https://www.facebook.com/ech.bay/
 * Version: 1.0.0
 * Text Domain: webgiareorg
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit();
}

define( 'EFF_DF_VERSION', '1.0.0' );
// echo EFF_DF_VERSION . "\n";

// define( 'EFF_DF_MAIN_FILE', __FILE__ );
// echo EFF_DF_MAIN_FILE . "\n";

define( 'EFF_DF_DIR', __DIR__ . '/' );
// echo EFF_DF_DIR . "\n";

// define( 'EFF_DF_TEXT_DOMAIN', 'echbayefm' );
// echo EFF_DF_TEXT_DOMAIN . "\n";

//define ( 'EFF_DF_ROOT_DIR', basename ( EFF_DF_DIR ) );
// echo EFF_DF_ROOT_DIR . "\n";

//define ( 'EFF_DF_NONCE', EFF_DF_ROOT_DIR . EFF_DF_VERSION );
// echo EFF_DF_NONCE . "\n";

//define ( 'EFF_DF_URL', plugins_url () . '/' . EFF_DF_ROOT_DIR . '/' );
// echo EFF_DF_URL . "\n";

//define ( 'EFF_DF_PREFIX_OPTIONS', '___EFF___' );
// echo EFF_DF_PREFIX_OPTIONS . "\n";

define( 'EFF_THIS_PLUGIN_NAME', 'EchBay For Flatsome' );
// echo EFF_THIS_PLUGIN_NAME . "\n";


// global echbay plugins menu name
// check if not exist -> add new
if ( !defined( 'EBP_GLOBAL_PLUGINS_SLUG_NAME' ) ) {
    define( 'EBP_GLOBAL_PLUGINS_SLUG_NAME', 'echbay-plugins-menu' );
    define( 'EBP_GLOBAL_PLUGINS_MENU_NAME', 'Webgiare Plugins' );

    define( 'EFF_ADD_TO_SUB_MENU', false );
}
// exist -> add sub-menu
else {
    define( 'EFF_ADD_TO_SUB_MENU', true );
}


/*
 * class.php
 */
// check class exist
if ( !class_exists( 'EFF_Actions_Module' ) ) {

    // my class
    class EFF_Actions_Module {

        /*
         * config
         */
        var $default_setting = array(
            'find_and_replace' => '',
        );

        var $custom_setting = array();

        var $eb_plugin_media_version = EFF_DF_VERSION;

        var $eb_plugin_prefix_option = '___EFF___';

        var $eb_plugin_root_dir = '';

        var $gio_server = 0;

        var $eb_plugin_url = '';

        var $eb_plugin_nonce = '';

        var $eb_plugin_admin_dir = 'wp-admin';

        var $web_link = '';


        /*
         * begin
         */
        function load() {

            /*
             * test in localhost
             */
            /*
            if ( $_SERVER['HTTP_HOST'] == 'localhost:8888' ) {
            	$this->eb_plugin_media_version = $this->gio_server;
            }
            */


            /*
             * Check and set config value
             */
            // root dir
            $this->eb_plugin_root_dir = basename( EFF_DF_DIR );

            // Get version by time file modife
            $this->eb_plugin_media_version = filemtime( EFF_DF_DIR . 'style.css' );

            // URL to this plugin
            //$this->eb_plugin_url = plugins_url () . '/' . EFF_DF_ROOT_DIR . '/';
            $this->eb_plugin_url = plugins_url() . '/' . $this->eb_plugin_root_dir . '/';

            // nonce for echbay plugin
            //$this->eb_plugin_nonce = EFF_DF_ROOT_DIR . EFF_DF_VERSION;
            $this->eb_plugin_nonce = $this->eb_plugin_root_dir . EFF_DF_VERSION;

            //
            if ( defined( 'WP_ADMIN_DIR' ) ) {
                $this->eb_plugin_admin_dir = WP_ADMIN_DIR;
            }

            //
            $this->gio_server = current_time( 'timestamp' );
            /*
            if ( $this->gio_server != time() ) {
            	$tz = get_option('timezone_string');
            	if ( $tz != '' ) {
            		$this->gio_server = time();
            	}
            }
            */


            /*
             * Load custom value
             */
            $this->get_op();
        }

        // get options
        function get_op() {
            global $wpdb;

            //
            $pref = $this->eb_plugin_prefix_option;

            $sql = $wpdb->get_results( "SELECT option_name, option_value
FROM
	`" . $wpdb->options . "`
WHERE
	option_name LIKE '{$pref}%'
ORDER BY
	option_id", OBJECT );

            foreach ( $sql as $v ) {
                $this->custom_setting[ str_replace( $this->eb_plugin_prefix_option, '', $v->option_name ) ] = $v->option_value;
            }
            //print_r( $this->custom_setting );
            //exit();


            /*
             * https://codex.wordpress.org/Validating_Sanitizing_and_Escaping_User_Data
             */
            // set default value if not exist or NULL
            foreach ( $this->default_setting as $k => $v ) {
                if ( !isset( $this->custom_setting[ $k ] ) ||
                    $this->custom_setting[ $k ] == ''
                    //	|| $this->custom_setting [$k] == 0
                    ||
                    $this->custom_setting[ $k ] == '0' ) {
                    $this->custom_setting[ $k ] = $v;
                }
            }

            // esc_ custom value
            foreach ( $this->custom_setting as $k => $v ) {
                //	if ( $k == 'custom_style' || $k == 'widget_title' ) {
                if ( $k == 'custom_style' ) {
                    $v = esc_textarea( $v );
                    /*
	}
	else if ( $k == 'widget_title' ) {
		$v = htmlentities( $v, ENT_QUOTES, "UTF-8" );
		*/
                } else {
                    $v = esc_html( $v );
                }
                $this->custom_setting[ $k ] = $v;
            }

            //print_r( $this->custom_setting ); exit();
        }

        // add checked or selected to input
        function ck( $v1, $v2, $e = ' checked' ) {
            if ( $v1 == $v2 ) {
                return $e;
            }
            return '';
        }

        function get_web_link() {
            if ( $this->web_link != '' ) {
                return $this->web_link;
            }

            //
            /*
            if ( defined('WP_SITEURL') ) {
            	$this->web_link = WP_SITEURL;
            }
            else if ( defined('WP_HOME') ) {
            	$this->web_link = WP_HOME;
            }
            else {
            	*/
            $this->web_link = get_option( 'siteurl' );
            //}

            //
            $this->web_link = explode( '/', $this->web_link );
            //print_r( $this->web_link );

            $this->web_link[ 2 ] = $_SERVER[ 'HTTP_HOST' ];
            //print_r( $this->web_link );

            // ->
            $this->web_link = implode( '/', $this->web_link );

            //
            if ( substr( $this->web_link, -1 ) == '/' ) {
                $this->web_link = substr( $this->web_link, 0, -1 );
            }
            //echo $this->web_link; exit();

            //
            return $this->web_link;
        }

        // update custom setting
        function update() {
            if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ '_ebnonce' ] ) ) {

                // check nonce
                if ( !wp_verify_nonce( $_POST[ '_ebnonce' ], $this->eb_plugin_nonce ) ) {
                    wp_die( '404 not found!' );
                }


                // print_r( $_POST );

                //
                foreach ( $_POST as $k => $v ) {
                    // only update field by efm
                    if ( substr( $k, 0, 5 ) == '_eff_' ) {

                        // add prefix key to option key
                        $key = $this->eb_plugin_prefix_option . substr( $k, 5 );
                        // echo $k . "\n";

                        //
                        delete_option( $key );

                        //
                        $v = stripslashes( stripslashes( stripslashes( $v ) ) );

                        //
                        $v = esc_html( $v );
                        $v = sanitize_text_field( $v );

                        //
                        add_option( $key, $v, '', 'no' );
                        //add_option ( $key, $v );
                    }
                }

                //
                die( '<script type="text/javascript">
// window.location = window.location.href;
try {
	if ( top != self && typeof top.a_lert == "function" ) {
		top.a_lert("Update done!");
	}
	else {
		alert("Update done!");
	}
} catch (e) {
	alert("Update done!");
}
</script>' );

                //
                // wp_redirect( '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

                //
                // exit();
            } // end if POST
        }

        // form admin
        function admin() {
            // admin -> used real time version
            $this->eb_plugin_media_version = $this->gio_server;
            $this->get_web_link();

            //
            $main = file_get_contents( EFF_DF_DIR . 'admin.html', 1 );

            $main = $this->template( $main, $this->custom_setting + array(
                '_ebnonce' => wp_create_nonce( $this->eb_plugin_nonce ),

                '___EFF___' => $this->eb_plugin_prefix_option,

                'eff_plugin_url' => $this->eb_plugin_url,
                'eff_plugin_version' => $this->eb_plugin_media_version,
            ) );

            //
            $this->default_setting[ 'find_and_replace' ] = esc_html( $this->default_setting[ 'find_and_replace' ] );

            $main = $this->template( $main, $this->default_setting, 'aaa' );

            echo $main;

            echo '<p>* Other <a href="' . $this->web_link . '/' . $this->eb_plugin_admin_dir . '/plugin-install.php?s=itvn9online&tab=search&type=author" target="_blank">WordPress Plugins</a> written by the same author. Thanks for choose us!</p>';

        }

        function deline( $str, $reg = "/\r\n|\n\r|\n|\r|\t/i", $re = "" ) {
            // v2
            $a = explode( "\n", $str );
            $str = '';
            foreach ( $a as $v ) {
                $v = trim( $v );
                if ( $v != '' ) {
                    if ( strstr( $v, '//' ) == true ) {
                        $v .= "\n";
                    }
                    $str .= $v;
                }
            }
            return $str;
        }

        // get html for theme
        function guest() {}

        // add value to template file
        function template( $temp, $val = array(), $tmp = 'tmp' ) {
            foreach ( $val as $k => $v ) {
                $temp = str_replace( '{' . $tmp . '.' . $k . '}', $v, $temp );
            }

            return $temp;
        }
    } // end my class
} // end check class exist


/*
 * Show in admin
 */
function EFF_show_setting_form_in_admin() {
    global $EFF_func;

    $EFF_func->update();

    $EFF_func->admin();
}

function EFF_add_menu_setting_to_admin_menu() {
    // only show menu if administrator login
    if ( !current_user_can( 'manage_options' ) ) {
        return false;
    }

    // menu name
    $a = EFF_THIS_PLUGIN_NAME;

    // add main menu
    if ( EFF_ADD_TO_SUB_MENU == false ) {
        add_menu_page( $a, EBP_GLOBAL_PLUGINS_MENU_NAME, 'manage_options', EBP_GLOBAL_PLUGINS_SLUG_NAME, 'EFF_show_setting_form_in_admin', NULL, 99 );
    }

    // add sub-menu
    add_submenu_page( EBP_GLOBAL_PLUGINS_SLUG_NAME, $a, trim( str_replace( 'EchBay', '', $a ) ), 'manage_options', strtolower( str_replace( ' ', '-', $a ) ), 'EFF_show_setting_form_in_admin' );
}


// Add settings link on plugin page
function EFF_plugin_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=' . strtolower( str_replace( ' ', '-', EFF_THIS_PLUGIN_NAME ) ) . '">Settings</a>';
    array_unshift( $links, $settings_link );
    return $links;
}


/*
 * replace content in echbay theme on flatsome
 */
function EFF_replace_to_echbay_content( $str, $arr = '' ) {
    // create find and replace content
    $arr .= "\n" . get_option( '___EFF___find_and_replace' );
    $arr = trim( $arr );
    $arr = explode( "\n", $arr );

    // bắt đầu thay
    foreach ( $arr as $v ) {
        $v = trim( $v );
        if ( $v != '' && substr( $v, 0, 1 ) != '#' ) {
            $v = explode( '|', $v );

            //
            $str = str_replace( trim( $v[ 0 ] ), trim( $v[ 1 ] ), $str );
        }
    }

    //
    return $str;
}

function EFF_replace_for_echbay_tmp( $str, $custom_arr = [] ) {
    // custom first
    foreach ( $custom_arr as $k => $v ) {
        $str = str_replace( '{tmp.' . $k . '}', $v, $str );
    }

    // default
    $arr = [
        'global-footer-copyright' => '<div class="global-footer-copyright">Bản quyền &copy; ' . date( 'Y' ) . ' <span>' . get_option( 'blogname' ) . '</span> - Toàn bộ phiên bản. <span class="powered-by-echbay">Cung cấp bởi <a href="https://echbay.com/" title="Cung cấp bởi ẾchBay.com - Thiết kế web chuyên nghiệp" target="_blank" rel="nofollow">EchBay.com</a></span></div>',
    ];
    foreach ( $arr as $k => $v ) {
        $str = str_replace( '{tmp.' . $k . '}', $v, $str );
    }

    //
    return $str;
}


// end class.php


//
$EFF_func = new EFF_Actions_Module();

// load custom value in database
$EFF_func->load();

// check and call function for admin
if ( is_admin() ) {
    add_action( 'admin_menu', 'EFF_add_menu_setting_to_admin_menu' );


    // Add menu setting to plugins page
    if ( strstr( $_SERVER[ 'REQUEST_URI' ], 'plugins.php' ) == true ) {
        $plugin = plugin_basename( __FILE__ );
        add_filter( "plugin_action_links_$plugin", 'EFF_plugin_settings_link' );
    }
}