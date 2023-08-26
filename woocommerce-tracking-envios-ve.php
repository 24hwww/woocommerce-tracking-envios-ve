<?php
/**
 * Plugin Name: WooCommerce Tracking Envios Venezuela
 * Plugin URI: https://github.com/24hwww/woocommerce-tracking-envios-ve/
 * Description: Permite a tus clientes rastrear sus compras cuando realices el envio por zoom, tealca, domesa, entre otros. Podras insertar en cada orden de pedido de tus cliente el codigo de seguimiento o tracking de empresas de encomiendas Venezolanas.
 * Version: 1.0
 * Author: Leonardo Reyes
 * Author URI: https://github.com/24hwww/
 * Text Domain: woocommerce-tracking-envios-ve
 * Domain Path: /i18n/languages/
 * Requires at least: 6.2
 * Requires PHP: 7.3
 *
 */

defined( 'ABSPATH' ) or die( 'Prohibido acceso directo.' );

define('WC_ENVIOS_VE_BASE', plugin_basename( __FILE__ ));
define('WC_ENVIOS_VE_BASE_SLASH', plugin_dir_path( __FILE__ ));
define('WC_ENVIOS_VE_BASE_URL', plugin_dir_url( __FILE__ ));
define('WC_ENVIOS_VE_BASE_PATH', dirname(__FILE__));
define('WC_ENVIOS_VE_DOMAIN', 'woocommerce-tracking-envios-ve');
define('WC_ENVIOS_VE_TITLE', 'WooCommerce Tracking Envios Venezuela');
define('WC_ENVIOS_VE_VERSION', '1.0');

add_action('admin_init', function(){
	if ( !class_exists( 'WooCommerce' ) ):
		deactivate_plugins( WC_ENVIOS_VE_BASE );
		if ( isset( $_GET['activate'] ) ){
		unset( $_GET['activate'] );
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		add_action( 'admin_notices', function(){
			$class = 'notice notice-error';
			$message = __( 'No se puede activar el plugin: '.WC_ENVIOS_VE_TITLE.', debe estar activado el WooCommerce.' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		});
		return;
		}
	endif;
});

if (!class_exists('Class_Backend_WC_Tracking_Envios_Ve')) {
	require_once WC_ENVIOS_VE_BASE_PATH . '/class/class-backend-woocommerce-tracking-envios-ve.php';
	add_action( 'plugins_loaded', [ 'Class_Backend_WC_Tracking_Envios_Ve', 'init' ]);
}