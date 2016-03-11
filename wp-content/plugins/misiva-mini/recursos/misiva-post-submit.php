<?php


if (!defined('ABSPATH')) die('!');
//Get Really Simple Guest Post submitted form
ob_start();

$title = $_REQUEST['titulo'];
$description = $_REQUEST['content'];
$cat =  $_REQUEST['jsonCategorias'];
$archivo = array();
$archivo[] =  $_REQUEST['archivo'];



//Load WordPress
//require($path);
$current_user = wp_get_current_user();
//Verify the form fields
//if (!wp_verify_nonce($nonce)) die('Security check');
$authorid = $current_user->ID;
//Post Properties
$new_post = array(
    'post_title' => $title,
    'post_content' => $description,
    'post_status' => 'publish',           // Choose: publish, preview, future, draft, etc.
    'post_type' => 'wpdmpro',  //'post',page' or use a custom post type if you want to
    'post_author' => $authorid, //Author ID
    'comment_status' => 'closed', //Author ID
    'ping_status' => 'closed' //Author ID
);
//save the new post
$post_id = wp_insert_post($new_post);
$ret = wp_set_post_terms($post_id, $cat, 'wpdmcategory' );

/* Insert Form data into Custom Fields */
add_post_meta($post_id, '__wpdm_individual_file_download','1', true);
add_post_meta($post_id, '__wpdm_page_template',  '544fef1fd30cc', true);
add_post_meta($post_id, '__wpdm_template', '545917f8343bb', true);
$acceso1 = array();
$acceso1[] = 'guest';
add_post_meta($post_id, '__wpdm_access', $acceso1);

update_post_meta($post_id, '__wpdm_files',  $archivo );

$fileinfo = array ($archivo[0]);
//$fileinfo = [$archivo[0]];
$fileinfo[$archivo[0]]['title'] = '' ;
$fileinfo[$archivo[0]]['password'] = '' ;

//add_post_meta($post_id, '__wpdm_fileinfo' ,$fileinfo);


add_post_meta($post_id, '__wpdm_package_dir','', true);
add_post_meta($post_id, '_edit_lock','1442605134:18', true);
add_post_meta($post_id, '_edit_last','18', true);
add_post_meta($post_id, '__wpdm_publish_date','', true);
add_post_meta($post_id, '__wpdm_expire_date','', true);
add_post_meta($post_id, '__wpdm_version','', true);
add_post_meta($post_id, '__wpdm_link_label','', true);
add_post_meta($post_id, '__wpdm_quota','', true);
add_post_meta($post_id, '__wpdm_download_limit_per_user','', true);
add_post_meta($post_id, '__wpdm_view_count','19', true);
add_post_meta($post_id, '__wpdm_download_count','1', true);
add_post_meta($post_id, '__wpdm_package_size','661.97 KB', true);
add_post_meta($post_id, '__wpdm_password','', true);
add_post_meta($post_id, '__wpdm_password_usage_limit','', true);
add_post_meta($post_id, '__wpdm_linkedin_message','', true);
add_post_meta($post_id, '__wpdm_linkedin_url','', true);
add_post_meta($post_id, '__wpdm_tweet_message','', true);
add_post_meta($post_id, '__wpdm_google_plus_1','', true);
add_post_meta($post_id, '__wpdm_facebook_like','', true);
add_post_meta($post_id, '__wpdm_email_lock_idl','0', true);
add_post_meta($post_id, '__wpdm_icon','', true);
add_post_meta($post_id, '__wpdm_masterkey',uniqid(), true);
add_post_meta($post_id, '__wpdm_package_size_b','677854', true);



echo "OK"
?>