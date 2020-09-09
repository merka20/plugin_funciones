
<?php
/*
Plugin Name: Funciones Personales
Plugin URI: http://merka20.com/
Description: Plugin para personalizar el tema y core de wordpress liberando espacio en <code>functions.php</code> y activarlo a placer (o no) .
Version: 1.0
Author: Oscar Domingo
Author URI: http://merka20.com/
License: GPLv2 o posterior
*/

// Codigos de seguridad en wordpress

/*HACER QUE APAREZCA EL LOGO DE EMPRESA AL INICIO DE SESION*/


function cmerka20_custom_login_logo() {
    echo '
        <style type="text/css">
        .login h1 a {
            background-image: url(' . get_stylesheet_directory_uri() . '/imagenes/logo.png)!important;
            padding-bottom: 0px;
            width: 100%;
            background-size: contain;
        }
    </style>';
}
add_action( 'login_enqueue_scripts', 'cmerka20_custom_login_logo' );

//Colocar la url de la empresa en el logo

function mi_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'mi_login_logo_url' );


//Colocar titulo al ponerte encima del logo en el login

function my_login_logo_url_title() {  
    return 'Grupo#ForSM';  
}  
add_filter( 'login_headertitle', 'my_login_logo_url_title' );  



//Elimina referencias a la version de WordPress

function quitar_version_wp() {

return '';

}

add_filter('the_generator', 'quitar_version_wp');



//Hace que WP no informe de que falla en el login

function sin_errores(){

return 'Acceso denegado.<br/>Contraseña o usuario erroneos';

}

add_filter( 'login_errors', 'sin_errores');


//hacer noindex en ciertas páginas
//
if ( !function_exists( 'add_noindex_nofollow_404' ) ):

function add_noindex_nofollow_404() {
    if ( is_404()||is_page('aviso-legal')||is_page('politica-de-cookies')|| is_page('politica-de-privacidad')|| is_page('condiciones-de-compra') ) {
        echo '<meta name="robots" content="noindex, nofollow" />';
    }
}
endif;

add_action( 'wp_head', 'add_noindex_nofollow_404' );




// Quitar xml-rpc de wordpress

add_filter( 'xmlrpc_methods', function( $methods ) {
   unset( $methods['pingback.ping'] );
   return $methods;
} );


//Cabecera X-Content-Type //Cabecera X-Frame //Cabecera XSS

add_action( 'send_headers', 'add_header_seguridad' );
function add_header_seguridad() {
header( 'X-Content-Type-Options: nosniff' );
header( 'X-Frame-Options: SAMEORIGIN' );
header( 'X-XSS-Protection: 1;mode=block' );
}

//PARAR NUMERACIÓN DE USUARIOS

if ( ! is_admin() && isset($_SERVER['REQUEST_URI'])){
    if(preg_match('/(wp-comments-post)/', $_SERVER['REQUEST_URI']) === 0 && !empty($_REQUEST['author']) ) {
        wp_die('404 - File not found!');
    }
}

//**************** ACCIONES ESPECIALES SEGUN PROYECTO ********************************//

/*HACER QUE WORDPRESS ADMITA SVG*/

add_filter( 'upload_mimes', 'custom_upload_mimes' );
function custom_upload_mimes( $existing_mimes = array() ) {
    // Add the file extension to the array
    $existing_mimes['svg'] = 'image/svg+xml';
    return $existing_mimes;
}

// Añadir Slideshare a oEmbed

function oembed_slideshare(){

wp_oembed_add_provider( 'http://www.slideshare.net/*', 'http://api.embed.ly/v1/api/oembed');

}

add_action('init','oembed_slideshare');

/* NO SE QUE ES ESTO */

function patricks_custom_variation_price( $price, $product ) {
    $target_product_types = array( 
        'variable' 
    );
    if ( in_array ( $product->product_type, $target_product_types ) ) {
        // if variable product return and empty string
        return $product->min_variation_price.'€';
    }
    // return normal price
    return $price;
}
add_filter('woocommerce_get_price_html', 'patricks_custom_variation_price', 10, 2);

/**
 *
 *  FORZAR http/s PARA IMAGENES EN WordPress
 *
 *  Source:
 *  https://core.trac.wordpress.org/ticket/15928#comment:63
 *
 *  @param $url
 *  @param $post_id
 *
 *  @return string
 */
