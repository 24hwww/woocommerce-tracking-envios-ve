<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Class Class_Backend_WC_Tracking_Envios_Ve{
    private static $_instance = null;
    public $id_wc_setting = '';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
    $this->id_wc_setting = 'shipping';
    register_activation_hook( __FILE__, function(){

    });
    }

    public static function init() {
    $instance = self::instance();

    add_filter("woocommerce_get_sections_{$instance->id_wc_setting}" , [$instance,'section_active_wc_tracking_envios_ve_func']);

    add_action( "woocommerce_get_settings_{$instance->id_wc_setting}", [$instance,'section_setting_wc_tracking_envios_ve_func'],10,2);

    add_action( 'add_meta_boxes', [$instance,'add_shop_order_meta_boxes_func']);

    }

    public function section_active_wc_tracking_envios_ve_func($settings_tab){
        $settings_tab[WC_ENVIOS_VE_DOMAIN] = __( WC_ENVIOS_VE_TITLE );
        return $settings_tab;
    }

    public function section_setting_wc_tracking_envios_ve_func($settings, $current_section){
        $plugin_settings = [];
        if($current_section !== WC_ENVIOS_VE_DOMAIN){
            return $settings;
        }

        $opciones_empresas = array_column(WC_ENVIOS_VE_EMPRESAS, 'name', 'id');

        $plugin_settings =  array(

			array(
				'name' => __( WC_ENVIOS_VE_TITLE ),
				'type' => 'title',
				'desc' => __( 'Permite a tus clientes rastrear sus compras cuando realices el envio por zoom, tealca, domesa, entre otros. Podras insertar en cada orden de pedido de tus cliente el código de seguimiento o tracking de empresas de encomiendas Venezolanas.' ),
				'id'   => WC_ENVIOS_VE_DOMAIN 
			),

			array(
				'name' => __( 'Activar funcionamiento' ),
				'type' => 'checkbox',
                'desc_tip' => __( 'Dentro de cada orden o pedido se visualiza un cuadro para escribir código de rastreo o tracking y seleccionar la empresa de encomienda.', 'text-domain' ),
				'desc' => __( 'Mostrar cuadro para insertar código.'),
				'id'	=> WC_ENVIOS_VE_DOMAIN .'_enable'
			),

			array(
				'name' => __( 'Empresas' ),
				'type' => 'multiselect',
				'desc' => __( 'Listado de empresas de encomiendas'),
				'desc_tip' => true,
                'class' => 'wc-enhanced-select',
				'id' => WC_ENVIOS_VE_DOMAIN .'_empresas',
				'options' => $opciones_empresas,
                'default' => array_keys($opciones_empresas)
			),

			array(
				'name' => __( 'Mensaje en shortcode' ),
				'type' => 'textarea',
				'desc' => __( 'Mensaje que se visualizará en el momento de consultar el código de rastreo/tracking'),
				'desc_tip' => true,
				'id'	=> WC_ENVIOS_VE_DOMAIN .'_mensaje',
			),

			array( 'type' => 'sectionend', 'id' => WC_ENVIOS_VE_DOMAIN ),
	);

    return $plugin_settings;

    }

    public function funcionamiento_activo(){
        $if_active = false;
        $funcionamiento_activo = get_option(WC_ENVIOS_VE_DOMAIN .'_enable','');
        if($funcionamiento_activo == 'on'){
            $if_active = true;
        }
        return $if_activo;
    }

    public function add_shop_order_meta_boxes_func(){
        $instance = self::instance();

        add_meta_box(WC_ENVIOS_VE_DOMAIN, __(WC_ENVIOS_VE_TITLE,'woocommerce'), array($instance,'add_shop_order_meta_boxes_content_func'), 'shop_order', 'side', 'core' );
    }

    public function find_empresas($empresas_seleccionadas){
        $arreglo = [];
        $opciones_empresas = array_column(WC_ENVIOS_VE_EMPRESAS, 'name', 'id');
        if(is_array(WC_ENVIOS_VE_EMPRESAS) && count(WC_ENVIOS_VE_EMPRESAS) > 0){
            foreach(WC_ENVIOS_VE_EMPRESAS as $key => $val){
                $id_empresa = $val['id'];
                if(in_array($id_empresa,$empresas_seleccionadas)){
                    $arreglo[$id_empresa] = $val;
                }
            }
        }
        return $arreglo;
    }

    private function estados_de_envio(){
        $estados_de_envio = [
            array(
                'id' => WC_ENVIOS_VE_DOMAIN.'_processing',
                'name' => __('Procesando','default'),
            ),
            array(
                'id' => WC_ENVIOS_VE_DOMAIN.'_on_the_way',
                'name' => __('En camino','default'),
            ),  
            array(
                'id' => WC_ENVIOS_VE_DOMAIN.'_delivered',
                'name' => __('Entregado','default'),
            ),                        
        ];
        $estados_de_envio_filters = apply_filters( 'estados_de_envio_encomienda',$estados_de_envio);
        return $estados_de_envio_filters;
    }

    public function add_shop_order_meta_boxes_content_func($post){
        $order = wc_get_order($post->ID);

        $opciones_empresas = array_column(WC_ENVIOS_VE_EMPRESAS, 'name', 'id');

        /* wc_tracking_ve_empresa */
        $empresa = $order->get_meta(WC_ENVIOS_VE_DOMAIN.'_empresa');

        $empresas_seleccionadas = get_option(WC_ENVIOS_VE_DOMAIN .'_empresas','');

        $empresas = count($empresas_seleccionadas) > 0 ? $empresas_seleccionadas : $opciones_empresas;
        

        $options_empresas = array_column($this->find_empresas($empresas), 'name', 'id');
        $options_empresas = array_merge([''=>'-- Seleccionar empresa --'],$options_empresas);

        /* wc_tracking_ve_codigo */
        $codigo = $order->get_meta(WC_ENVIOS_VE_DOMAIN.'_codigo');
        $nonce = wp_create_nonce()/* wc_tracking_ve_nonce */;

        ob_start();

        woocommerce_form_field( 'wc_tracking_ve_empresa', array(
            'type'          => 'select',
            'class'         => array('wc_tracking_ve_empresa form-row-wide'),
            'label'         => __('Empresa'),
            'required'    => false,
            'options'     => $options_empresas,
        ), $empresa);
        
        woocommerce_form_field( 'wc_tracking_ve_codigo', array(
            'type'        => 'text',
            'required'    => false,
            'label'       => 'Código de rastreo/tracking',
            'description' => 'Indique el código del envio.',
        ), $codigo );

        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

}
