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


function mi_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'mi_login_logo_url' );

function mi_login_logo_url_title() {
    return get_bloginfo( 'name' );    
}
add_filter( 'login_headertitle', 'mi_login_logo_url_title' );


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



add_action( 'wp_print_scripts', 'deregister_cf7_javascript', 100 );


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