function ssl_post_thumbnail_urls( $url, $post_id ) {

    //Skip file attachments
    if ( ! wp_attachment_is_image( $post_id ) ) {
        return $url;
    }

    //Correct protocol for https connections
    list( $protocol, $uri ) = explode( '://', $url, 2 );

    if ( is_ssl() ) {
        if ( 'http' == $protocol ) {
            $protocol = 'https';
        }
    } else {
        if ( 'https' == $protocol ) {
            $protocol = 'http';
        }
    }

    return $protocol . '://' . $uri;
}

add_filter( 'wp_get_attachment_url', 'ssl_post_thumbnail_urls', 10, 2 );


//PONER TRADUCCION EN TEMA HIJO

function my_child_theme_setup() {
    load_child_theme_textdomain( 'tm-organik', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'my_child_theme_setup' );

//Poner woocommerce como catálogo de productos

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );

/** No mostrar SKU **/
add_filter( 'wc_product_sku_enabled', '__return_false' );

// Mostrar 12 productos por página. 
add_filter( 'loop_shop_per_page', create_function( '$cols', 'return 12;' ), 20 );



/* ECOMMERCE*/

/**
 * Formato precios variable product WC 2.0
 *
 * @param  string $price
 * @param  object $product
 * @return string
 */
function wc_wc20_variation_price_format( $price, $product ) {
    // Main Price
    $prices = array( $product->get_variation_price( 'min', true ), $product->get_variation_price( 'max', true ) );
    $price = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );

    // Sale Price
    $prices = array( $product->get_variation_regular_price( 'min', true ), $product->get_variation_regular_price( 'max', true ) );
    sort( $prices );
    $saleprice = $prices[0] !== $prices[1] ? sprintf( __( '%1$s', 'woocommerce' ), wc_price( $prices[0] ) ) : wc_price( $prices[0] );

    if ( $price !== $saleprice ) {
        $price = '<del>' . $saleprice . '</del> <ins>' . $price . '</ins>';
    }
    
    return $price;
}
add_filter( 'woocommerce_variable_sale_price_html', 'wc_wc20_variation_price_format', 10, 2 );
add_filter( 'woocommerce_variable_price_html', 'wc_wc20_variation_price_format', 10, 2 );



/*Ocultar otros metodos de envio al pasar al envio gratuito.*/

add_filter( 'woocommerce_package_rates', 'my_hide_shipping_when_free_is_available', 100 );

function my_hide_shipping_when_free_is_available( $rates ) {

    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;
}

/**
 * @snippet       Añadir nota de envío en carrito - WooCommerce
 * @how-to        Watch tutorial @ https://businessbloomer.com/?p=19055
 * @sourcecode    https://businessbloomer.com/?p=358
 * @author        Rodolfo Melogli
 * @compatible    WC 2.6.14, WP 4.7.2, PHP 5.5.9
 */
 
function bbloomer_notice_shipping() {
echo '<p id="allw">Envío gratuito a partir de 42€ ó entregas en Pamplona.</p>';
}
 
add_action( 'woocommerce_review_order_before_payment', 'bbloomer_notice_shipping' );


function yo_notice_shipping() {
echo '<p id="allw">Envío gratuito a partir de 42€ ó entregas en Pamplona.</p>';
}
 
add_action( 'woocommerce_cart_totals_after_order_total', 'yo_notice_shipping' );

/*Limitar ciertos envíos con WooCommerce en este caso están limitados envíos a tenerife, gran canarias. melilla. ceuta y baleares*/

function ejr_limita_envios ($provincias) {
   unset ($provincias ['ES'] ['TF']);
   unset ($provincias ['ES'] ['GC']);
   unset ($provincias ['ES'] ['CE']);
   unset ($provincias ['ES'] ['ML']);
   unset ($provincias ['ES'] ['PM']);
   return $provincias;
   }
 
add_filter ('woocommerce_states', 'ejr_limita_envios');


/** 
 *Reduce the strength requirement on the woocommerce password.
 * 
 * Strength Settings
 * 3 = Strong (default)
 * 2 = Medium
 * 1 = Weak
 * 0 = Very Weak / Anything
 */
