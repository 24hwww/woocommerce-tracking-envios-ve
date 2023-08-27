<?php
if ( ! defined( 'ABSPATH' ) ) exit;

Class Class_Backend_WC_Tracking_Envios_Ve{
    private static $_instance = null;
    public $id_wc_setting = '';
    public $msg_placeholder = '';

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        $this->id_wc_setting = 'shipping';

        $this->msg_placeholder = 'Rastreo de envio | Empresa: {empresa} | Codigo: {empresa} | Estado: {empresa}. {fecha}';

        register_activation_hook( __FILE__, function(){

        });
    }

    public static function init() {
    $instance = self::instance();

    add_filter("woocommerce_get_sections_{$instance->id_wc_setting}" , [$instance,'section_active_wc_tracking_envios_ve_func']);

    add_action( "woocommerce_get_settings_{$instance->id_wc_setting}", [$instance,'section_setting_wc_tracking_envios_ve_func'],10,2);

    add_action( 'add_meta_boxes', [$instance,'add_shop_order_meta_boxes_func']);

    add_action( 'woocommerce_process_shop_order_meta', [$instance,'save_shop_order_meta_boxes_func'], 10, 2 );

    add_filter('woocommerce_new_order_note_data', [$instance,'woocommerce_new_order_note_data_func'], 10, 2 );    

    }

    public function section_active_wc_tracking_envios_ve_func($settings_tab){
        $settings_tab[WC_ENVIOS_VE_DOMAIN] = __( WC_ENVIOS_VE_TITLE );
        return $settings_tab;
    }

    public function etiquetas_tracking($order_id=''){
        $etiquetas = array(
            array(
                'id' => '{empresa}'
            ),
            array(
                'id' => '{codigo}'
            ),    
            array(
                'id' => '{estado}'
            ),  
            array(
                'id' => '{fecha}'
            ),                                    
        );
        return $etiquetas;
    }

    public function section_setting_wc_tracking_envios_ve_func($settings, $current_section){
        $plugin_settings = [];
        if($current_section !== WC_ENVIOS_VE_DOMAIN){
            return $settings;
        }

        $opciones_empresas = array_column(WC_ENVIOS_VE_EMPRESAS, 'name', 'id');

        $id_etiquetas = array_column($this->etiquetas_tracking(), 'id');
        $id_etiquetas = implode(', ',$id_etiquetas);

        $desc_mensaje_placeholder = sprintf('Mensaje que se visualizará en el momento de consultar el código de rastreo/tracking. <p><strong>Etiquetas:</strong></p><p>%s</p>', $id_etiquetas);

        $mensaje_placeholder = $this->msg_placeholder;

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
                'desc_tip' => __( 'Dentro de cada orden o pedido se visualiza un cuadro para escribir código de rastreo o tracking y seleccionar la empresa de encomienda.', 'default' ),
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
				'name' => __( 'Mensaje en nota' ),
				'type' => 'textarea',
				'desc' => $desc_mensaje_placeholder,
                'desc_tip' => __( 'Dentro de cada orden o pedido se visualiza un cuadro para escribir código de rastreo o tracking y seleccionar la empresa de encomienda.', 'default' ),
				'placeholder' => $mensaje_placeholder,
				'id'	=> WC_ENVIOS_VE_DOMAIN .'_mensaje',
			),

			array( 'type' => 'sectionend', 'id' => WC_ENVIOS_VE_DOMAIN ),

            array(
				'name' => __( 'Shortcode' ),
				'type' => 'title',
				'desc' => __( 'Coloque este shortcode: <strong>[wc_tracking_ve_consulta]</strong> en la página donde desea mostrar un buscador de código de rastreo.' ),
				'id'   => WC_ENVIOS_VE_DOMAIN 
			),


	);

    return $plugin_settings;

    }

    public function funcionamiento_activo(){
        $if_activo = false;
        $funcionamiento_activo = get_option(WC_ENVIOS_VE_DOMAIN .'_enable','');
        if($funcionamiento_activo == 'yes'){
            $if_activo = true;
        }
        return $if_activo;
    }

    public function add_shop_order_meta_boxes_func(){
        $instance = self::instance();

        if($this->funcionamiento_activo() !== true){return false;}

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
            array(
                'id' => WC_ENVIOS_VE_DOMAIN.'_stopped',
                'name' => __('Detenido','default'),
            ),  
            array(
                'id' => WC_ENVIOS_VE_DOMAIN.'_failed',
                'name' => __('Fallido','default'),
            ),                                                        
        ];
        $estados_de_envio_filters = apply_filters( 'estados_de_envio_encomienda',$estados_de_envio);
        return $estados_de_envio_filters;
    }

    public function add_shop_order_meta_boxes_content_func($post){

        if($this->funcionamiento_activo() !== true){return false;}

        $post_id = $post->ID;

        $order = wc_get_order($post_id);

        $opciones_empresas = array_column(WC_ENVIOS_VE_EMPRESAS, 'name', 'id');

        $codigo = $order->get_meta(WC_ENVIOS_VE_DOMAIN.'_codigo');

        /* wc_tracking_ve_empresa */
        $empresa = $order->get_meta(WC_ENVIOS_VE_DOMAIN.'_empresa');

        $empresas_seleccionadas = get_option(WC_ENVIOS_VE_DOMAIN .'_empresas','');

        $empresas = count($empresas_seleccionadas) > 0 ? $empresas_seleccionadas : $opciones_empresas;
        

        $options_empresas = array_column($this->find_empresas($empresas), 'name', 'id');
        $options_empresas = array_merge([''=>'-- Seleccionar empresa --'],$options_empresas);

        /* wc_tracking_ve_codigo */
        
        $nonce = wp_create_nonce()/* wc_tracking_ve_nonce */;

        $estados = array_column($this->estados_de_envio(), 'name', 'id');
        $options_estados = array_merge([''=>'-- Seleccionar estado --'],$estados);
        $estado = $order->get_meta(WC_ENVIOS_VE_DOMAIN.'_estado');

        ob_start();

        woocommerce_form_field( WC_ENVIOS_VE_DOMAIN.'_empresa', array(
            'type'          => 'select',
            'class'         => array('wc_tracking_ve_empresa form-row-wide'),
            'label'         => __('Empresa'),
            'required'    => false,
            'options'     => $options_empresas,
        ), $empresa);
        
        woocommerce_form_field( WC_ENVIOS_VE_DOMAIN.'_codigo', array(
            'type'        => 'text',
            'required'    => false,
            'label'       => 'Código de rastreo/tracking',
            'description' => 'Indique el código del envio.',
        ), $codigo );

        woocommerce_form_field( WC_ENVIOS_VE_DOMAIN.'_estado', array(
            'type'          => 'select',
            'class'         => array('wc_tracking_ve_estado form-row-wide'),
            'label'         => __('Estado de envio'),
            'required'    => false,
            'options'     => $options_estados,
        ), $estado);        

        echo sprintf('<input type="hidden" name="%s_nonce" value="%s" />',WC_ENVIOS_VE_DOMAIN, $nonce);

        $get_order_notes = wc_get_order_notes([
            'order_id' => $order->get_id(),
            'type' => 'customer',
         ]);
        echo '<pre>';
        print_r($get_order_notes);
        echo '</pre>';

        $output = ob_get_contents();
        ob_end_clean();
        echo $output;
    }

    public function woocommerce_new_order_note_data_func($comment){
        $comment_content = $comment['comment_content'];
        $if_wc_envios = strpos($comment_content,WC_ENVIOS_VE_DOMAIN);
        if($if_wc_envios !== false){
            $comment['comment_agent'] = WC_ENVIOS_VE_DOMAIN;
        }
        return $comment;
    }

    public function save_shop_order_meta_boxes_func($order_id, $post){

        $wc_tracking_ve_nonce = isset($_REQUEST[WC_ENVIOS_VE_DOMAIN.'_nonce']) ? $_REQUEST[WC_ENVIOS_VE_DOMAIN.'_nonce'] : '';

        if ( ! wp_verify_nonce($wc_tracking_ve_nonce) ) {
            return $order_id;
        }

        $order = wc_get_order($order_id);

        $msg = get_option(WC_ENVIOS_VE_DOMAIN .'_mensaje',$this->msg_placeholder);

        $campos = array(
            array(
                'name' => 'empresa',
                'tag' => '{empresa}',
                'id' => WC_ENVIOS_VE_DOMAIN.'_empresa',
                'label' => 'Empresa',
            ),
            array(
                'name' => 'codigo',
                'tag' => '{codigo}',
                'id' => WC_ENVIOS_VE_DOMAIN.'_codigo',
                'label' => '{Codigo}',
            ),
            array(
                'name' => 'estado',
                'tag' => '{estado}',
                'id' => WC_ENVIOS_VE_DOMAIN.'_estado',
                'label' => 'Estado',
            )                                 
        );

        $etiquetas = [];
        foreach($campos as $index => $val){
            $campo = $val['id'];
            $label = $val['label'];
            $variable = isset($_REQUEST[$campo]) ? sanitize_text_field($_REQUEST[$campo]) : '';

            $order->update_meta_data($val, $variable);

            $etiquetas[esc_attr($val['tag'])] = $variable;
        }

        $etiquetas['{fecha}'] = date_i18n( 'Y-m-d' );

        #$txt_orde_note .= ' | '.date_i18n( 'Y-m-d' );
        
        $msg_note = str_replace(
            array_keys($etiquetas),
            array_values($etiquetas), 
            $msg);

        $order->add_order_note($msg_note, true );

        $order->save();

    }

}
