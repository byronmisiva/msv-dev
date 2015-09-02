<?php
/*
 * To add custom PHP functions to the theme, create a new 'custom-functions.php' file in the theme folder. 
 * They will be added to the theme automatically.
 */

/////// Actions ////////
add_action( 'wp_head', 'themify_ie_enhancements' );
add_action( 'wp_head', 'themify_viewport_tag' );
add_action( 'wp_head', 'themify_ie_standards_compliant');
add_action( 'wp_footer', 'themify_ie_skrollr', 20 );
add_action( 'wp_enqueue_scripts', 'themify_theme_enqueue_scripts');

// Register Custom Menu Function - Action
add_action( 'init', 'themify_register_custom_nav');

/////// Filters ////////
add_filter('themify_default_post_layout_condition', 'themify_theme_default_post_layout_condition', 12);
add_filter('themify_default_post_layout', 'themify_theme_default_post_layout', 12);
add_filter('themify_default_layout_condition', 'themify_theme_default_layout_condition', 12);
add_filter('themify_default_layout', 'themify_theme_default_layout', 12);

function themify_theme_default_post_layout_condition($condition) {
	global $themify;
	return $condition || is_tax('portfolio-category');
}

function themify_theme_default_post_layout() {
	global $themify;
	// get default layout
	$class = $themify->post_layout;
	if('portfolio' == $themify->query_post_type) {
		$class = themify_check('portfolio_layout') ? themify_get('portfolio_layout') : themify_get('setting-default_post_layout');
	} elseif (is_tax('portfolio-category')) {
		$class = themify_check('setting-default_portfolio_index_post_layout')? themify_get('setting-default_portfolio_index_post_layout') : 'list-post';
	}
	return $class;
}

/**
 * Changes condition to filter layout class
 * @param bool $condition
 * @return bool
 */
function themify_theme_default_layout_condition($condition) {
	global $themify;
	// if layout is not set or is the home page and front page displays is set to latest posts 
	return $condition || (is_home() && 'posts' == get_option('show_on_front')) || '' != $themify->query_category || is_tax('portfolio-category') || is_singular('portfolio');
}
/**
 * Returns modified layout class
 * @param string $class Original body class
 * @return string
 */
function themify_theme_default_layout($class) {
	global $themify;
	// get default layout
	$class = $themify->layout;
	if (is_tax('portfolio-category')) {
		$class = themify_check('setting-default_portfolio_index_layout')? themify_get('setting-default_portfolio_index_layout') : 'sidebar-none';
	}
	return $class;
}

/**
 * Enqueue Stylesheets and Scripts
 */
function themify_theme_enqueue_scripts(){
	global $wp_query;
	
	// Themify base styling
	wp_enqueue_style( 'theme-style', get_stylesheet_uri(), array(), wp_get_theme()->display('Version'));

	// Themify Media Queries CSS
	wp_enqueue_style( 'themify-media-queries', THEME_URI . '/media-queries.css');
	
	// User stylesheet
	if(is_file(TEMPLATEPATH . "/custom_style.css"))
		wp_enqueue_style( 'custom-style', THEME_URI . '/custom_style.css');
	
	//Google Web Fonts embedding
	wp_enqueue_style( 'google-fonts', 'http://fonts.googleapis.com/css?family=Crete+Round|Vidaloka|Alice');

	// Enqueue scripts ////////////////////////////////////////////
	$scripts = array('isotope', 'infinitescroll', 'backstretch');
	foreach ($scripts as $js) {
		wp_enqueue_script( 'themify-'.$js, THEME_URI.'/js/'.$js.'.js', array('jquery'), false, true );
	}

	// Check scrolling effect
	$scrollingEffect = 'enabled';
	$scrollingEffectType = themify_get('section_parallax_effect') != '' ? 
							themify_get('section_parallax_effect') : 'effect2';
	if( ( themify_get('setting-scrolling_effect_mobile_exclude') != 'on' && themify_is_mobile() ) 
		|| themify_check('setting-scrolling_effect_all_disabled') ) {
		$scrollingEffect = 'disabled';
	}

	// Check transition Effect, only enqueue skroll when transition active
	if ( ( themify_get('setting-transition_effect_mobile_exclude') != 'on' && themify_is_mobile() ) 
		|| themify_check('setting-transition_effect_all_disabled') ) {
		// nothing
	} else {
		wp_enqueue_script( 'themify-skrollr', THEME_URI.'/js/skrollr.js', array('jquery'), false, true );
	}

	if ( $scrollingEffectType == 'effect2' ) 
		wp_enqueue_script( 'themify-parallax', THEME_URI.'/js/jquery.parallax.js', array('jquery'), false, true );
	
	wp_enqueue_script( 'bootstrap', THEME_URI.'/js/bootstrap.js', array('jquery'), false, true );
	
	// Initialize carousel
	wp_enqueue_script( 'themify-carousel-js' );
	
	// Themify internal scripts
	wp_enqueue_script( 'theme-script',	THEME_URI . '/js/themify.script.js', array('jquery', 'themify-backstretch'), false, true );

	//Themify Gallery
	wp_enqueue_script( 'themify-gallery', THEMIFY_URI . '/js/themify.gallery.js', array('jquery'), false, true );

	// Get auto infinite scroll setting
	$autoinfinite = '';
	if ( ! themify_get( 'setting-autoinfinite' ) ) {
		$autoinfinite = 'auto';
	}

	//Inject variable values in gallery script
	wp_localize_script( 'theme-script', 'themifyScript', array(
		'lightbox' => themify_lightbox_vars_init(),
		'lightboxContext' => apply_filters('themify_lightbox_context', '#pagewrap'),
		'loadingImg'   	=> THEME_URI . '/images/loading.gif',
		'maxPages'	   	=> $wp_query->max_num_pages,
		'autoInfinite' 	=> $autoinfinite,
		'fixedHeader'	=> apply_filters('themify_fixed_header', true),
		'ajaxurl'	=> admin_url( 'admin-ajax.php' ),
		'load_nonce' => wp_create_nonce( 'theme_load_nonce' ),
		'transitionSetup' => themify_get_transition_setup(), // transition flyIn settings
		'scrollingEffect' => $scrollingEffect,
		'scrollingEffectType' => $scrollingEffectType // Overlapping section effect | background scrolling
	));

	// Header gallery
	wp_enqueue_script( 'gallery-script', THEME_URI . '/js/themify.gallery.js', array('jquery'), false, true );

	//Inject variable values in gallery script
	wp_localize_script( 'gallery-script', 'themifyVars', array(
			'play'		=> (!themify_get('setting-footer_slider_auto'))? 'yes' : themify_get('setting-footer_slider_auto'),
			'autoplay'	=> (!themify_get('setting-footer_slider_autotimeout'))? 5 : themify_get('setting-footer_slider_autotimeout'),
			'speed'		=> (!themify_get('setting-footer_slider_speed'))? 500 : themify_get('setting-footer_slider_speed')
		)
	);
	
	//WordPress internal script to move the comment box to the right place when replying to a user
	if ( is_single() || is_page() ) wp_enqueue_script( 'comment-reply' );
	
}

/**
 * Add Skrollr inline tags
 */
function themify_ie_skrollr() {
	echo '
	<!--[if lt IE 9]>
		<script type="text/javascript" src="' . THEME_URI . '/js/skrollr.ie.js">
	</script>
	<![endif]-->
	';
};

