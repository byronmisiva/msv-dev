<?php
// Add Shortcode
function enjoyinstagram_mb_shortcode_grid() {
	$shortcode_content = '';
STATIC $i = 1;
if(get_option('enjoyinstagram_client_id') || get_option('enjoyinstagram_client_id') != '') {

$instagram = new Enjoy_Instagram(get_option('enjoyinstagram_client_id'));
$instagram->setAccessToken(get_option('enjoyinstagram_access_token'));
if(get_option('enjoyinstagram_user_or_hashtag')=='hashtag'){
$result = $instagram->getTagMedia(urlencode(get_option('enjoyinstagram_hashtag')));
}else{
$result = $instagram->getUserMedia(urlencode(get_option('enjoyinstagram_user_id')));
}

$pre_shortcode_content = "<div id=\"grid-".$i."\" class=\"ri-grid ri-grid-size-2 ri-shadow\" style=\"display:none;\"><ul>";



	if (isHttps()) {
		foreach ($result->data as $entry) {
			$entry->images->thumbnail->url = str_replace('http://', 'https://', $entry->images->thumbnail->url);
			$entry->images->standard_resolution->url = str_replace('http://', 'https://', $entry->images->standard_resolution->url);
		}
	}




foreach ($result->data as $entry) {
	if(!empty($entry->caption)) {
		$caption = $entry->caption->text;
	}else{
		$caption = '';
	}

	if(!empty($entry->user)) {
		$user = $entry->user->username;
		$userPicture = $entry->user->profile_picture;
	}else{
		$user = '';
		$userPicture = '';
	}

	$shortcode_content .=  "<li><div class=\"header_ca\"><img  src=\"{$userPicture}\" style=\"width:30px; margin-right: 10px; border-radius: 50%;\">{$user}</div><img  src=\"{$entry->images->standard_resolution->url}\"><div class=\"caption_ca\">{$caption}</div></li>";
	
  }
  
$post_shortcode_content = "</ul></div>";

?>
<script type="text/javascript">
			jQuery(function() {
				jQuery('#grid-<?php echo $i; ?>').fadeIn('1000');
			});
		</script>
<?php
}
$i++;
$shortcode_content = $pre_shortcode_content.$shortcode_content.$post_shortcode_content;
return $shortcode_content;
}
add_shortcode( 'enjoyinstagram_mb_grid', 'enjoyinstagram_mb_shortcode_grid' );
?>
