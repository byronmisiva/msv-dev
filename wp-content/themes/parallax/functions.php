<?php

add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 ); 
add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );  
 # Wordpress 2.8 a 3.0: 
remove_action( 'wp_version_check', 'wp_version_check' ); 
remove_action( 'admin_init', '_maybe_update_core' ); 
add_filter( 'pre_transient_update_core', create_function( '$a', "return null;" ) );  
 # Wordpress 3.0: 
add_filter( 'pre_site_transient_update_core', create_function( '$a', "return null;" ) );

/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 						DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 * 
 *  					Copyright (C) Themify
 * 						http://themify.me
 *
 ***************************************************************************/

$theme_includes = apply_filters( 'themify_theme_includes',
	array(	'themify/themify-database.php',
			'themify/class-themify-config.php',
			'themify/themify-utils.php',
			'themify/themify-config.php',
			'themify/themify-modules.php',
			'theme-options.php',
			'theme-modules.php',
			'theme-functions.php',
			'custom-modules.php',
			'custom-functions.php',
			'theme-class.php',
			'themify/themify-widgets.php' ));
			
foreach ( $theme_includes as $include ) { locate_template( $include, true ); }

/**********************************************************************************************************
 * 
 * Do not edit this file.
 * To add custom PHP functions to the theme, create a new 'custom-functions.php' file in the theme folder.
 * 
***********************************************************************************************************/
?>