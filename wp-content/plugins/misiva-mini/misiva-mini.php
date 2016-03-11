<?php
/*
Plugin Name: Misiva Mini
Plugin URI: http://example.com/my-crazy-admin-theme
Description: Misiva Visor mini de admin  - Upload and Activate.
Author: Misiva Corp.
Version: 1.0
Author URI: http://misiva.com.ec
*/


/* Disable WordPress Admin Bar for all users but admins. */
//desabilitamos  la barra de admin

show_admin_bar(false);

function theme_name_scripts()
{
    wp_enqueue_style('my-admin-theme', plugins_url('recursos/misiva_style.css', __FILE__));
    wp_enqueue_script('script-name', plugins_url('recursos/misiva-front-mini.js', __FILE__), array(), '1.0.0', true);
}

add_action('wp_enqueue_scripts', 'theme_name_scripts');

// para el envio por email
function mi_funcion_ajax()
{
    $user_asunto = $_POST ['user_asunto'];
    $user_cuerpo = $_POST ['user_cuerpo'];
    $user_file = $_POST ['user_file'];
    $user_from = $_POST ['user_from'];
    $user_name = $_POST ['user_name'];
    $user_to = $_POST ['user_to'];
    $user_file_name = $_POST ['user_file_name'];

    add_filter('wp_mail_content_type', 'set_html_content_type');
    $to = $user_to;
    $subject = $user_asunto;
    $message = "Se a enviado un archivo, <br><br>Mensaje: <br><br> " . $user_cuerpo . "<br><br> Para descargar de click en el siguiente vínculo : <br><br>  <a  href='" . $user_file . "'>" . $user_file_name . "</a>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
    $headers .= "From: MyPlugin <" . $user_from . ">" . "\r\n";

    wp_mail($to, $subject, $message, $headers);

    remove_filter('wp_mail_content_type', 'set_html_content_type');

    echo 'Mensaje enviado, ';
    die();
}

add_filter('wp_mail_from', 'custom_wp_mail_from');
function custom_wp_mail_from($email)
{
    global $current_user;
    get_currentuserinfo();
    return $current_user->user_email;
}

add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');
function custom_wp_mail_from_name($name)
{
    global $current_user;
    get_currentuserinfo();
    return $current_user->display_name;
}

function set_html_content_type()
{
    return 'text/html';
}

// Creando las llamadas Ajax para el plugin de WordPress  
add_action('wp_ajax_nopriv_mi_funcion_accion', 'mi_funcion_ajax');
add_action('wp_ajax_mi_funcion_accion', 'mi_funcion_ajax');


function misiva_save_post()
{

    // The $_REQUEST contains all the data sent via ajax
    if (isset($_REQUEST)) {

        require_once("recursos/misiva-post-submit.php");

    } else {
        echo "no parametros";
    }

    // Always die in functions echoing ajax content
    die();
}

add_action('wp_ajax_misiva_save_post', 'misiva_save_post');

add_shortcode('misiva_parallax', 'misiva_parallax_call');

function misiva_parallax_call()
{
    $user_ID = get_current_user_id();
    if ($user_ID != 0) {
        if (function_exists('get_wp_parallax_content_slider')) {
            get_wp_parallax_content_slider();
        }
    }

}

add_shortcode("misiva_wpdm_category", "misiva_wpdm_category");

function misiva_wpdm_category($params)
{

    // muestra solamente cuando esta logeado una categoria.
    $user_ID = get_current_user_id();
    if ($user_ID != 0) {
        $params['order_field'] = isset($params['order_by']) ? $params['order_by'] : 'publish_date';
        unset($params['order_by']);
        if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
        unset($params['item_per_page']);
        return wpdm_embed_category($params);
    }
}

?>

<?php
//***********************
//Misiva Widget
// Creating the widget

class wpb_widget extends WP_Widget
{