/**
 * Add JavaScript files if IE version is lower than 9
 */
function themify_ie_enhancements(){
	echo '
	<!-- media-queries.js -->
	<!--[if lt IE 9]>
		<script src="' . THEME_URI . '/js/respond.js"></script>
	<![endif]-->

	<!-- jquery-extra-selectors.js -->
	<!--[if lt IE 9]>
		<script src="' . THEME_URI . '/js/jquery-extra-selectors.js"></script>
	<![endif]-->
	
	<!-- html5.js -->
	<!--[if lt IE 9]>
		<script src="'.themify_https_esc('http://html5shim.googlecode.com/svn/trunk/html5.js').'"></script>
	<![endif]-->
	';
}

/**
 * Add viewport tag for responsive layouts
 */
function themify_viewport_tag(){
	echo "\n".'<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">'."\n";
}

/**
 * Make IE behave like a standards-compliant browser
 */
function themify_ie_standards_compliant() {
	echo '
	<!--[if lt IE 9]>
	<script src="'.themify_https_esc('http://s3.amazonaws.com/nwapi/nwmatcher/nwmatcher-1.2.5-min.js').'"></script>
	<script type="text/javascript" src="'.themify_https_esc('http://cdnjs.cloudflare.com/ajax/libs/selectivizr/1.0.2/selectivizr-min.js').'"></script> 
	<![endif]-->
	';
}

/**
 * Custom Post Type Background Gallery
/***************************************************************************/
class Themify_Background_Gallery{

	/**
	 * Custom post type key
	 * @var String
	 */
	static $cpt = 'background-gallery';

	function __construct(){}

	function create_controller(){
		global $post;

		/** ID of default background gallery
		 * @var String|Number */
		$bggallery_id = $this->get_bggallery_id();

		// If we still don't have a background gallery ID, do nothing.
		if( !$bggallery_id || 'default' == $bggallery_id ) return;

		$images = get_posts(array(
			'post__in' => $bggallery_id,
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'orderby' => 'post__in',
			'order' => 'ASC'
		));

		if($images){
			echo '
			<div id="gallery-controller">
				<div class="slider">
					<ul class="slides clearfix">';
			foreach( $images as $image ){
				// Get large size for background
				$image_data = wp_get_attachment_image_src( $image->ID, 'large' );
				echo '<li data-bg="',$image_data[0],'"><span class="slider-dot"></span></li>';
			}
			echo '		</ul>
						<div class="carousel-nav-wrap">
							<a href="#" class="carousel-prev" style="display: block; ">&lsaquo;</a>
							<a href="#" class="carousel-next" style="display: block; ">&rsaquo;</a>
						</div>

				</div>
			</div>
			<!-- /gallery-controller -->';
		}
	}

	/**
	 * Displays a list of current background galleries
	 * @return array of name/value arrays
	 */
	function get_backgrounds(){
		$bgs = get_posts( array(
			'post_type' => self::$cpt,
			'orderby' => 'title',
			'order' => 'ASC',
			'numberposts' => -1
		));
		$backgrounds = array();
		$backgrounds[] = array( 'name' => '', 'value' => 'default');
		foreach($bgs as $index => $background){
			$backgrounds[] = array(
				'name' => $background->post_title,
				'value' => $background->ID
			);
		}
		return $backgrounds;
	}

	/**
	 * Return the background gallery ID if one of the following is found:
	 * - bg gallery defined in theme settings
	 * - bg gallery defined in Themify custom panel, either in post or page
	 * @return String|Mixed Background Gallery ID or 'default'
	 */
	function get_bggallery_id() {
		global $post;
		$sc_gallery = preg_replace('#\[gallery(.*)ids="([0-9|,]*)"(.*)\]#i', '$2', themify_get('background_gallery'));

		// If it's a page or post, check if a gallery was specified in custom field

		$image_ids = explode(',', str_replace(' ', '', $sc_gallery));

		return $image_ids;
	}
}
// Start Background Gallery
global $themify_bg_gallery;
$themify_bg_gallery = new Themify_Background_Gallery();

/* Custom Write Panels
/***************************************************************************/

if ( ! function_exists('themify_get_google_web_fonts_list') ) {
	/**
	 * Returns a list of Google Web Fonts
	 * @return array
	 * @since 1.0.0
	 */
	function themify_get_google_web_fonts_list() {
		$google_fonts_list = array(
			array('value' => '', 'name' => ''),
			array(
				'value' => '',
				'name' => '--- '.__('Google Fonts', 'themify').' ---'
			)
		);
		foreach( themify_get_google_font_lists() as $font ) {
			$google_fonts_list[] = array(
				'value' => $font,
				'name' => $font
			);
		}
		return apply_filters('themify_get_google_web_fonts_list', $google_fonts_list);
	}
}

if ( ! function_exists('themify_get_web_safe_font_list') ) {
	/**
	 * Returns a list of web safe fonts
	 * @return array
	 * @since 1.0.0
	 */
	function themify_get_web_safe_font_list($only_names = false) {
		$web_safe_font_names = array(
			'Arial, Helvetica, sans-serif',
			'Verdana, Geneva, sans-serif',
			'Georgia, \'Times New Roman\', Times, serif',
			'\'Times New Roman\', Times, serif',
			'Tahoma, Geneva, sans-serif',
			'\'Trebuchet MS\', Arial, Helvetica, sans-serif',
			'Palatino, \'Palatino Linotype\', \'Book Antiqua\', serif',
			'\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'
		);

		if( ! $only_names ) {
			$web_safe_fonts = array(
				array('value' => 'default', 'name' => '', 'selected' => true),
				array('value' => '', 'name' => '--- '.__('Web Safe Fonts', 'themify').' ---')
			);
			foreach( $web_safe_font_names as $font ) {
				$web_safe_fonts[] = array(
					'value' => $font,
					'name' => str_replace( '\'', '"', $font )
				);
			}
		} else {
			$web_safe_fonts = $web_safe_font_names;
		}

		return apply_filters( 'themify_get_web_safe_font_list', $web_safe_fonts );
	}
}

// Return Google Web Fonts list
$google_fonts_list = themify_get_google_web_fonts_list();
// Return Web Safe Fonts list
$fonts_list = themify_get_web_safe_font_list();

///////////////////////////////////////
// Setup Write Panel Options
///////////////////////////////////////

/** Definition for tri-state hide meta buttons
 *  @var array */
