<?php
/*
 * Plugin Name: CP Forms
 * Plugin URI: https://linkedin.com/in/mrsbs
 * Description: Make Magic
 * Version: 1.0
 * Requires at least: 5.7
 * Requires PHP: 8.x
 * Author: Subash
 * Author URI: https://twitter.com/subaasw
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cpforms
 * Domain Path: /languages
**/

if ( ! defined('ABSPATH') ) exit;

class CPforms_Init {

    public static function init(){
        return new self();
    }

    function setup_plugin(){
        $this->define_constraints();
        $this->includes();
    }

    function define($name, $value){
        if ( ! defined( $name )){
            define( $name, $value );
        }
    }

    function includes(){
        require_once CPFORMS_PATH . 'inc/db/class-db-handler.php';
        require_once CPFORMS_PATH . 'inc/class-core.php';
        require_once CPFORMS_PATH . 'inc/class-ajax.php';
    }

    private function define_constraints(){
        $this->define( 'CPFORMS_PATH', plugin_dir_path(__FILE__) );
        $this->define( 'CPFORMS_URL', plugin_dir_url(__FILE__) );
        $this->define( 'CPFORMS_VERSION', '1.0' );
    }
}

function load_cpforms_init(){
    $instance = CPforms_Init::init();
    $instance->setup_plugin();
}

add_action( 'init', 'load_cpforms_init' );
