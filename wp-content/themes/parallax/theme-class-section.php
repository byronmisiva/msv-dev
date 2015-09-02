<?php
if(!class_exists('Themify_Section')){
	/**
	 * Class to create sections
	 */
	class Themify_Section extends Themify_Types {
		function shortcode($atts = array(), $post_type){
			extract($atts);
			// Parameters to get posts
			$args = array(
				'post_type' => $post_type,
				'posts_per_page' => $limit,
				'order' => $order,
				'orderby' => $orderby,
				'suppress_filters' => false
			);
			$args['tax_query'] = $this->parse_category_args($category, $post_type);
			
			// Defines layout type
			$cpt_layout_class = $this->post_type.'-multiple clearfix type-multiple';
	
			// Single post type or many single post types
			if( '' != $id ){
				if(strpos($id, ',')){
					$ids = explode(',', str_replace(' ', '', $id));
					foreach ($ids as $string_id) {
						$int_ids[] = intval($string_id);
					}
					$args['post__in'] = $int_ids;
					$args['orderby'] = 'post__in';
				} else {
					$args['p'] = intval($id);
					$cpt_layout_class = $this->post_type.'-single';
				}
			}	

			// Get posts according to parameters
			$posts = get_posts( apply_filters('themify_'.$post_type.'_shortcode_args', $args) );
	
			// Collect markup to be returned
			$out = '';
		
			if ($posts) {
				global $themify;
				$themify_save = clone $themify; // save a copy
			
				// override $themify object
				$themify->hide_title = $title;
				$themify->display_content = $display;
				$themify->more_link = $more_link;
				$themify->more_text = $more_text;
				$themify->post_layout = $style;
				
				$out .= '<div class="loops-wrapper shortcode ' . $post_type  . ' ' . $style . ' '. $cpt_layout_class .'">';
					$out .= themify_get_shortcode_template($posts, 'includes/loop-section', 'index');
					$out .= $this->section_link($more_link, $more_text, $post_type);
				$out .= '</div>';
				
				$themify = clone $themify_save; // revert to original $themify state
			}
			return $out;
		}
	}
}
?>