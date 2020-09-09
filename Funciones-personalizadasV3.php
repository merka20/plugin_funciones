<?php
/*
Plugin Name: Funciones Personales
Plugin URI: http://merka20.com/
Description: Plugin para personalizar el tema y core de wordpress liberando espacio en <code>functions.php</code> y activarlo a placer (o no) .
Version: 3.0
Author: Oscar Domingo
Author URI: http://merka20.com/
License: GPLv2 o posterior
Fecha: 18/11/2018
*/

//PARA ACCER QUE COJA LOS ESTILOS DESDE EL TEMA HIJO

add_action( 'wp_enqueue_scripts', 'skilled_child_theme_enqueue_styles' );
function skilled_child_theme_enqueue_styles() {
    $parent_style = 'naturalife';
    wp_register_style( $parent_style, get_template_directory_uri() . '/style.css' );
}
// add child theme style after
add_action( 'wp_enqueue_scripts', 'skilled_child_theme_enqueue_child_styles', 101 );
function skilled_child_theme_enqueue_child_styles() {
    $parent_style = 'naturalife';

    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

if ( !function_exists( 'theme_enqueue_styles' ) ):

function theme_enqueue_styles() {
     wp_enqueue_style( 'naturalife', get_template_directory_uri() . '/style.css' );
}

endif;


add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles',10 );


//hacer que coja los estilos de woocommerce desde tema hijo.


function weg_localisation() {


   unload_textdomain('woocommerce');

    load_textdomain('woocommerce', get_stylesheet_directory() . '/woocommerce/i18n/languages/woocommerce-es_ES.mo');

}


add_action('init', 'weg_localisation');


//hacer que coja los estilos de the events calendar desde tema hijo.


function event_calendarpo() {


   unload_textdomain('the-events-calendar');

    load_textdomain('the-events-calendar', get_stylesheet_directory() . '/the-events-calendar/lang/the-events-calendar-es_ES.mo');

}


add_action('init', 'event_calendarpo');


//PONER TRADUCCION TEMA EN TEMA HIJO

function my_child_theme_setup() {
    load_child_theme_textdomain( 'naturalife', get_stylesheet_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'my_child_theme_setup' );


/////////////////////////////////// CÓDIGO DISEÑO PÁGINA ADMINISTRACIÓN WP ////////////////////////////////////////////


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


function mi_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'mi_login_logo_url' );

function mi_login_logo_url_title() {
    return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'mi_login_logo_url_title' );


//////////////////////////////////////// CÓDIGOS DE SEGURIDAD EN WORDPRESS  ///////////////////////////////////////////////

//Elimina referencias a la version de WordPress

add_filter('the_generator','quitar_version_wp');
function quitar_version_wp() { return ''; }
remove_action('wp_head', 'wp_generator');


//Hace que WP no informe de que falla en el login

function sin_errores(){

return 'Acceso denegado.<br/>Contraseña o usuario erroneos';

}

add_filter( 'login_errors', 'sin_errores');

//hacer noindex en ciertas páginas

if ( !function_exists( 'add_noindex_nofollow_404' ) ):

function add_noindex_nofollow_404() {
    if ( is_404()||is_page('aviso-legal')||is_page('politica-de-cookies')|| is_page('politica-de-privacidad')|| is_page('condiciones-de-compra') ) {
        echo '<meta name="robots" content="noindex, nofollow" />';
    }
}
endif;

add_action( 'wp_head', 'add_noindex_nofollow_404' );


//OCULTAR DIRECCIONES DE EMAIL

function security_remove_emails($content) {
    $pattern = '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})/i';
    $fix = preg_replace_callback($pattern, "security_remove_emails_logic", $content);
    return $fix;
}

function security_remove_emails_logic($result) {
    return antispambot($result[1]);
}

add_filter( 'the_content', 'security_remove_emails', 20 ); //ocultar en posts
add_filter( 'comment_text', 'security_remove_emails', 20 ); //ocultar en comentarios
add_filter( 'widget_text', 'security_remove_emails', 20 ); //ocultar en widgets


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

//BLOQUEAR NUMERACIÓN DE USUARIOS
if ( ! is_admin() && isset($_SERVER['REQUEST_URI'])){
    if(preg_match('/(wp-comments-post)/', $_SERVER['REQUEST_URI']) === 0 && !empty($_REQUEST['author']) ) {
        wp_die('404 - File not found!');
    }
}

////////////////////////////////////  CÓDIGOS PARA DAR VELOCIDAD A LA WEB /////////////////////////////////////////

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

//****************Desactivar js y css de Woocommerce en todas las páginas menos en las del woocommerce ********

//hacer que cargue woocommerce solo en paginas de woocommerce

add_action( 'wp_enqueue_scripts', 'borrar_woocommerce_styles_scripts', 99 );

function borrar_woocommerce_styles_scripts() {
    if ( function_exists( 'is_woocommerce' ) ) {
        if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
            # Styles
            wp_dequeue_style( 'woocommerce-general' );
            wp_dequeue_style( 'woocommerce-layout' );
            wp_dequeue_style( 'woocommerce-smallscreen' );
            wp_dequeue_style( 'woocommerce_frontend_styles' );
            wp_dequeue_style( 'woocommerce_fancybox_styles' );
            wp_dequeue_style( 'woocommerce_chosen_styles' );
            wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
            # Scripts
            wp_dequeue_script( 'wc_price_slider' );
            wp_dequeue_script( 'wc-single-product' );
            wp_dequeue_script( 'wc-add-to-cart' );
            wp_dequeue_script( 'wc-cart-fragments' );
            wp_dequeue_script( 'wc-checkout' );
            wp_dequeue_script( 'wc-add-to-cart-variation' );
            wp_dequeue_script( 'wc-single-product' );
            wp_dequeue_script( 'wc-cart' );
            wp_dequeue_script( 'wc-chosen' );
            wp_dequeue_script( 'woocommerce' );
            wp_dequeue_script( 'prettyPhoto' );
            wp_dequeue_script( 'prettyPhoto-init' );
            wp_dequeue_script( 'jquery-blockui' );
            wp_dequeue_script( 'jquery-placeholder' );
            wp_dequeue_script( 'fancybox' );
            wp_dequeue_script( 'jqueryui' );
        }
    }
}


