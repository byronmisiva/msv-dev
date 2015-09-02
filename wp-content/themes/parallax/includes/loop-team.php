<?php
/** Themify Default Variables
 *  @var object */
global $themify; ?>

<article id= "team-<?php the_ID(); ?>" class="<?php echo (preg_match("/SN/", get_the_title())) ? 'hidden-xs hidden-sm' : ''?>">
	<?php
	$link = themify_get_featured_image_link();
	$before = '';
	$after = '';
	if ($link != '') {
		$before = '<a href="' . $link . '" title="' . get_the_title() . '">';
		$zoom_icon = themify_zoom_icon(false);
		$after = $zoom_icon . '</a>' . $after;
		$zoom_icon = '';
	}
	?>
	<?php if ( 'yes' != $themify->hide_image ) : ?>
		<?php
		// Check if user wants to use a common dimension or those defined in each highlight
		if ( 'yes' == $themify->use_original_dimensions ) {
			// Save post id
			$post_id = get_the_ID();

			// Set image width
			$themify->width = get_post_meta($post_id, 'image_width', true);

			// Set image height
			$themify->height = get_post_meta($post_id, 'image_height', true);
		}
		?>					
		<?php if ( 'yes' != $themify->unlink_image and !preg_match("/SN/", get_the_title())) : ?>
			<?php echo $before; ?>
				<?php themify_image('ignore=true&w='.$themify->width.'&h='.$themify->height); ?>
			<?php echo $after; ?>
		<?php else : ?>
			<?php themify_image('ignore=true&w='.$themify->width.'&h='.$themify->height); ?>
		<?php endif; ?>		
	<?php endif; // hide image ?>
</article>