    function __construct()
    {
        parent::__construct(
// Base ID of your widget
            'wpb_widget',

// Widget name will appear in UI
            __('Misiva Simple Upload DownloaManager', 'wpb_widget_domain'),

// Widget description
            array('description' => __('Cargador de archivos en front end de DownloadManager', 'wpb_widget_domain'),)
        );
    }

// Creating widget front-end
// This is where the action happens
    public function widget($args, $instance)
    {
        wp_enqueue_script( 'jquery-ui-tabs' );
        $title = apply_filters('widget_title', $instance['title']);
// before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];


// Creación formulario
        $user_ID = get_current_user_id();
        if ($user_ID != 0) {
            $user_info = get_userdata($user_ID);
            $nombre = $user_info->first_name . " " . $user_info->last_name;
            $email = $user_info->user_email;

            ?>
            <a class="popup-with-form" href="#form-send-f">Open form</a>

            <div id="form-send-f" class="mfp-hide white-popup-block">
                <h3>Envío de link por email</h3>

                <form name="login" id="login" class="forma-login-envio">
                    <input class="box-text" type="hidden" id="user_name" name="user_name" required="required"
                           placeholder="De:" value="<?php echo $nombre; ?>">
                    <input class="box-text" type="hidden" id="user_from" name="user_from" required="required"
                           placeholder="Nombre:" value="<?php echo $email; ?>">
                    <ul class="login_wid">

                        <li><input class="box-text" type="email" id="user_to" name="user_to" required="required"
                                   placeholder="Para:"></li>
                        <li><input class="box-text" type="text" id="user_asunto" name="user_asunto" required="required"
                                   placeholder="Asunto:"></li>
                        <li><input class="box-text" type="text" id="user_cuerpo" name="user_cuerpo" required="required"
                                   placeholder="Mensaje:"></li>
                        <li>
                            <div><input class="btn-ingreso" name="login" type="submit" value="Enviar"></div>
                        </li>
                        <li>
                            <div id='mensaje-envio'></div>
                        </li>

                    </ul>
                    <div class="campos-reservados">
                        <input class="box-text" type="text" id="user_file_name" name="user_file_name"
                               required="required"
                               placeholder="Nombre archivo">
                        <input class="box-text" type="text" id="user_file" name="user_file" required="required"
                               placeholder="Archivo">
                    </div>

                </form>
                <?php
                global $current_user;
                get_currentuserinfo();
                //echo 'Username: ' . $current_user->user_login . "\n";
                ?>

            </div>
            <link rel='stylesheet'
                  href='<?php echo get_site_url(); ?>/wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=dashicons,buttons,media-views,wp-admin,wp-auth-check,wp-pointer,wp-jquery-ui-dialog&amp;ver=4.2.4'
                  type='text/css' media='all'/>

            <link rel='stylesheet' id='jqui-css-css'
                  href='<?php echo get_site_url(); ?>/wp-content/plugins/download-manager/jqui/theme/jquery-ui.css?ver=4.2.4'
                  type='text/css' media='all'/>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('.popup-with-form').magnificPopup({
                        type: 'inline',
                        preloader: false,
                        focus: '#name',
                        // When elemened is focused, some mobile browsers in some cases zoom in
                        // It looks not nice, so we disable it:
                        callbacks: {
                            beforeOpen: function () {
                                if (jQuery(window).width() < 700) {
                                    this.st.focus = false;
                                } else {
                                    this.st.focus = '#name';
                                }
                            }
                        }
                    });
                    botonVar = '<div class=" " style="float: left; padding: 10px">' +
                        '<input type="submit" name="publish" id="publish"' +
                        ' class="button button-primary button-large  btn-subir-archivos" value="Subir archivos">' +
                        '</div>';
                    jQuery('.mostrarcategorias .navbar-header').append(botonVar)
                });
                <?php
                    global $current_user;
                    get_currentuserinfo();
                    echo "var user ='"  . $current_user->user_login . "';";
                ?>
            </script>
            <style>
                .input-group {
                    display: none !important;
                }

