<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<?php
/** Themify Default Variables
 *  @var object */
	global $themify; ?>
<meta charset="<?php bloginfo( 'charset' ); ?>">

<title><?php if (is_home() || is_front_page()) { bloginfo('name'); } else { echo wp_title(''); } ?></title>

<?php
/**
 *  Stylesheets and Javascript files are enqueued in theme-functions.php
 */
?>

<!-- wp_header -->
<?php wp_head(); ?>

</head> 

<body <?php body_class(); ?>>
<a href="https://plus.google.com/103815111926622445819" rel="publisher"></a>
<?php themify_body_start(); // hook ?>
<div id="pagewrap">

	<div id="headerwrap" <?php $themify->theme->custom_header_background(); ?> >
    
		<?php themify_header_before(); // hook ?>
		<header id="header" class="pagewidth">
			<?php themify_header_start(); // hook ?>
			<hgroup>
				
	
				<h2 id="site-description"><?php bloginfo('description'); ?></h2>
			
				<div class="social-widget">
					<?php dynamic_sidebar('social-widget'); ?>

					<?php if(!themify_check('setting-exclude_rss')): ?>
						<div class="rss">
							<a href="<?php if(themify_get('setting-custom_feed_url') != ""){ echo themify_get('setting-custom_feed_url'); } else { bloginfo('rss2_url'); } ?>">RSS</a>
						</div>
					<?php endif; ?>
				</div>
				<!-- /.social-widget -->
					
				<?php if(!themify_check('setting-exclude_search_form')): ?>
					<?php get_search_form(); ?>
				<?php endif ?>

				<?php
					// If there's a header background slider, show it.
					global $themify_bg_gallery;
					$themify_bg_gallery->create_controller();
				?>
			</hgroup>
			
			<div id="nav-bar" class="clearfix">
				<nav class="container">
					<div id="menu-icon" class="mobile-button"><span><?php _e('', 'themify'); ?></span></div>
					<?php echo themify_logo_image('site_logo'); ?>
					<?php themify_theme_menu_nav(); ?>
					<!-- /#main-nav --> 
				</nav>
			</div>

			<?php themify_header_end(); // hook ?>
		</header>
		<!-- /#header -->
        <?php themify_header_after(); // hook ?>
				
	</div>
	<!-- /#headerwrap -->
	
	<div id="body" class="clearfix">
    <?php themify_layout_before(); //hook ?>