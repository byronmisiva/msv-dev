<?php if(!is_single()){ global $more; $more = 0; } //enable more link ?>

<?php 
/** Themify Default Variables
 *  @var object */
global $themify; ?>

<?php themify_post_before(); // hook ?>

<section id="<?php echo apply_filters('editable_slug', $post->post_name); ?>" class="<?php echo join( ' ', get_post_class(array("post", "clearfix" ,"section-post")))?>">

	<div class="thumbnail">
		<div class="caption">
			<div class="container">					
				<?php if($themify->hide_title != 'yes'): ?>
					<?php themify_before_post_title(); // Hook ?>
						<h2><?php the_title(); ?></h2>
					<?php themify_after_post_title(); // Hook ?>
				<?php endif; //section title ?>																
			</div>				
		</div>
		<?php $themify->theme->section_background("featured-image"); ?>
	</div>
	
	<div class="section-inner clearfix">
		<?php themify_post_start(); // hook ?>
		
		<?php if($themify->hide_subtitle != 'yes'): ?>
			<?php if(themify_check('subtitle')): ?>
				<h2 class="section-subhead"><?php echo themify_get('subtitle'); ?></h2>
			<?php endif; // end check for subtitle text ?>
		<?php endif; // end check for hide_subtitle ?>
	
		<div class="section-content post-content clearfix">

			<?php the_content(themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>
			
			<?php edit_post_link(__('Edit Section', 'themify'), '<span class="edit-button">[', ']</span>'); ?>

		</div>
		<!-- /.section-content -->

		<?php themify_post_end(); // hook ?>
	</div> <!-- /.section-inner -->
	
	<div class="section-overlay"></div>
	<!-- /section-overlay -->
	
</section>
<?php themify_post_after(); // hook ?>
<!-- /.section-post -->