$states = array(
	array(
		'name' => __('Hide', 'themify'),
		'value' => 'yes',
		'icon' => THEMIFY_URI . '/img/ddbtn-check.png',
		'title' => __('Hide this meta', 'themify')
	),
	array(
		'name' => __('Do not hide', 'themify'),
		'value' => 'no',
		'icon' => THEMIFY_URI . '/img/ddbtn-cross.png',
		'title' => __('Show this meta', 'themify')
	),
	array(
		'name' => __('Theme default', 'themify'),
		'value' => '',
		'icon' => THEMIFY_URI . '/img/ddbtn-blank.png',
		'title' => __('Use theme settings', 'themify'),
		'default' => true
	)
);
// Common Fields ///////////////////////////////////////
$post_image = array(
	'name' 		=> 'post_image',
	'title' 	=> __('Featured Image', 'themify'),
	'description' => '',
	'type' 		=> 'image',
	'meta'		=> array(),
);
$featured_image_size = array(
	'name'	=>	'feature_size',
	'title'	=>	__('Image Size', 'themify'),
	'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="admin.php?page=themify_regenerate-thumbnails">Regenerated</a>', 'themify'),
	'type'		 =>	'featimgdropdown'
);
$external_link = array(
	'name' 		=> 'external_link',	
	'title' 		=> __('External Link', 'themify'), 	
	'description' => __('Link Featured Image and Post Title to external URL', 'themify'), 				
	'type' 		=> 'textbox',			
	'meta'		=> array()
);
$post_image_dimensions = array(
	// Image Width
	array(
		'name' => 'image_width',	
		'label' => __('width', 'themify'), 
		'description' => '',
		'type' => 'textbox',			
		'meta' => array('size'=>'small'),
		'before' => '',
		'after' => '',
	),
	// Image Height
	array(
		'name' => 'image_height',
		'label' => __('height', 'themify'),
		'type' => 'textbox',						
		'meta' => array('size'=>'small'),
		'before' => '',
		'after' => '',
	)
);
$nav_menus = array(array('name' => '', 'value' => '', 'selected' => true));
foreach(get_terms('nav_menu') as $menu){
  $nav_menus[] = array('name' => $menu->name, 'value' => $menu->term_id);
}
/** 
 * Post Meta Box Options
 * @var array */
