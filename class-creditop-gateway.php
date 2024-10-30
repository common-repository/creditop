<?php
/*
 ******************************************************
 *  Creditop
 *
 *  Copyright (c) 2024 Creditop
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License version 2 as published by
 *  the Free Software Foundation.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
 ******************************************************
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Creditop_Gateway extends WC_Payment_Gateway {
  
  public function __construct() {
    $this->id                 = 'creditop_gateway';
    $this->description = '<div class="custom-description" style="margin-bottom: 10px; text-align: center;">' .
                '<img src="' . plugins_url('assets/creditop-description.png', __FILE__) . '" alt="¡Revisa acá las diferentes opciones de financiamiento!" style="max-width: 100%; min-height: 320px;" />' .
                '</div>';
    $this->title='Paga a cuotas con Creditop';
    
    $this->icon = plugins_url( 'assets/creditop-badge.png', __FILE__ );

            
    $this->has_fields = true;
    $this->method_title = 'Creditop';
    $this->method_description = 'Conectamos comercios con múltiples opciones de financiamiento para sus clientes.';
        
    $this->supports = array('products');
        
    $this->init_form_fields();
    $this->init_settings();
        
        
    $this->enabled = $this->get_option('enabled');
    $this->hash = $this->get_option('hash');
    $this->token = $this->get_option('token');
     $position = $this->get_option('before_add_to_cart_widget_enabled');  
        $header_position= $this->get_option('header_widget_position');  
    add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
   add_filter('woocommerce_available_payment_gateways', array($this, 'woocommerce_available_payment_gateways'));

  $this->add_custom_header_hook($position);
     $this->add_custom_badge_hook($position);
  
      add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
}

public function enqueue_styles() {
    wp_enqueue_style('creditop-styles', plugins_url('assets/creditop-styles.css', __FILE__));
}

public function add_custom_badge_hook($position) {
    switch ($position) {
        case 'before_add_to_cart_button':
            add_action('woocommerce_before_add_to_cart_button', array($this, 'display_custom_badge'), 10); 
            break;
        case 'before_add_to_cart_form':
            add_action('woocommerce_before_add_to_cart_form', array($this, 'display_custom_badge'), 10);
            break;
        case 'after_add_to_cart_form':
            add_action('woocommerce_after_add_to_cart_form', array($this, 'display_custom_badge'), 10);
            break;
        case 'before_single_product_summary':
            add_action('woocommerce_before_single_product_summary', array($this, 'display_custom_badge'), 10);
            break;
        case 'product_meta_start':
            add_action('woocommerce_product_meta_start', array($this, 'display_custom_badge'), 10);
            break;
        case 'product_meta_end':
            add_action('woocommerce_product_meta_end', array($this, 'display_custom_badge'), 10);
            break;
  
    }
}
public function add_custom_header_hook($position) {
    switch ($position) {
    case 'after_header':
        add_action('wp_after_header', array($this, 'add_homepage_banner'), 10);
        break;
    case 'after_navbar':
        add_action('wp_after_navbar', array($this, 'add_homepage_banner'), 10);
        break;
     case 'after_custom_header':
        add_action('get_header', array($this, 'add_homepage_banner'), 10);
        break;
    default:
        add_action('wp_head', array($this, 'add_homepage_banner'));
  
    }
}
public function add_homepage_banner() {
    // Check if the banner should be displayed
    $widget_setting = $this->get_option('homepage_widget_enabled');
    static $banner_displayed = false; // Static variable to track if the banner has been displayed

    // Display the banner based on the setting
    if (!$banner_displayed) {
        // Get current page type
        $is_product_page = is_product(); // Check if we are on a product detail page

        if ($widget_setting === 'home' && is_front_page()) {
            // Display for homepage only
            $banner_displayed = true;
            $this->render_banner(); // Call a separate method to render the banner
        } elseif ($widget_setting === 'all') {
            // Display for all pages
            $banner_displayed = true;
            $this->render_banner(); // Call a separate method to render the banner
        } elseif ($widget_setting === 'all_except_product' && !$is_product_page) {
            // Display for all pages except product detail
            $banner_displayed = true;
            $this->render_banner(); // Call a separate method to render the banner
        }
    }
}

// Method to render the banner
private function render_banner() {
    echo '<div class="custom-badge" style="background-color: #503cfc; width: 100%; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;">
    
    <img src="'.plugins_url('assets/header1.png', __FILE__).'" alt="Creditop Logo" style="height: 30px; margin: 0; align-self: flex-start; margin-bottom: -30px">
    
    <img class="hide-on-small" src="'.plugins_url('assets/header2.png', __FILE__).'" alt="Creditop Logo" style="height: 20px; margin: 0; align-self: flex-end;">
    
    <img class="hide-on-small" src="'.plugins_url('assets/header3.png', __FILE__).'" alt="Creditop Logo" style="height: 30px; margin: 0; align-self: flex-start;">

    <!-- Middle text block -->
    <div class="middle-section" style="display: flex; align-items: center; flex-wrap: nowrap; text-align: center; margin: 10px 0; color: white;">
        Financiar tus compras es más sencillo con 
          <img id="creditop_logo" src="'.plugins_url('assets/creditop-white-logo.png', __FILE__).'" style="margin: 0 15px;margin-bottom:2px; max-height: 30px;"> 
        compra hoy y paga después
    </div>

    <img class="hide-on-small" src="'.plugins_url('assets/header4.png', __FILE__).'" alt="Creditop Logo" style="height: 30px; margin: 0; align-self: flex-start;">
    <img class="hide-on-small" src="'.plugins_url('assets/header5.png', __FILE__).'" alt="Creditop Logo" style="height: 30px; margin: 0; align-self: flex-end;">
    <img src="'.plugins_url('assets/header6.png', __FILE__).'" alt="Creditop Logo" style="height: 30px; margin: 0; align-self: flex-end; margin-top: -30px">
</div>
';
}




 // Method to display the badge image
public function display_custom_badge() {
   static $banner_added = false; 


    // Determine if we should display the banner based on the setting
    if (!$banner_added) {
        
        echo    '<div class="before-add-to-cart" style="margin-bottom: 10px; background-color: #503cfc;  width: 100%; display: flex; align-items: center; justify-content: space-between;">
    <img src="'.plugins_url( 'assets/header1.png', __FILE__ ).'" alt="Creditop Logo" style="height: 20px; padding: 0; align-self: flex-start;margin-right: -10px;">

<div style="display: inline-block; text-align: center; margin: 10px 0; color: white; vertical-align: middle;">
    <span style="display: inline-block; vertical-align: middle; margin: 3px 0;">
        Paga con múltiples opciones de financiamiento en&nbsp;
        <img src="'.plugins_url( 'assets/creditop-white-logo.png', __FILE__ ).'" alt="Creditop Logo" style="height: 17px; vertical-align: middle;margin-bottom:2px;position: relative;z-index:4;">
    </span>
</div>


    
    <img src="'.plugins_url( 'assets/header6.png', __FILE__ ).'" alt="Creditop Logo" style="height: 25px;align-self: flex-end;margin-left: -35px;">
</div>';
        $banner_added = true; 
    }
}
    public function woocommerce_available_payment_gateways($available_gateways) {
        if (!is_checkout()) return $available_gateways;
        if (array_key_exists('creditop_gateway', $available_gateways)) {
            $available_gateways['creditop_gateway']->order_button_text = __('Paga con Creditop', 'woocommerce');
        }
        return $available_gateways;
    }
  public function init_form_fields() {
         $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Activar/Desactivar',
                    'type' => 'checkbox',
                    'label' => 'Activar el método de pago Creditop',
                    'default' => 'yes',
                ),
                'hash' => array(
                    'title' => 'Hash',
                    'type' => 'text',
                    'description' => 'Hash entregado por creditop para identificarte como comercio aliado.',
                ),
                'token' => array(
                    'title' => 'Token',
                    'type' => 'text',
                    'description' => 'Token entregado por Creditop para validar tu identidad.',
                ),
                'checkout_config' => array(
                    'title' => 'Configuración del checkout',
                    'type' => 'title',
                    'description' => '¿Has asignado nombres personalizados a alguno de los siguientes campos? Si no has realizado cambios en ellos, déjalos en blanco:',
                ),  
                'first_name' => array(
                    'title' => 'Campo nombres',
                    'type' => 'text',
                    'default' => '',
                ),
                'surname' => array(
                    'title' => 'Campo apellidos',
                    'type' => 'text',
                    'default' => '',
                ),
                'document_number' => array(
                    'title' => 'Campo documento',
                    'type' => 'text',
                    'default' => '',
                ),
                'address' => array(
                    'title' => 'Campo dirección',
                    'type' => 'text',
                    'default' => '',
                ),
                'city' => array(
                    'title' => 'Campo ciudad',
                    'type' => 'text',
                    'default' => '',
                ),
                'phone' => array(
                    'title' => 'Campo teléfono',
                    'type' => 'text',
                    'default' => '',
                ),
               'homepage_widget_enabled' => array(
            'title' => 'Configuración del widget del encabezado',
            'type' => 'select',
            'label' => 'Selecciona dónde mostrar el widget',
            'default' => 'home',
            'options' => array(
                'home' => 'Solo en la página de inicio',
                'all' => 'En todas las páginas',
                                  'all_except_product' => 'En todas las páginas excepto en detalles del producto', 
                'none' => 'No mostrar',
            ),
        ),'header_widget_position' => array(
    'title' => 'Posición del widget del encabezado',
    'type' => 'select',
    'description' => 'Selecciona la posición donde quieres que se muestre el widget del encabezado.',
    'default' => 'wp_head',
    'options' => array(
        'after_header' => 'Después del encabezado',
        'after_navbar' => 'Después de la barra de navegación',
        'wp_head'=>'Antes del encabezado',
        'after_custom_header' => 'Después del encabezado personalizado',
    ),
),
        'before_add_to_cart_widget_enabled' => array(
            'title' => 'Activar widget antes del botón de añadir al carrito',
            'type' => 'select',
            'description' => 'Selecciona la posición donde quieres que se muestre el widget.',
            'default' => 'before_add_to_cart_button',
            'options' => array(
                'before_add_to_cart_button'=>'Antes del botón de añadir al carrito',
                'before_add_to_cart_form'=>'Antes del formulario para añadir producto',
                'after_add_to_cart_form' => 'Después del formulario para añadir producto',
                'before_single_product_summary'  => 'Antes del resumen del producto',
                'product_meta_start' => 'Antes de la información metadata del producto',
                'product_meta_end'  => 'Después de la información metadata del producto',
                'disable'=>'No mostrar'
            ),
        ),
            );
  }
  
  // Process the payment
  public function process_payment($order_id) {
      $order = wc_get_order($order_id);

    if (!$order) {
        error_log('Failed to retrieve order with ID: ' . $order_id);
        return;
    }

    $products_info = array();

   foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product(); // Get the product object
        $product_meta_data = $product->get_meta_data(); // Get product metadata

        // Extract metadata
        $metadata = [];
        foreach ($product_meta_data as $meta) {
            $metadata[$meta->key] = $meta->value;
        }

        // Collect product information including metadata
        $product_info = [
            'product_id' => $product->get_id(),
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'price' => $product->get_price(),
        ];

        // Add product info to the array
        $products_info[] = $product_info;
    }

    // Data
    $order_data = $order->get_data();
    unset($order_data['line_items']); 
    unset($order_data['meta_data']); 
    $serialized_order_data = serialize($order_data);
    $data=base64_encode($serialized_order_data);
    
    //Products
    $encoded_products_info = wp_json_encode($products_info);
    $products=base64_encode($encoded_products_info);
    
    //Return url
    $return_url = $this->get_return_url() ? base64_encode(serialize($this->get_return_url())) : home_url();
    
    //endpoint url
    $endpoint_url = get_rest_url(null, '/wc/v3/orders/');
    $endpoint=base64_encode($endpoint_url);
    
    //token
    $token = base64_encode($this->get_option('token'));
    
    //hash
    $hash = $this->get_option('hash');
    
    // Collect checkout configuration fields
    $checkout_config = array();

    foreach (array('first_name', 'surname', 'document_number', 'address', 'city', 'phone') as $field) {
        $checkout_config[$field] = $this->get_option($field);
    }
    $encoded_checkout_config = wp_json_encode($checkout_config);
    $serialized_checkout_config = serialize($encoded_checkout_config);
    $config=base64_encode($serialized_checkout_config);
    $redirect_url = 'https://aliados.creditop.com/checkout/' . $hash . 
                    '/?o=' . $data . 
                    '&p=' . $products . 
                    '&u=' . $return_url . 
                    '&ps=' . $endpoint . 
                    '&t=' . $token . 
                    '&config=' . $config;
    // Redirect to the Creditop payment page
    return array(
        'result' => 'success',
        'redirect' => $redirect_url,
    );
  }
  
}
?>