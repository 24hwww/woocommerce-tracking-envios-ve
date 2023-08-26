<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Class Class_Backend_WC_Tracking_Envios_Ve{
    private static $_instance = null;
    public $id_menu = '';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
    $this->id_menu = '';
    register_activation_hook( __FILE__, function(){

    });
    }

    public static function init() {
    $instance = self::instance();

    add_action( 'init', [$instance,'agregar_cpt_regalos_func']);

    }

    public function agregar_cpt_regalos_func(){

    }

}