//*********************************************** ACCIONES ESPECIALES SEGUN PROYECTO ********************************************//

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
 *  FORZAR https PARA IMAGENES EN WordPress
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

//QUITAR PROVINCIAS EN LA VENTA CON WOOCOMMERCE
function esp_limita_envios ($provincias) {
   unset ($provincias ['ES'] ['TF']);
   unset ($provincias ['ES'] ['GC']);
   unset ($provincias ['ES'] ['CE']);
   unset ($provincias ['ES'] ['ML']);
   unset ($provincias ['ES'] ['PM']);

   return $provincias;
   }

add_filter ('woocommerce_states', 'esp_limita_envios');


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

# Limpia automáticamente la caché de Autoptimize si va por encima de 500MB
if (class_exists('autoptimizeCache')) {
    $myMaxSize = 500000; # Puedes cambiar este valor a uno mÃ¡s bajo como 100000 para 100MB si tienes poco espacio en el servidor
    $statArr=autoptimizeCache::stats();
    $cacheSize=round($statArr[1]/1024);

    if ($cacheSize>$myMaxSize){
       autoptimizeCache::clearall();
       header("Refresh:0"); # Recarga la pÃ¡gina para que autoptimize pueda crear nuevos archivos de cachÃ©.
    }
}



// Include the Google Analytics Tracking Code (ga.js)

function google_analytics_tracking_code(){

	$propertyID = 'UA-XXXXX-X'; // GA Property ID

	if ($options['ga_enable']) { ?>

		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', '<?php echo $propertyID; ?>']);
		  _gaq.push(['_trackPageview']);

		  (function() {
		    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>

<?php }
}

// include GA tracking code before the closing head tag
add_action('wp_head', 'google_analytics_tracking_code');

// OR include GA tracking code before the closing body tag
// add_action('wp_footer', 'google_analytics_tracking_code');



// add google analytics al pie
function añadir_google_analytics() {
    echo '<script src="http://www.google-analytics.com/ga.js" type="text/javascript"></script>';
    echo '<script type="text/javascript">';
    echo 'var pageTracker = _gat._getTracker("UA-XXXXX-X");';
    echo 'pageTracker._trackPageview();';
    echo '</script>';
}
add_action('wp_footer', 'añadir_google_analytics');


//AÑADIR CAMPO RGPD EN WOOCOMMERCE


function yo_notice_shipping() {
echo '<br/><br/><ul>
<li class="aviso"><strong>Responsable de los datos:</strong> Sos Himalaya te informa que los datos de carácter personal que nos proporciones rellenando el presente formulario serán tratados por Sos Himalaya como responsable de esta web.</li>

<li class="aviso"><strong>Finalidad:</strong> La finalidad de la recogida y tratamiento de los datos personales que te solicitamos es para la compra de un producto y se utilizarán para gestionar el envío del producto y en un futuro enviar campañas de email de nuevas ofertas.</li>

<li class="aviso"><strong>Legitimación:</strong> Al marcar la casilla de aceptación, estás dando tu legítimo consentimiento para que tus datos sean tratados conforme a las finalidades de este formulario descritas en la <a href="https://soshimalaya.org/politica-de-privacidad/">política de privacidad.</a></li>

<li class="aviso"><strong>Destinatario:</strong>Como usuario e interesado te informamos que los datos que nos facilitas estarán ubicados en los servidores de Arsys (proveedor de hosting de Sos Himalaya) dentro de la UE. Ver política de privacidad de <a href="https://www.arsys.es/legal"target="_blank">Arsys</a>.</li>

<li class="aviso">El hecho de que no introduzcas los datos de carácter personal que aparecen en el formulario como obligatorios podrá tener como consecuencia que no puedas inscribirte en los cursos ofertados.</li>

<li class="aviso"><strong>Derechos:</strong>Podrás ejercer tus derechos de acceso, rectificación, limitación y suprimir los datos en fundacion@soshimalaya.org así como el Derecho a presentar una reclamación ante una autoridad de control.</li>

<li class="aviso">Puedes consultar la información adicional y detallada sobre Protección de Datos en nuestra página web, así como consultar nuestra <a href="https://soshimalaya.org/politica-de-privacidad/">política de privacidad.</a></li>
</ul>';
}


add_action( 'woocommerce_review_order_after_submit', 'yo_notice_shipping' );


//AÑADIR CAMPOS RGPD EN COMENTARIOS

/** Aceptación después del formulario de comentarios **/
add_filter( 'comment_form_field_comment', 'mi_campo_de_privacidad_en_comentarios' );
function mi_campo_de_privacidad_en_comentarios( $comment_field ) {
    return $comment_field.'<p class="pprivacy"><input type="checkbox" name="privacy" value="privacy-key" class="privacyBox" aria-req="true">&nbsp;&nbsp;Acepto la <a target="blank" href="https://soshimalaya.org/politica-de-privacidad/">política de privacidad</a><p>';
}
//validación por javascript
add_action('wp_footer','validate_privacy_comment_javascript');
function validate_privacy_comment_javascript(){
    if (is_single() && comments_open()){
        wp_enqueue_script('jquery');
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            $("#submit").click(function(e)){
                if (!$('.privacyBox').prop('checked')){
                    e.preventDefault();
                    alert('Debes aceptar nuestra política de privacidad marcando la casilla ....');
                    return false;
                }
            }
        });
        </script>
        <?php
    }
}

