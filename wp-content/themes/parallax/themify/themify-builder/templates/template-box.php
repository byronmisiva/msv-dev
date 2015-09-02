<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Box
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

$fields_default = array(
	'mod_title_box' => '',
	'content_box' => '',
	'appearance_box' => '',
	'color_box' => '',
	'add_css_box' => ''
);

if ( isset( $mod_settings['appearance_box'] ) ) 
	$mod_settings['appearance_box'] = $this->get_checkbox_data( $mod_settings['appearance_box'] );

$fields_args = wp_parse_args( $mod_settings, $fields_default );
extract( $fields_args, EXTR_SKIP );

$class = $appearance_box . ' ' . $color_box . ' ' . $add_css_box . ' module-' . $mod_name;

?>
<!-- module box -->
<div id="<?php echo $module_ID; ?>" class="module ui <?php echo esc_attr( $class ); ?>">
	<?php if ( $mod_title_box != '' ): ?>
	<h3 class="module-title"><?php echo $mod_title_box; ?></h3>
	<?php endif; ?>
	
	<?php do_action( 'themify_builder_before_template_content_render' ); ?>

	<?php echo apply_filters( 'themify_builder_tmpl_shortcode', $content_box ); ?>

	<?php do_action( 'themify_builder_after_template_content_render' ); ?>
</div>
<!-- /module box -->