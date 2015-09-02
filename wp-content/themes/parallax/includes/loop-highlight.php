<?php
/** Themify Default Variables
 *  @var object */
global $themify; ?>

<article id="highlight-'<?php the_ID(); ?>'" <?php post_class('post highlight-post ' . $themify->col_class); ?>>

	<figure class="post-image">
		<?php
		$link = themify_get_featured_image_link('no_permalink=true');
		$before = '';
		$after = '';
		if ($link != '') {
			$before = '<a href="' . $link . '" title="' . get_the_title() . '">';
			$zoom_icon = themify_zoom_icon(false);
			$after = $zoom_icon . '</a>' . $after;
			$zoom_icon = '';
		}
		?>
		<?php if('no' != $themify->hide_image): ?>
			<?php echo $before; ?>
			<?php themify_image('ignore=true&w='.$themify->width.'&h='.$themify->height); ?>
			<?php echo $after; ?>
		<?php endif; // hide image ?>
	</figure>

	<div class="post-content">
		<?php if('no' != $themify->hide_title): ?>
			<h6 class="post-title"><?php echo $before; ?><?php the_title(); ?><?php echo $after; ?></h6>
		<?php endif; // hide title ?>
		<?php if ( 'excerpt' == $themify->display_content && ! is_attachment() ) : ?>
			<?php the_excerpt(); ?>
		<?php elseif($themify->display_content == 'content'): ?>
			<?php the_content(themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>
		<?php endif; //display content ?>
		<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>
	</div>

</article>
<!-- / .post -->