$post_meta_box_options = array(
	// Layout
	array(
		'name' 	=> 'layout',	
		'title' => __('Sidebar Option', 'themify'), 	
		'description' => '', 				
		'type' 	=> 'layout',
		'show_title' => true,			
		'meta'	=> array(
			array('value' => 'default', 'img' => 'images/layout-icons/default.png', 'selected' => true, 'title' => __('Default', 'themify')),
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
		)
	),
	// Post Image
	array(
		'name' 		=> "post_image",
		'title' 		=> __('Featured Image', 'themify'),
		'description' => '',
		"type" 		=> "image",
		'meta'		=> array()
	),
   	// Featured Image Size
	array(
		'name'	=>	'feature_size',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="admin.php?page=themify_regenerate-thumbnails">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown'
		),
	// Multi field: Image Dimension
	array(
		'type' => 'multi',
		'name' => 'image_dimensions',
		'title' => __('Image Dimension', 'themify'),
		'meta' => array(
			'fields' => $post_image_dimensions,
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// Hide Post Title
	array(
		  'name' 		=> "hide_post_title",	
		  'title' 		=> __('Hide Post Title', 'themify'), 	
		  'description' => '',		
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
				array('value' => 'default', 'name' => '', 'selected' => true),
				array('value' => 'yes', 'name' => __('Yes', 'themify')),
				array('value' => 'no',	'name' => __('No', 'themify'))
			)			
		),
	// Unlink Post Title
	array(
		'name' 		=> "unlink_post_title",	
		'title' 	=> __('Unlink Post Title', 'themify'), 	
		'description' => __('Display the post title without link', 'themify'), 				
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Meta
	array(
		'name' 		=> 'hide_meta_multi',	
		'title' 	=> __('Hide Post Meta', 'themify'), 	
		'description' => '', 				
		'type' 		=> 'multi',			
		'meta'		=>  array (
			'fields' => array(
				array(
					'name' => 'hide_meta_all',
					'title' => __('Hide All', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'main' => true,
					'disable_value' => 'yes'
				),
				array(
					'name' => 'hide_meta_author',
					'title' => __('Author', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true
				),
				array(
					'name' => 'hide_meta_category',
					'title' => __('Category', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true
				),
				array(
					'name' => 'hide_meta_comment',
					'title' => __('Comment', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true
				),
				array(
					'name' => 'hide_meta_tag',
					'title' => __('Tag', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true
				),
			),
			'description' => '',
			'before' => '',
			'after' => '',
			'separator' => ''
		),
	),
	// Hide Post Date
	array(
		'name' 		=> "hide_post_date",	
		'title' 	=> __('Hide Post Date', 'themify'), 	
		'description' => '', 				
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Image
	array(
		'name' 		=> "hide_post_image",	
		'title' 	=> __('Hide Featured Image', 'themify'), 	
		'description' => '', 				
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		),
		'toggle'	=> array('media-image-toggle')
	),
	// Unlink Post Image
	array(
		'name' 		=> "unlink_post_image",	
		'title' 		=> __('Unlink Featured Image', 'themify'), 	
		'description' => __('Display the Featured Image without link', 'themify'), 				
		'type' 		=> 'dropdown',			
		'meta'		=> array(
				array('value' => 'default', 'name' => '', 'selected' => true),
				array('value' => 'yes', 'name' => __('Yes', 'themify')),
				array('value' => 'no',	'name' => __('No', 'themify'))
		),
		'toggle'	=> array('media-image-toggle')
	),
	// Video URL
	array(
		'name' 		=> 'video_url',
		'title' 		=> __('Video URL', 'themify'),
		'description' => __('Video embed URL such as YouTube or Vimeo video url (<a href="http://themify.me/docs/video-embeds">details</a>)', 'themify'),
		'type' 		=> 'textbox',
		'meta'		=> array()
	),
	// External Link
	array(
		'name' 		=> 'external_link',	
		'title' 		=> __('External Link', 'themify'), 	
		'description' => __('Link Featured Image and Post Title to external URL', 'themify'), 				
		'type' 		=> 'textbox',			
		'meta'		=> array()
	),
	// Lightbox Link + Zoom icon
	array(
		'name' 	=> 'multi_lightbox_link',	
		'title' => __('Lightbox Link', 'themify'), 	
		'description' => '', 				
		'type' 	=> 'multi',			
		'meta'	=> array(
			'fields' => array(
		  		// Lightbox link field
		  		array(
					'name' 	=> 'lightbox_link',
					'label' => '',
					'description' => __('Link Featured Image and Post Title to lightbox image, video or iframe URL <br/>(<a href="http://themify.me/docs/lightbox">learn more</a>)', 'themify'),
					'type' 	=> 'textbox',
					'meta'	=> array(),
					'before' => '',
					'after' => '',
				),
				array(
					'name' 		=> 'iframe_url',
					'label' 		=> __('iFrame URL', 'themify'),
					'description' => '',
					'type' 		=> 'checkbox',
					'before' => '',
					'after' => '',
				),
				array(
					'name' 		=> 'lightbox_icon',
					'label' 		=> __('Add zoom icon on lightbox link', 'themify'),
					'description' => '',
					'type' 		=> 'checkbox',
					'before' => '',
					'after' => '',
				)
			),
			'description' => '',
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	)
);
$post_meta_box_styles = array(
	// Separator
	array(
		'name' => 'separator',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html'=>'<h4>'.__('Custom Header Background').'</h4><hr class="meta_fields_separator"/>'),
	),
	// Custom header background for page //////////////////
	// Backgroud image
	array(
		'name' 	=> 'background_image',
		'title'		=> __('Background Image', 'themify'),
		'type' 	=> 'image',
		'description' => '',
		'meta'	=> array(),
		'before' => '',
		'after' => ''
	),
	// Background repeat
	array(
		'name' 		=> 'background_repeat',
		'title'		=> __('Background Repeat', 'themify'),
		'description'	=> '',
		'type' 		=> 'dropdown',
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'repeat', 'name' => __('Repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify'))
		)
	),
	// Select Background Gallery
	array(
		'name' 		=> 'background_gallery',
		'title'		=> __('Header Slider', 'themify'),
		'description' => '',
		'type' 		=> 'gallery_shortcode',
	),
);

/** 
 * Page Meta Box Options
 * @var array */
$page_meta_box_options = array(
  	// Page Layout
	array(
		'name' 		=> 'page_layout',
		'title'		=> __('Sidebar Option', 'themify'),
		'description'	=> '',
		'type'		=> 'layout',
		'show_title' => true,
		'meta'	=> array(
			array('value' => 'default',	'img' => 'images/layout-icons/default.png', 'selected' => true, 'title' => __('Default', 'themify')),
			array('value' => 'sidebar1', 'img' => 'images/layout-icons/sidebar1.png', 'title' => __('Sidebar Right', 'themify')),
			array('value' => 'sidebar1 sidebar-left', 'img' => 'images/layout-icons/sidebar1-left.png', 'title' => __('Sidebar Left', 'themify')),
			array('value' => 'sidebar-none', 'img' => 'images/layout-icons/sidebar-none.png', 'title' => __('No Sidebar ', 'themify'))
		),
	),
	// Hide page title
	array(
		  'name' 		=> 'hide_page_title',
		  'title'		=> __('Hide Page Title', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Custom menu for page
	array(
		'name' 		=> 'custom_menu',
		'title'		=> __('Custom Menu', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> $nav_menus
	),
	// Separator
	array(
		'name' => 'separator',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html'=>'<h4>'.__('Header').'</h4><hr class="meta_fields_separator"/>'),
	),
	// Custom header background for page //////////////////
	// Header image
	array(
		'name' 	=> 'background_image',
		'title'		=> __('Header Image', 'themify'),
		'type' 	=> 'image',
		'description' => '',
		'meta'	=> array(),
		'before' => '',
		'after' => ''
	),
	// Background repeat
	array(
		'name' 		=> 'background_repeat',
		'title'		=> __('Header Image Repeat', 'themify'),
		'description'	=> '',
		'type' 		=> 'dropdown',
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'repeat', 'name' => __('Repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify'))
		)
	),
	// Select Background Gallery
	array(
		'name' 		=> 'background_gallery',
		'title'		=> __('Header Slider', 'themify'),
		'description' => '',
		'type' 		=> 'gallery_shortcode',
	),
);
$query_post_meta_box = array(
	// Post Category
	array(
		'name' 		=> 'query_category',
		'title'		=> __('Post Category', 'themify'),
		'description'	=> __('Select a category or enter multiple category IDs (eg. 2,5,6). Enter 0 to display all categories.', 'themify'),
		'type'		=> 'query_category',
		'meta'		=> array()
	),
	// Descending or Ascending Order for Posts
	array(
		'name' 		=> 'order',
		'title'		=> __('Order', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
			array('name' => __('Ascending', 'themify'), 'value' => 'asc')
		)
	),
	// Criteria to Order By
	array(
		'name' 		=> 'orderby',
		'title'		=> __('Order By', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Date', 'themify'), 'value' => 'content', 'selected' => true),
			array('name' => __('Random', 'themify'), 'value' => 'rand'),
			array('name' => __('Author', 'themify'), 'value' => 'author'),
			array('name' => __('Post Title', 'themify'), 'value' => 'title'),
			array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
			array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
			array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
			array('name' => __('Post ID', 'themify'), 'value' => 'ID')
		)
	),
	// Post Layout
	array(
		  'name' 		=> 'layout',
		  'title'		=> __('Post Layout', 'themify'),
		  'description'	=> '',
		  'type'		=> 'layout',
		  'show_title' => true,
		  'meta'		=> array(
				array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'selected' => true, 'title' => __('List Post', 'themify')),
				array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify')),
				array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
				array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
				array('value' => 'grid2-thumb', 'img' => 'images/layout-icons/grid2-thumb.png', 'title' => __('Grid 2 Thumb', 'themify'))
			)
		),
	// Posts Per Page
	array(
		  'name' 		=> 'posts_per_page',
		  'title'		=> __('Posts per page', 'themify'),
		  'description'	=> '',
		  'type'		=> 'textbox',
		  'meta'		=> array('size' => 'small')
		),
	
	// Display Content
	array(
		'name' 		=> 'display_content',
		'title'		=> __('Display Content', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Full Content', 'themify'),'value'=>'content','selected'=>true),
			array('name' => __('Excerpt', 'themify'),'value'=>'excerpt'),
			array('name' => __('None', 'themify'),'value'=>'none')
		)
	),
	// Featured Image Size
	array(
		'name'	=>	'feature_size_page',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="admin.php?page=themify_regenerate-thumbnails">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown'
		),
	array(
		'type' => 'multi',
		'name' => '_post_image_dimensions',
		'title' => __('Image Dimensions', 'themify'),
		'meta' => array(
			'fields' => array(
				// Image Width
				array(
				  'name' 		=> 'image_width',	
				  'label' => __('width', 'themify'),
				  'description' => '', 				
				  'type' 		=> 'textbox',			
				  'meta'		=> array('size'=>'small')			
				),
				// Image Height
				array(
				  'name' 		=> 'image_height',	
				  'label' => __('height', 'themify'),
				  'description' => '', 				
				  'type' 		=> 'textbox',			
				  'meta'		=> array('size'=>'small')			
				),
			),
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// Hide Title
	array(
		  'name' 		=> 'hide_title',
		  'title'		=> __('Hide Post Title', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Unlink Post Title
	array(
		  'name' 		=> 'unlink_title',	
		  'title' 		=> __('Unlink Post Title', 'themify'), 	
		  'description' => __('Unlink post title (it will display the post title without link)', 'themify'), 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)			
		),
	// Hide Post Date
	array(
		  'name' 		=> 'hide_date',
		  'title'		=> __('Hide Post Date', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Hide Post Meta
	array(
		'name' 		=> 'hide_meta_multi',	
		'title' 	=> __('Hide Post Meta', 'themify'), 	
		'description' => '', 				
		'type' 		=> 'multi',			
		'meta'		=>  array (
			'fields' => array(
				array(
					'name' => 'hide_meta_all',
					'title' => __('Hide All', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'main' => true,
					'disable_value' => 'yes',
					'toggle'	=> array('post-toggle', 'portfolio-toggle')
				),
				array(
					'name' => 'hide_meta_author',
					'title' => __('Author', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true,
					'toggle'	=> 'post-toggle'
				),
				array(
					'name' => 'hide_meta_category',
					'title' => __('Category', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true,
					'toggle'	=> 'post-toggle'
				),
				array(
					'name' => 'hide_meta_comment',
					'title' => __('Comment', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true,
					'toggle'	=> 'post-toggle'
				),
				array(
					'name' => 'hide_meta_tag',
					'title' => __('Tag', 'themify'),
					'description' => '',
					'type' => 'dropdownbutton',
					'states' => $states,
					'sub' => true,
					'toggle'	=> 'post-toggle'
				),
			),
			'description' => '',
			'before' => '',
			'after' => '',
			'separator' => ''
		),
	),
	// Media Above/Below Title
	array(
		'name' 		=> 'media_position',
		'title'		=> __('Media Position', 'themify'),
		'description'	=> '',
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'below', 'name' => __('Below Post Title', 'themify')),
			array('value' => 'above', 'name' => __('Above Post Title', 'themify')),
		)
	),
	// Hide Post Image
	array(
		  'name' 		=> 'hide_image',	
		  'title' 		=> __('Hide Featured Image', 'themify'), 	
		  'description' => '', 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Unlink Post Image
	array(
		  'name' 		=> 'unlink_image',	
		  'title' 		=> __('Unlink Featured Image', 'themify'), 	
		  'description' => __('Display the Featured Image without link', 'themify'), 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)			
	),
	// Page Navigation Visibility
	array(
		  'name' 		=> 'hide_navigation',
		  'title'		=> __('Hide Page Navigation', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	)
);

/** 
 * Page Meta Box Options
 * @var array */
$query_portfolio_meta_box = array(
  
	// Query Category
	array(
		'name' 		=> 'portfolio_query_category',
		'title'		=> __('Portfolio Category', 'themify'),
		'description'	=> __('Select a portfolio category or enter multiple portfolio category IDs (eg. 2,5,6). Enter 0 to display all portfolio categories.', 'themify'),
		'type'		=> 'query_category',
		'meta'		=> array('taxonomy' => 'portfolio-category')
	),
	// Descending or Ascending Order for Portfolios
	array(
		'name' 		=> 'portfolio_order',
		'title'		=> __('Order', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
			array('name' => __('Ascending', 'themify'), 'value' => 'asc')
		)
	),
	// Criteria to Order By
	array(
		'name' 		=> 'portfolio_orderby',
		'title'		=> __('Order By', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Date', 'themify'), 'value' => 'content', 'selected' => true),
			array('name' => __('Random', 'themify'), 'value' => 'rand'),
			array('name' => __('Author', 'themify'), 'value' => 'author'),
			array('name' => __('Post Title', 'themify'), 'value' => 'title'),
			array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
			array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
			array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
			array('name' => __('Post ID', 'themify'), 'value' => 'ID')
		)
	),
	// Post Layout
	array(
		  'name' 		=> 'portfolio_layout',
		  'title'		=> __('Portfolio Layout', 'themify'),
		  'description'	=> '',
		  'type'		=> 'layout',
		  'show_title' => true,
		  'meta'		=> array(
				array('value' => 'list-post', 'img' => 'images/layout-icons/list-post.png', 'selected' => true),
				array('value' => 'grid4', 'img' => 'images/layout-icons/grid4.png', 'title' => __('Grid 4', 'themify')),
				array('value' => 'grid3', 'img' => 'images/layout-icons/grid3.png', 'title' => __('Grid 3', 'themify')),
				array('value' => 'grid2', 'img' => 'images/layout-icons/grid2.png', 'title' => __('Grid 2', 'themify')),
				array('value' => 'grid2-thumb', 'img' => 'images/layout-icons/grid2-thumb.png', 'title' => __('Grid 2 Thumb', 'themify'))
			)
		),
	// Posts Per Page
	array(
		  'name' 		=> 'portfolio_posts_per_page',
		  'title'		=> __('Portfolios per page', 'themify'),
		  'description'	=> '',
		  'type'		=> 'textbox',
		  'meta'		=> array('size' => 'small')
		),
	
	// Display Content
	array(
		  'name' 		=> 'portfolio_display_content',
		  'title'		=> __('Display Content', 'themify'),
		  'description'	=> '',
		  'type'		=> 'dropdown',
		  'meta'		=> array(
								array('name' => __('Full Content', 'themify'),'value'=>'content','selected'=>true),
		  						array('name' => __('Excerpt', 'themify'),'value'=>'excerpt'),
								array('name' => __('None', 'themify'),'value'=>'none')
							)
		),
	// Featured Image Size
	array(
		'name'	=>	'portfolio_feature_size_page',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="admin.php?page=themify_regenerate-thumbnails">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown'
		),
	
	// Multi field: Image Dimension
	array(
		'type' => 'multi',
		'name' => '_portfolio_image_dimensions',
		'title' => __('Image Dimensions', 'themify'),
		'meta' => array(
			'fields' => array(
				// Image Width
				array(
				  'name' 		=> 'portfolio_image_width',	
				  'label' => __('width', 'themify'),
				  'description' => '', 				
				  'type' 		=> 'textbox',			
				  'meta'		=> array('size'=>'small')			
				),
				// Image Height
				array(
				  'name' 		=> 'portfolio_image_height',	
				  'label' => __('height', 'themify'),
				  'description' => '', 				
				  'type' 		=> 'textbox',			
				  'meta'		=> array('size'=>'small')			
				),
			),
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// Hide Title
	array(
		  'name' 		=> 'portfolio_hide_title',
		  'title'		=> __('Hide Portfolio Title', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Unlink Post Title
	array(
		  'name' 		=> 'portfolio_unlink_title',	
		  'title' 		=> __('Unlink Portfolio Title', 'themify'), 	
		  'description' => __('Unlink portfolio title (it will display the post title without link)', 'themify'), 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)			
		),
	// Hide Post Date
	array(
		  'name' 		=> 'portfolio_hide_date',
		  'title'		=> __('Hide Portfolio Date', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Hide Post Meta
	array(
		'name' 		=> 'portfolio_hide_meta_all',	
		'title' 	=> __('Hide Portfolio Meta', 'themify'), 	
		'description' => '', 				
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Image
	array(
		  'name' 		=> 'portfolio_hide_image',	
		  'title' 		=> __('Hide Featured Image', 'themify'), 	
		  'description' => '', 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	),
	// Unlink Post Image
	array(
		  'name' 		=> 'portfolio_unlink_image',	
		  'title' 		=> __('Unlink Featured Image', 'themify'), 	
		  'description' => __('Display the Featured Image without link', 'themify'), 				
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)			
	),
	// Page Navigation Visibility
	array(
		  'name' 		=> 'portfolio_hide_navigation',
		  'title'		=> __('Hide Page Navigation', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'yes', 'name' => __('Yes', 'themify')),
			array('value' => 'no',	'name' => __('No', 'themify'))
		)
	)
);

/** 
 * Page Meta Box Options
 * @var array */
$query_section_meta_box = array(
  
	// Query Category
	array(
		'name' 		=> 'section_query_category',
		'title'		=> __('Section Category', 'themify'),
		'description'	=> __('Select a section category or enter multiple section category IDs (eg. 2,5,6). Enter 0 to display all section categories.', 'themify'),
		'type'		=> 'query_category',
		'meta'		=> array('taxonomy' => 'section-category')
	),
	// Descending or Ascending Order for Sections
	array(
		'name' 		=> 'section_order',
		'title'		=> __('Order', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Descending', 'themify'), 'value' => 'desc', 'selected' => true),
			array('name' => __('Ascending', 'themify'), 'value' => 'asc')
		)
	),
	// Criteria to Order By
	array(
		'name' 		=> 'section_orderby',
		'title'		=> __('Order By', 'themify'),
		'description'	=> '',
		'type'		=> 'dropdown',
		'meta'		=> array(
			array('name' => __('Date', 'themify'), 'value' => 'content', 'selected' => true),
			array('name' => __('Random', 'themify'), 'value' => 'rand'),
			array('name' => __('Author', 'themify'), 'value' => 'author'),
			array('name' => __('Post Title', 'themify'), 'value' => 'title'),
			array('name' => __('Comments Number', 'themify'), 'value' => 'comment_count'),
			array('name' => __('Modified Date', 'themify'), 'value' => 'modified'),
			array('name' => __('Post Slug', 'themify'), 'value' => 'name'),
			array('name' => __('Post ID', 'themify'), 'value' => 'ID')
		)
	),
	// Posts Per Page
	array(
		  'name' 		=> 'section_posts_per_page',
		  'title'		=> __('Sections per page', 'themify'),
		  'description'	=> '',
		  'type'		=> 'textbox',
		  'meta'		=> array('size' => 'small')
		),
	// Featured Image Size
	array(
		'name'	=>	'section_feature_size_page',
		'title'	=>	__('Image Size', 'themify'),
		'description' => __('Image sizes can be set at <a href="options-media.php">Media Settings</a> and <a href="admin.php?page=themify_regenerate-thumbnails">Regenerated</a>', 'themify'),
		'type'		 =>	'featimgdropdown'
		),
	// Hide Title
	array(
		  'name' 		=> 'section_hide_title',
		  'title'		=> __('Hide Section Title', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Hide Subtitle
	array(
		  'name' 		=> 'section_hide_subtitle',
		  'title'		=> __('Hide Section Subtitle', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'default', 'name' => '', 'selected' => true),
								array('value' => 'yes', 'name' => __('Yes', 'themify')),
								array('value' => 'no',	'name' => __('No', 'themify'))
							)
		),
	// Parallax Effect
	array(
		  'name' 		=> 'section_parallax_effect',
		  'title'		=> __('Parallax Effect', 'themify'),
		  'description'	=> '',
		  'type' 		=> 'dropdown',			
		  'meta'		=> array(
		  						array('value' => 'effect2', 'name' => __('Background Scrolling', 'themify'), 'selected' => true),
								array('value' => 'effect1',	'name' => __('Overlap Scrolling', 'themify'))
							)
		)
);

/**
 * Post Styles Meta Box Options
 * @var array
 */
$section_meta_box = array(
	// Separator
	array(
		'name' => 'separator_font',
		'title' => '', 
		'description' => '',
		'type' => 'separator',
		'meta' => array('html'=>'<h4>'.__('Section Font').'</h4><hr class="meta_fields_separator"/>'),
	),
	// Multi field: Font
	array(
		'type' => 'multi',
		'name' => 'multi_font',
		'title' => __('Font', 'themify'),
		'meta' => array(
			'fields' => array(
				// Font size
				array(
					'name' => 'font_size',	
					'label' => '',
					'description' => '',
					'type' => 'textbox',			
					'meta' => array('size'=>'small'),
					'before' => '',
					'after' => ''
				),
				// Font size unit
				array(
					'name' 	=> 'font_size_unit',	
					'label' => '',
					'type' 	=> 'dropdown',	
					'meta'	=> array(
						array('value' => 'px', 'name' => __('px', 'themify'), 'selected' => true),
						array('value' => 'em', 'name' => __('em', 'themify'))
					),
					'before' => '',
					'after' => ''
				),
				// Font family
				array(
					'name' 	=> 'font_family',	
					'label' => '',
					'type' 	=> 'dropdown',	
					'meta'	=> array_merge( $fonts_list, $google_fonts_list ),
					'before' => '',
					'after' => '',
				),
			),
			'description' => '',	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// Font Color
	array(
		'name' => 'font_color',
		'title' => __('Font Color', 'themify'), 
		'description' => '',
		'type' => 'color',
		'meta' => array('default'=>null),
	),
	// Link Color
	array(
		'name' => 'link_color',
		'title' => __('Link Color', 'themify'), 
		'description' => '',
		'type' => 'color',
		'meta' => array('default'=>null),
	),
	// Separator
	array(
		'name' => 'separator',
		'title' => '', 
		'description' => '',
		'type' => 'separator',
		'meta' => array('html'=>'<h4>'.__('Section Background').'</h4><hr class="meta_fields_separator"/>'),
	),
	// Background Color
	array(
		'name' => 'background_color',
		'title' => __('Background Color', 'themify'), 
		'description' => '',
		'type' => 'color',
		'meta' => array('default'=>null),
	),
	// Backgroud image
	array(
		'name' 	=> 'background_image',	
		'title' => '',
		'type' 	=> 'image',
		'description' => '',	
		'meta'	=> array(),
		'before' => '',
		'after' => ''
	),
	// Background repeat
	array(
		'name' 		=> 'background_repeat',
		'title'		=> __('Background Repeat', 'themify'),
		'description'	=> '',
		'type' 		=> 'dropdown',			
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'repeat', 'name' => __('Repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify'))
		)
	),
);

/** Portfolio Meta Box Options */
$portfolio_meta_box = array(
	// Feature Image
	$post_image,
	// Gallery Shortcode
	array(
		'name' 		=> 'gallery_shortcode',	
		'title' 	=> __('Gallery', 'themify'),
		'description' => '',			
		'type' 		=> 'gallery_shortcode'
	),
	// Featured Image Size
	$featured_image_size,
	// Multi field: Image Dimension
	array(
		'type' => 'multi',
		'name' => 'image_dimensions',
		'title' => __('Image Dimension', 'themify'),
		'meta' => array(
			'fields' => $post_image_dimensions,
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		),
		'toggle'	=> array('media-image-toggle')
	),
	// Hide Title
	array(
		"name" 		=> "hide_post_title",
		"title"		=> __('Hide Post Title', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)
	),
	// Unlink Post Title
	array(
		"name" 		=> "unlink_post_title",
		"title" 		=> __('Unlink Post Title', 'themify'), 	
		"description" => __('Unlink post title (it will display the post title without link)', 'themify'), 				
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Date
	array(
		"name" 		=> "hide_post_date",
		"title"		=> __('Hide Post Date', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Meta
	array(
		"name" 		=> "hide_post_meta",
		"title"		=> __('Hide Post Meta', 'themify'),
		"description"	=> "",
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)
	),
	// Hide Post Image
	array(
		"name" 		=> "hide_post_image",
		"title" 		=> __('Hide Featured Image', 'themify'), 	
		"description" => "", 				
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)			
	),
	// Unlink Post Image
	array(
		"name" 		=> "unlink_post_image",
		"title" 		=> __('Unlink Featured Image', 'themify'), 	
		"description" => __('Display the Featured Image without link', 'themify'), 				
		"type" 		=> "dropdown",			
		"meta"		=> array(
			array("value" => "default", "name" => "", "selected" => true),
			array("value" => "yes", 'name' => __('Yes', 'themify')),
			array("value" => "no",	'name' => __('No', 'themify'))
		)
	),
	// Video URL
	array(
		'name' 		=> 'video_url',
		'title' 		=> __('Video URL', 'themify'),
		'description' => __('Video embed URL such as YouTube or Vimeo video url (<a href="http://themify.me/docs/video-embeds">details</a>)', 'themify'),
		'type' 		=> 'textbox',
		'meta'		=> array(),
	),
	// External Link
	$external_link,
	// Lightbox Link
	themify_lightbox_link_field(),
	// Shortcode ID
	array(
		'name' 		=> '_post_id_info',	
		'title' 	=> __('Shortcode ID', 'themify'),
		'description' => __('To show this use [portfolio id="%s"]'),			
		'type' 		=> 'post_id_info'
	)
);
$portfolio_meta_box_styles = array(
	// Separator
	array(
		'name' => 'separator',
		'title' => '',
		'description' => '',
		'type' => 'separator',
		'meta' => array('html'=>'<h4>'.__('Custom Header Background').'</h4><hr class="meta_fields_separator"/>'),
	),
	// Custom header background for page //////////////////
	// Backgroud image
	array(
		'name' 	=> 'background_image',
		'title'		=> __('Background Image', 'themify'),
		'type' 	=> 'image',
		'description' => '',
		'meta'	=> array(),
		'before' => '',
		'after' => ''
	),
	// Background repeat
	array(
		'name' 		=> 'background_repeat',
		'title'		=> __('Background Repeat', 'themify'),
		'description'	=> '',
		'type' 		=> 'dropdown',
		'meta'		=> array(
			array('value' => 'default', 'name' => '', 'selected' => true),
			array('value' => 'repeat', 'name' => __('Repeat', 'themify')),
			array('value' => 'fullcover', 'name' => __('Fullcover', 'themify'))
		)
	),
	// Select Background Gallery
	array(
		'name' 		=> 'background_gallery',
		'title'		=> __('Header Slider', 'themify'),
		'description' => '',
		'type' 		=> 'gallery_shortcode',
	),
);

/**
 * Highlight Meta Box Options
 * @var array $highlight_meta_box Options for Themify Custom Panel
 */
$highlight_meta_box = array(
	// Post Image
	$post_image,
   	// Featured Image Size
	$featured_image_size,
	// Image Dimensions
	array(
		'type' => 'multi',
		'name' => 'image_dimensions',
		'title' => __('Image Dimension', 'themify'),
		'meta' => array(
			'fields' => $post_image_dimensions,
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// External Link
	$external_link,
	// Lightbox Link
	themify_lightbox_link_field(),
	// Shortcode ID
	array(
		'name' 		=> 'post_id_info',	
		'title' 	=> __('Shortcode ID', 'themify'),
		'description' => __('To show this use [highlight id="%s"]'),			
		'type' 		=> 'post_id_info'
	)
);

/**
 * Team Meta Box Options
 * @var array $team_meta_box Options for Themify Custom Panel
 */
$team_meta_box = array(
	// Post Image
	$post_image,
   	// Featured Image Size
	$featured_image_size,
	// Image Dimensions
	array(
		'type' => 'multi',
		'name' => 'image_dimensions',
		'title' => __('Image Dimension', 'themify'),
		'meta' => array(
			'fields' => $post_image_dimensions,
			'description' => __('Enter height = 0 to disable vertical cropping with img.php enabled', 'themify'), 	
			'before' => '',
			'after' => '',
			'separator' => ''
		)
	),
	// Team Title
	array(
		'name' 		=> 'team_title',	
		'title' 	=> __('Team Member Position', 'themify'), 	
		'description' => '',
		'type' 		=> 'textbox',			
		'meta'		=> array()			
	),
	// Shortcode ID
	array(
		'name' 		=> 'post_id_info',	
		'title' 	=> __('Shortcode ID', 'themify'),
		'description' => __('To show this use [team id="%s"]'),			
		'type' 		=> 'post_id_info'
	),
	// External Link
	$external_link,
	// Lightbox Link
	themify_lightbox_link_field(),
);

	///////////////////////////////////////
	// Build Write Panels
	///////////////////////////////////////
	themify_build_write_panels(array(
		array(
			'name'		=> __('Post Options', 'themify'),
			'id' => 'post-options',
			'options'	=> $post_meta_box_options,
			'pages'	=> 'post'
		),
		array(
			'name'		=> __('Post Styles', 'themify'),
			'id' => 'post-styles',
			'options'	=> $post_meta_box_styles,
			'pages'	=> 'post'
		),
		array(
			'name'		=> __('Page Options', 'themify'),	
			'id' => 'page-options',
			'options'	=> $page_meta_box_options, 		
			'pages'	=> 'page'
		),
		array(
			'name'		=> __('Query Posts', 'themify'),	
			'id' => 'query-posts',
			'options'	=> $query_post_meta_box, 		
			'pages'	=> 'page'
		),
		array(
			'name'		=> __('Query Sections', 'themify'),	
			'id' => 'query-section',
			'options'	=> $query_section_meta_box, 		
			'pages'	=> 'page'
		),
		array(
			'name'		=> __('Query Portfolios', 'themify'),	
			'id' => 'query-portfolio',
			'options'	=> $query_portfolio_meta_box, 		
			'pages'	=> 'page'
		),
		array(
			'name'		=> __('Portfolio Options', 'themify'),
			'id' => 'portfolio-options',
			'options'	=> $portfolio_meta_box,
			'pages'	=> 'portfolio'
		),
		array(
			'name'		=> __('Portfolio Styles', 'themify'),
			'id' => 'portfolio-styles',
			'options'	=> $portfolio_meta_box_styles,
			'pages'	=> 'portfolio'
		),
		array(
			'name'	=> __('Highlight Options', 'themify'),	
			'id' => 'highlight-options',
			'options' => $highlight_meta_box,
			'pages'	=> 'highlight'
		),
		array(
			'name'	=> __('Team Options', 'themify'),	
			'id' => 'team-options',
			'options' => $team_meta_box,
			'pages'	=> 'team'
		),
		array(
			'name'		=> __('Section Options', 'themify'),			// Name displayed in box
			'id' => 'section-options',
			'options'	=> $section_meta_box, 	// Field options
			'pages'	=> 'section'					// Pages to show write panel
		)
  	));
	
/* 	Custom Functions
/***************************************************************************/

	///////////////////////////////////////
	// Enable WordPress feature image
	///////////////////////////////////////
	add_theme_support( 'post-thumbnails' );
	remove_post_type_support( 'page', 'thumbnail' );

	///////////////////////////////////////
	// Setup content width for media
	///////////////////////////////////////
	if ( ! isset( $content_width ) ) {
		$content_width = 978;
	}
		
	/**
	 * Register Custom Menu Function
	 */
	function themify_register_custom_nav() {
		if (function_exists('register_nav_menus')) {
			register_nav_menus( array(
				'main-nav' => __( 'Main Navigation', 'themify' ),
				'footer-nav' => __( 'Footer Navigation', 'themify' ),
			) );
		}
	}
	
	/**
	 * Default Main Nav Function
	 */
	function themify_default_main_nav() {
		echo '<ul id="main-nav" class="main-nav clearfix pagewidth">';
		wp_list_pages('title_li=');
		echo '</ul>';
	}

	/**
	 * Sets custom menu selected in page custom panel as navigation, otherwise sets the default.
	 */
	function themify_theme_menu_nav(){
		global $themify;
		if('' != $themify->custom_menu){
			wp_nav_menu(array('menu' => $themify->custom_menu, 'fallback_cb' => 'themify_default_main_nav' , 'container'  => '' , 'menu_id' => 'main-nav' , 'menu_class' => 'main-nav'));
		} else {
			wp_nav_menu(array('theme_location' => 'main-nav' , 'fallback_cb' => 'themify_default_main_nav' , 'container'  => '' , 'menu_id' => 'main-nav' , 'menu_class' => 'main-nav'));
		}
	}
	
	/**
	 * Checks if the browser is a mobile device
	 * @return bool 
	 */
	function themify_is_mobile(){
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	/**
	 * Transition animation setup
	 * @return array
	 */
	function themify_get_transition_setup() {
		$config = array();
		$selectors = array();
		$selectors['element_first'] = '.grid4:not(.query-section) .post:not(.section-post):nth-of-type(4n+1), 
					.grid3:not(.query-section) .post:not(.section-post):nth-of-type(3n+1), 
					.grid2:not(.query-section) .post:not(.section-post):nth-of-type(2n+1), 
					.grid2-thumb:not(.query-section) .post:not(.section-post):nth-of-type(2n+1), 
					.list-post:not(.query-section) .post:not(.section-post):nth-of-type(2n+1), 
					.col2-1.first, 
					.col3-2.first, 
					.col3-1.first, 
					.col4-3.first, 
					.col4-2.first,
					.col4-1.first';
		$selectors['element_last'] = '.grid4:not(.query-section) .post:not(.section-post):nth-of-type(4n+4),
					.grid3:not(.query-section) .post:not(.section-post):nth-of-type(3n+3),
					.grid2:not(.query-section) .post:not(.section-post):nth-of-type(2n+2),
					.grid2-thumb:not(.query-section) .post:not(.section-post):nth-of-type(2n+2),
					.list-post:not(.query-section) .post:not(.section-post):nth-of-type(2n+2),
					.col2-1.last,
					.col3-2.last,
					.col3-1.last,
					.col4-3.last,
					.col4-2.last,
					.col4-1.last';
		$selectors['element_first_second'] = '.grid4:not(.query-section) .post:not(.section-post):nth-of-type(4n+2),
					.col4-1.second';
		$selectors['element_last_second'] = '.grid4:not(.query-section) .post:not(.section-post):nth-of-type(4n+3),
					.col4-1.third';
		$selectors['element_middle'] = '.grid3:not(.query-section) .post:not(.section-post):nth-of-type(3n+2),
					.col3-1.second,
					.col4-2.second';
		$selectors['element_button'] = '.section-post .button';

		$config = array(
			array(
				'selector' => themify_remove_rn_chars($selectors['element_first']),
				'value' => array(
					'data-center-top' => 'left[sqrt]:0px;top[sqrt]:0px;opacity:1;',
					'data-bottom-top' => 'left[sqrt]: -400px;top[sqrt]:200px;opacity: 0;'
				)
			),
			array(
				'selector' => themify_remove_rn_chars($selectors['element_last']),
				'value' => array(
					'data-center-top' => 'right[sqrt]:0px;top[sqrt]:0px;opacity: 1;',
					'data-bottom-top' => 'right[sqrt]:-400px;top[sqrt]:200px;opacity:0;'
				)
			),
			array(
				'selector' => themify_remove_rn_chars($selectors['element_first_second']),
				'value' => array(
					'data-center-top' => 'left[sqrt]:0px;top[sqrt]:0px;opacity: 1;',
					'data-bottom-top' => 'left[sqrt]:-200px;top[sqrt]:200px;opacity:0;'
				)
			),
			array(
				'selector' => themify_remove_rn_chars($selectors['element_last_second']),
				'value' => array(
					'data-center-top' => 'right[sqrt]:0px;top[sqrt]:0px;opacity: 1;',
					'data-bottom-top' => 'right[sqrt]:-200px;top[sqrt]:200px;opacity:0;'
				)
			),
			array(
				'selector' => themify_remove_rn_chars($selectors['element_middle']),
				'value' => array(
					'data-center-top' => 'top[sqrt]:0px;opacity: 1;',
					'data-bottom-top' => 'top[sqrt]:200px;opacity: 0;'
				)
			),
			array(
				'selector' => themify_remove_rn_chars($selectors['element_button']),
				'value' => array(
					'data-bottom-top' => 'opacity:0;',
					'data-center-top' => 'opacity:1;'
				)
			)
		);

		// check fadeIn option checked
		if ( themify_check('setting-transition_effect_fadein') ) {
			foreach ( $config as $k => $v ) {
				$config[$k]['value'] = array(
					'data-bottom-top' => 'opacity:0;',
					'data-center' => 'opacity:1;'
				);
			}
		}

		// check if mobile exclude disabled OR disabled all transition
		if ( ( themify_get('setting-transition_effect_mobile_exclude') != 'on' && themify_is_mobile() ) 
			|| themify_check('setting-transition_effect_all_disabled') ) {
			$config = array();
		}

		return apply_filters( 'themify_parallax_config', $config );
	}

	/**
	 * Remove new line character in strings
	 * @param type $string 
	 * @return string
	 */
	function themify_remove_rn_chars($string) {
		return preg_replace('/^\s+|\n|\r|\s+$/m', '', $string);
	}

	///////////////////////////////////////
	// Register Sidebars
	///////////////////////////////////////
	if ( function_exists('register_sidebar') ) {
		register_sidebar(array(
			'name' => __('Sidebar', 'themify'),
			'id' => 'sidebar-main',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h4 class="widgettitle">',
			'after_title' => '</h4>',
		));
		register_sidebar(array(
			'name' => __('Social Widget', 'themify'),
			'id' => 'social-widget',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<strong>',
			'after_title' => '</strong>',
		));
	}

	///////////////////////////////////////
	// Footer Sidebars
	///////////////////////////////////////
	themify_register_grouped_widgets();

if( ! function_exists('themify_theme_comment') ) {
	/**
	 * Custom Theme Comment
	 * @param object $comment Current comment.
	 * @param array $args Parameters for comment reply link.
	 * @param int $depth Maximum comment nesting depth.
	 * @since 1.0.0
	 */
	function themify_theme_comment($comment, $args, $depth) {
	   $GLOBALS['comment'] = $comment; 
	   ?>

		<li id="comment-<?php comment_ID() ?>" <?php comment_class(); ?>>
			<p class="comment-author"> <?php echo get_avatar($comment,$size='48'); ?> <?php printf('<cite>%s</cite>', get_comment_author_link()) ?><br />
				<small class="comment-time"><?php comment_date(apply_filters('themify_comment_date', 'M d, Y')); ?> @ <?php comment_time(apply_filters('themify_comment_time', 'H:i:s')); ?>
				<?php edit_comment_link( __('Edit', 'themify'),' [',']') ?>
				</small> </p>
			<div class="commententry">
				<?php if ($comment->comment_approved == '0') : ?>
				<p><em>
					<?php _e('Your comment is awaiting moderation.', 'themify') ?>
					</em></p>
				<?php endif; ?>
				<?php comment_text() ?>
			</div>
			<p class="reply">
				<?php comment_reply_link(array_merge( $args, array('add_below' => 'comment', 'depth' => $depth, 'reply_text' => __( 'Reply', 'themify' ), 'max_depth' => $args['max_depth']))) ?>
			</p>
	<?php
	}
}
	
	///////////////////////////////////////
	// Themify Theme Key
	///////////////////////////////////////
	add_filter('themify_theme_key', create_function('$k', "return 'i7rs5mzevaasb06udbqumsy5ttebiorgc';"));
?>