                #wpdm-activate-shop, #ftabs .ui-tabs-nav, #tabs .ui-tabs-nav, #lock-options, #package-icons {
                    display: none
                }

                #link_label_row, #stock_row, #downliad_limit_row, #view_count_row {
                    display: none
                }

                .margen0 {
                    padding: 0 !important;
                    margin: 10px 0 0 0;
                }

                .margentabla {
                    border: 1px solid #e7e7e7;
                    padding: 10px;
                    border-radius: 5px;
                    background-color: #f5f5f5;
                    -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, 0.075);
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.075);
                }

                .plupload-browse-button {
                    width: 100%;
                }

                #header {
                    height: 55px !important;
                }

            </style>
            <div class="w3eden">
                <!--<div class="col-md-12 col-sm-12 col-xs-12">
                    <input type="submit" name="publish" id="publish"
                           class="button button-primary button-large  btn-subir-archivos" value="Subir archivos">
                </div>-->
                <div class="subir-archiuvos col-md-12 col-sm-12 col-xs-12 margentabla ">
                    <div class="col-md-8 col-sm-12 col-xs-12 margen0">
                        <div class="col-md-12 col-sm-12 col-xs-12 margen0">
                            <div id="post-body-content" style="position: relative;">
                                <div id="titlediv">
                                    <div id="titlewrap">
                                        <input type="text" name="post_title" size="30" value="" id="title"
                                               spellcheck="true"
                                               placeholder="Introduce el título aquí">
                                    </div>
                                </div>

                                <div id="postdivrich" class="postarea wp-editor-expand">
                                    <div>
                                        <label for="content">Descripción</label>
                                    <textarea class="wp-editor-area" style="height:150px; margin: 5px; padding: 5px 0;"
                                              cols="40" name="content" id="content"
                                              aria-hidden="false"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12 margen0">
                            <?php
                            // interface de agregar archivo
                            include(ABSPATH . "wp-content/plugins/download-manager/tpls/metaboxes/attach-file.php");

                            ?>

                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 col-xs-12 margen0">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <?php
                            // pone categorias
                            require_once(ABSPATH . 'wp-content/plugins/misiva-mini/recursos/meta-boxes-misiva.php');
                            require_once(ABSPATH . 'wp-admin/includes/template.php');

                            $post = (object)array('ID' => '4444');
                            $box = array('args' => array('taxonomy' => 'wpdmcategory'));

                            post_categories_meta_box($post, $box);

                            //remove_meta_box( 'wpdmcategory', 'slides', 'side' );
                            //add_meta_box('wpdmcategory',  'Slide Image' , 'post_thumbnail_meta_box', 'slides', 'normal', 'high');

                            $file = get_post_meta($post->ID, "_filedata", true);
                            //fin  pone categorias
                            ?>
                            <div id="delete-action">
                            </div>
                            <div id="major-publishing-actions  ">


                                <div id="publishing-action row col-md-12 col-sm-12 col-xs-12 margen0">
                                    <div class="col-md-6 col-sm-6 col-xs-12 margen0">
                                        <span class="messageSave"></span>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-12 pull-right margen0">
                                        <input type="submit" value="Publicar"
                                               class="button button-primary button-large  btn-subir-archivos pull-right"
                                               id="publicar"
                                               name="publicar">
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <?php
                                // interface que muestra el archivo subido
                                include(ABSPATH . "wp-content/plugins/download-manager/tpls/metaboxes/attached-files.php");
                                ?>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12 hidden">
                                <?php
                                // interface de parametros que la vamos a usar oculta
                                //                            include(ABSPATH . "wp-content/plugins/download-manager/tpls/metaboxes/package-settings.php");
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php

        } else {
            echo "No log ";
        }
    }

// Widget Backend
    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('', 'wpb_widget_domain');
        }
// Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <?php
    }

// Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
} // Class wpb_widget ends here

// Register and load the widget
function wpb_load_widget()
{
    register_widget('wpb_widget');
}

add_action('widgets_init', 'wpb_load_widget');
/*Fin Misiva Widget */
/************************/
?>