function reduce_woocommerce_min_strength_requirement( $strength ) {
    return 0;
}
add_filter( 'woocommerce_min_password_strength', 'reduce_woocommerce_min_strength_requirement' );



/*AÑADIR NIF EN WOOCOMMERCE INICIO */

/*** Añade el campo NIF a la página de checkout de WooCommerce ***/

add_action( 'woocommerce_after_order_notes', 'agrega_mi_campo_personalizado' );
 
function agrega_mi_campo_personalizado( $checkout ) {
 
    echo '<div id="additional_checkout_field"><h2>' . __('Información adicional') . '</h2>';
 
    woocommerce_form_field( 'nif', array(
        'type'          => 'text',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('NIF/DNI/NIE'),
        'required'      => false,
        'placeholder'   => __('Introduce el Nº NIF o DNI o NIE'),
        ), $checkout->get_value( 'nif' ));
 
    echo '</div>';
 
}

// /*** Comprueba que el campo NIF no esté vacío ***/
// add_action('woocommerce_checkout_process', 'comprobar_campo_nif');
 
// function comprobar_campo_nif() {
   
//     // Comprueba si se ha introducido un valor y si está vacío se muestra un error.
//     if ( ! $_POST['nif'] )
//         wc_add_notice( __( 'NIF/DNI/NIE, es un campo requerido.' ), 'error' );
// }

/*** Actualiza la información del pedido con el nuevo campo ***/
add_action( 'woocommerce_checkout_update_order_meta', 'actualizar_info_pedido_con_nuevo_campo' );
 
function actualizar_info_pedido_con_nuevo_campo( $order_id ) {
    if ( ! empty( $_POST['nif'] ) ) {
        update_post_meta( $order_id, 'NIF', sanitize_text_field( $_POST['nif'] ) );
    }
} 

/*** Muestra el valor del nuevo campo NIF en la página de edición del pedido ***/
add_action( 'woocommerce_admin_order_data_after_billing_address', 'mostrar_campo_personalizado_en_admin_pedido', 10, 1 );
 
function mostrar_campo_personalizado_en_admin_pedido($order){
    echo '<p><strong>'.__('NIF').':</strong> ' . get_post_meta( $order->id, 'NIF', true ) . '</p>';
} 

/*** Incluye el campo NIF en el email de notificación del cliente ***/
 
add_filter('woocommerce_email_order_meta_keys', 'muestra_campo_personalizado_email');
 
function muestra_campo_personalizado_email( $keys ) {
    $keys[] = 'NIF';
    return $keys;
}

/*** Incluir NIF en la factura ***/
 
add_filter( 'wpo_wcpdf_billing_address', 'incluir_nif_en_factura' );
 
function incluir_nif_en_factura( $address ){
  global $wpo_wcpdf;
 
  echo $address . '<p>';
  $wpo_wcpdf->custom_field( 'NIF', 'NIF/DNI/NIE: ' );
  echo '</p>';
}

/*FIN AÑADIR NIF EN WOOCOMMERCE */




//****************Desactivar js y css de contact form en todas las páginas menos en la del contacto ********

//eliminar Java Script de contact form 7

add_action( 'wp_print_scripts', 'deregister_cf7_javascript', 100 );
function deregister_cf7_javascript() {
    if ( !is_page('contacto') ) {
        wp_deregister_script( 'contact-form-7' );
    }
}

//eliminar CSS de contact form 7

add_action( 'wp_print_styles', 'deregister_cf7_styles', 100 );
function deregister_cf7_styles() {
    if ( !is_page('contacto') ) {
        wp_deregister_style( 'contact-form-7' );
    }
}

# Limpia automáticamente la caché de Autoptimize si va por encima de 500MB
if (class_exists('autoptimizeCache')) {
    $myMaxSize = 500000; # Puedes cambiar este valor a uno más bajo como 100000 para 100MB si tienes poco espacio en el servidor
    $statArr=autoptimizeCache::stats(); 
    $cacheSize=round($statArr[1]/1024);
    
    if ($cacheSize>$myMaxSize){
       autoptimizeCache::clearall();
       header("Refresh:0"); # Recarga la página para que autoptimize pueda crear nuevos archivos de caché.
    }
}