//sin validación js
add_filter( 'preprocess_comment', 'verify_comment_privacy' );
function verify_comment_privacy( $commentdata ) {
    if ( ! isset( $_POST['privacy'] ) )
        wp_die( __( 'Error: Debes aceptar nuestra política de privacidad marcando la casilla ....' ) );

    return $commentdata;
}

//guarda el campo como comment meta
add_action( 'comment_post', 'save_comment_privacy' );
function save_comment_privacy( $comment_id ) {
    add_comment_meta( $comment_id, 'privacy', $_POST[ 'privacy' ] );
}

//CAMPOS PRIMERA CAPA DE INFORMACIÓN FINALIDAD LEGITIMACIÓN ETC..

/** Primera capa privacidad en comentarios **/
$acceptance = '<p class="comment-reply-title comentarios">Información sobre protección de datos</p>
<p class="comment-subscription-form"><ul>
<li class="aviso"><strong>Responsable de los datos: </strong> Sos Himalaya te informa que los datos de carácter personal que nos proporciones rellenando el presente formulario serán tratados por Sos Himalaya como responsable de esta web.</li>
<li class="aviso"><strong>Finalidad: </strong> La finalidad de la recogida y tratamiento de los datos personales que te solicitamos es sólo para gestionar los comentarios del blog.</li>
<li class="aviso"><strong>Legitimación: </strong> Al marcar la casilla de aceptación, estás dando tu legítimo consentimiento para que tus datos sean tratados conforme a las finalidades de este formulario descritas en la <a href="https://soshimalaya.org/politica-de-privacidad/">política de privacidad.</a></li>
<li class="aviso"><strong>Destinatario: </strong> Como usuario e interesado te informamos que los datos que nos facilitas estarán ubicados en los servidores de Arsys (proveedor de hosting de Sos Himalaya) dentro de la UE. Ver política de privacidad de <a href="https://www.arsys.es/legal"target="_blank">Arsys</a>.</li>
<li class="aviso">El hecho de que no introduzcas los datos de carácter personal que aparecen en el formulario como obligatorios podrá tener como consecuencia que no pueda atender tu solicitud.</li>
<li class="aviso"><strong>Derechos: </strong> Podrás ejercer tus derechos de acceso, rectificación, limitación y suprimir los datos en a&#100;m&#105;&#110;&#105;st&#114;&#97;ci&#111;&#110;&#64;c&#112;&#102;&#101;&#109;&#101;rg&#101;n&#99;i&#97;&#115;&#46;&#101;s así como el Derecho a presentar una reclamación ante una autoridad de control.</li>
<li class="aviso">Puedes consultar la información adicional y detallada sobre Protección de Datos en nuestra página web, así como consultar nuestra <a href="https://soshimalaya.org/politica-de-privacidad/">política de privacidad.</a></li>
</ul></p>';
function ft_acceptance_comments( $form ) {
    global $acceptance;
    return $form . $acceptance;
}
    add_action( 'comment_form_field_comment', 'ft_acceptance_comments' );


//borrar campo web en comentarios del blog
//
add_filter ('comment_form_field_url', function ($url) {
  return;
});

function scripts_footer() { 

    remove_action('wp_head', 'wp_print_scripts'); 
    remove_action('wp_head', 'wp_print_head_scripts', 9); 
    remove_action('wp_head', 'wp_enqueue_scripts', 1);
    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5); 
} 
add_action( 'wp_enqueue_scripts', 'scripts_footer' );
