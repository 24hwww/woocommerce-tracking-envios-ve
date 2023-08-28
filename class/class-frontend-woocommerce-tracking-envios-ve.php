<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Class Class_Frontend_WC_Tracking_Envios_Ve{
    private static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {

        register_activation_hook( __FILE__, function(){

        });
    }

    public static function init() {
        $instance = self::instance();

        add_shortcode('wc_tracking_ve_consulta', [$instance,'wc_tracking_ve_consulta_func']);

    }

    public function wc_tracking_ve_consulta_func(){
        global $post;
        ob_start();
        ?>
        <div class="woocommerce woocommerce-page">
        <form class="form" method="post">
            <fieldset class="form-group form-row">
                <label for="codigo_rastreo">Consultar código:</label>
                <input type="text" id="codigo_rastreo" class="woocommerce-Input woocommerce-Input--text input-text" name="codigo_rastreo" class="form-control"/>
            </fieldset>
            <fieldset class="form-group form-row">
                <button type="submit" class="btn button"><?php echo __('Consultar'); ?></button>
                <span class="spinner"></span>
            </fieldset>
        </form>
        <div class="resultado-consulta"></div>
        </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;        
    }

}
