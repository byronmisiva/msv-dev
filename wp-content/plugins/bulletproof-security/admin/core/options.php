<?php
// Direct calls to this file are Forbidden when core files are not present
if ( ! function_exists('add_action') ) {
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}
 
if ( ! current_user_can('manage_options') ) { 
		header('Status: 403 Forbidden');
		header('HTTP/1.1 403 Forbidden');
		exit();
}

?>

<div class="wrap" style="margin-top:45px;">

<?php

if ( function_exists('get_transient') ) {
require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

	if ( false === ( $bps_api = get_transient('bulletproof-security_info') ) ) {
		$bps_api = plugins_api( 'plugin_information', array( 'slug' => stripslashes( 'bulletproof-security' ) ) );
		
	if ( ! is_wp_error( $bps_api ) ) {
		$bps_expire = 60 * 30; // Cache downloads data for 30 minutes
		$bps_downloaded = array( 'downloaded' => $bps_api->downloaded );
		maybe_serialize( $bps_downloaded );
		set_transient( 'bulletproof-security_info', $bps_downloaded, $bps_expire );
	}
	}

		$bps_transient = get_transient( 'bulletproof-security_info' );
    	
		echo '<div class="bps-star-container" style="float:right;position:relative;top:-40px;left:0px;margin:0px 0px -40px 0px;font-weight:bold;">';
		echo '<div class="bps-star"><img src="'.plugins_url('/bulletproof-security/admin/images/star.png').'" /></div>';
		echo '<div class="bps-downloaded">';
		
		foreach ( $bps_transient as $key => $value ) {
			echo number_format_i18n( $value ) .' '. str_replace( 'downloaded', "Downloads", $key );
		}
		
		echo '<div class="bps-star-link"><a href="http://wordpress.org/support/view/plugin-reviews/bulletproof-security" target="_blank" title="Add your own BPS Plugin Review">'.__('Add a Review', 'bulletproof-security').'</a><br><a href="http://affiliates.ait-pro.com/po/" target="_blank" title="Upgrade to BulletProof Security Pro">Upgrade to Pro</a></div>';
		echo '</div>';
		echo '</div>';
}

?>  

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ htaccess Core', 'bulletproof-security'); ?></h2>
<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#000;">

<?php
// HUD - Heads Up Display - Warnings and Error messages
echo bps_check_php_version_error();
echo bps_hud_check_bpsbackup();
echo bps_check_safemode();
echo @bps_w3tc_htaccess_check($plugin_var);
echo @bps_wpsc_htaccess_check($plugin_var);
bps_delete_language_files();

// default.htaccess, secure.htaccess, fwrite content for all WP site types
$bps_get_domain_root = bpsGetDomainRoot();
$bps_get_wp_root_default = bps_wp_get_root_folder();
// Replace ABSPATH = wp-content/plugins
$bps_plugin_dir = str_replace( ABSPATH, '', WP_PLUGIN_DIR );
// Replace ABSPATH = wp-content
$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );
// Replace ABSPATH = wp-content/uploads
$wp_upload_dir = wp_upload_dir();
$bps_uploads_dir = str_replace( ABSPATH, '', $wp_upload_dir['basedir'] );
$bps_topDiv = '<div id="message" class="updated" style="background-color:#ffffe0;font-size:1em;font-weight:bold;border:1px solid #999999; margin-left:70px;"><p>';
$bps_bottomDiv = '</p></div>';

// Form: Root BulletProof Mode and Default Mode - copy and rename htaccess files to root folder
if ( isset( $_POST['Submit-Secure-Root'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_root_copy' );
	
	$options = get_option('bulletproof_security_options_autolock');
	$DefaultHtaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
	$RootHtaccess = ABSPATH . '.htaccess';
	$SecureHtaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
	$permsRootHtaccess = @substr(sprintf('%o', fileperms($RootHtaccess)), -4);
	$sapi_type = php_sapi_name();	

	if ( $_POST['bpsecureroot'] == 'bulletproof' ) { 
		
		if ( @substr($sapi_type, 0, 6) != 'apache' && @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777') { // Windows IIS, XAMPP, etc
			@chmod($RootHtaccess, 0644);
		}		
		
		if ( ! copy($SecureHtaccess, $RootHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to activate Root Folder BulletProof Mode protection. Your website is NOT protected.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {
			
			if ( @$permsRootHtaccess == '0644' && @substr($sapi_type, 0, 6) != 'apache' && $options['bps_root_htaccess_autolock'] != 'Off' || $options['bps_root_htaccess_autolock'] == 'On' ) {			
				@chmod($RootHtaccess, 0404);
			}
		
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Root Folder BulletProof Mode protection activated. Your website is now protected.', 'bulletproof-security').'</strong></font>';
			echo $text;
    		echo $bps_bottomDiv;

			// Save the Setup Wizard DB option only once if it does not already exist
			// A manual new setup of BPS was chosen instead of using the Setup Wizard
			// A BPS upgrade updates the value to: upgrade once if the option does not already exist.
			$bps_setup_wizard = 'bulletproof_security_options_wizard_free';
			$BPS_Wizard = array( 'bps_wizard_free' => 'manual' );	
	
			if ( ! get_option( $bps_setup_wizard ) ) {	
		
				foreach( $BPS_Wizard as $key => $value ) {
					update_option('bulletproof_security_options_wizard_free', $BPS_Wizard);
				}
			}		
		}
	}
	elseif ( $_POST['bpsecureroot'] == 'default' ) {

		if ( @substr($sapi_type, 0, 6) != 'apache' || @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777') { // Windows IIS, XAMPP, etc
			@chmod($RootHtaccess, 0644);
		}

		if ( !copy($DefaultHtaccess, $RootHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to activate Root Folder BulletProof Mode (Default Mode). Unable to Copy the default.htaccess file to your root folder.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {

			if ( @$permsRootHtaccess == '0644' && @substr($sapi_type, 0, 6) != 'apache' && $options['bps_root_htaccess_autolock'] != 'Off' || $options['bps_root_htaccess_autolock'] == 'On' ) {
				@chmod($RootHtaccess, 0404);
			}
			
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Reminder Warning: Root Folder BulletProof Mode (Default Mode) is activated. Your root folder is not protected.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form: wp-admin BulletProof Mode - copy and rename htaccess file to wp-admin folder
// Do String Replacements for Custom Code AFTER new .htaccess file has been copied to wp-admin
if ( isset( $_POST['Submit-Secure-wpadmin'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_wpadmin_copy' );
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		echo $bps_topDiv;
		$text = '<font color="red"><strong>'.__('wp-admin Folder BulletProof Mode was not activated. Either it is disabled on the Security Modes page or you have a Go Daddy Managed WordPress Hosting account. The wp-admin folder is restricted on GDMW hosting account types.', 'bulletproof-security').'</strong></font>';
		echo $text;
   		echo $bps_bottomDiv;		
	return;
	}
	
	$options = get_option('bulletproof_security_options_customcode_WPA');  
	$HtaccessMaster = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	$wpadminHtaccess = ABSPATH . 'wp-admin/.htaccess';
	$permsHtaccess = @substr(sprintf('%o', fileperms($wpadminHtaccess)), -4);
	$sapi_type = php_sapi_name();	
	$bpsString1 = "# CCWTOP";
	$bpsString2 = "# CCWPF";
	$bpsString3 = '/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s';
	$bpsString4 = '/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s';
	$bpsReplace1 = htmlspecialchars_decode($options['bps_customcode_one_wpa'], ENT_QUOTES);
	$bpsReplace2 = htmlspecialchars_decode($options['bps_customcode_two_wpa'], ENT_QUOTES);
	$bpsReplace3 = htmlspecialchars_decode($options['bps_customcode_deny_files_wpa'], ENT_QUOTES);	
	$bpsReplace4 = htmlspecialchars_decode($options['bps_customcode_bpsqse_wpa'], ENT_QUOTES);	
	
	if ( $_POST['bpsecurewpadmin'] == 'bulletproof' ) {

		if ( @substr($sapi_type, 0, 6) != 'apache' || @$permsHtaccess != '0666' || @$permsHtaccess != '0777') { // Windows IIS, XAMPP, etc
			@chmod($wpadminHtaccess, 0644);
		}		

		if ( !copy($HtaccessMaster, $wpadminHtaccess) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to activate wp-admin Folder BulletProof Mode protection. Your wp-admin folder is NOT protected.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
			
		} else {
	
			if ( file_exists($wpadminHtaccess) ) {
				
				if ( @$permsHtaccess != '0666' || @$permsHtaccess != '0777' ) { // Windows IIS, XAMPP, etc
					@chmod($wpadminHtaccess, 0644);
				}				
				
				$bpsBaseContent = file_get_contents($wpadminHtaccess);
		
			if ( $options['bps_customcode_deny_files_wpa'] != '') {        
				$bpsBaseContent = preg_replace('/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s', $bpsReplace3, $bpsBaseContent);
			}
			
			if ( $options['bps_customcode_bpsqse_wpa'] != '') {        
				$bpsBaseContent = preg_replace('/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s', $bpsReplace4, $bpsBaseContent);
			}
				
				$bpsBaseContent = str_replace($bpsString1, $bpsReplace1, $bpsBaseContent);
				$bpsBaseContent = str_replace($bpsString2, $bpsReplace2, $bpsBaseContent);
				
				file_put_contents( $wpadminHtaccess, $bpsBaseContent );

				echo $bps_topDiv;
				$text = '<font color="green"><strong>'.__('wp-admin Folder BulletProof Mode protection activated. Your wp-admin folder is now protected.', 'bulletproof-security').'</strong></font>';
				echo $text;
				echo $bps_bottomDiv;
			}
		}
	}
	elseif ( $_POST['bpsecurewpadmin'] == 'default' ) {

		@unlink($wpadminHtaccess);
	
	if ( file_exists($wpadminHtaccess) ) {
		echo $bps_topDiv;
		$text = '<font color="red"><strong>'.__('Failed to delete the wp-admin htaccess file! The file does not exist. It may have been deleted or renamed already.', 'bulletproof-security').'</strong></font>';
		echo $text;
   		echo $bps_bottomDiv;
	
	} else {
		
		echo $bps_topDiv;
		$text = '<font color="green"><strong>'.__('wp-admin Folder BulletProof Mode deactivated. The wp-admin htaccess file has been deleted. If you are testing or troubleshooting then be sure to activate wp-admin BulletProof Mode when you are done testing.', 'bulletproof-security').'</strong></font><br><font color="red"><strong>'.__('Your wp-admin folder is no longer protected.', 'bulletproof-security').'</strong></font>';
		echo $text;
		echo $bps_bottomDiv;
	}
	}
}

// Form: BPS Master htaccess folder - copy Deny All htaccess file 
if ( isset( $_POST['Submit-Master-Folder'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_denyall_master' );
	
	$bps_rename_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	
	if ( $_POST['bpssecuremaster'] == 'bulletproof' ) { 

		if ( !copy($bps_rename_htaccess, $bps_rename_htaccess_renamed) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate Master htaccess BulletProof Mode. Your BPS Master htaccess folder is NOT Protected with Deny All htaccess folder protection.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		} else {
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Master htaccess BulletProof Mode Activated. Your BPS Master htaccess folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form: BPS backup folder - copy Deny All htaccess file 
if ( isset( $_POST['Submit-Backup-Folder'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_denyall_bpsbackup' );
	
	$bps_rename_htaccess_backup = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/deny-all.htaccess';
	$bps_rename_htaccess_backup_online = WP_CONTENT_DIR . '/bps-backup/.htaccess';
	
	if ( $_POST['bpssecurebackup'] == 'bulletproof' ) { 
		
		if ( !copy($bps_rename_htaccess_backup, $bps_rename_htaccess_backup_online) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Activate BPS Backup BulletProof Mode. Your BPS /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder is NOT Protected with Deny All htaccess folder protection!', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		} else {
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('BPS Backup BulletProof Mode Activated. Your BPS /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder is Now Protected with Deny All htaccess folder protection.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
	}
}

// Form: Backup htaccess files
if ( isset( $_POST['Submit-Backup-htaccess-Files'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_backup_active_htaccess_files' );
	
	$old_backroot = ABSPATH . '.htaccess';
	$new_backroot = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
	$old_backwpadmin = ABSPATH . 'wp-admin/.htaccess';
	$new_backwpadmin = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
	
	if ( $_POST['bpsbackuphtaccessfiles'] == 'backup-htaccess-files' ) { 
	
		if ( !file_exists($old_backroot) ) { 
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('You do not currently have an .htaccess file in your Root folder to backup.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo '</p></div>';
		
		} else {	
		
		if ( !copy($old_backroot, $new_backroot) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Backup Your Root .htaccess File. File copy function failed. Check the folder permissions for the /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your currently active Root .htaccess file has been backed up successfully.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		}
		}
		
		if ( !file_exists($old_backwpadmin) ) { 
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('You do not currently have an htaccess file in your wp-admin folder to backup.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
		
		if ( !copy($old_backwpadmin, $new_backwpadmin) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Backup Your wp-admin htaccess File. File copy function failed. Check the folder permissions for the /', 'bulletproof-security').$bps_wpcontent_dir.__('/bps-backup folder. Folder permissions should be set to 755.', 'bulletproof-security').'</strong></font>';
			echo $text;
			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$text = '<font color="green"><strong>'.__('Your currently active wp-admin htaccess file has been backed up successfully.', 'bulletproof-security').'</strong></font><br>';
			echo $text;
			echo $bps_bottomDiv;
		}
		}
	}
}

// Form: Restore backed up htaccess files
if ( isset( $_POST['Submit-Restore-htaccess-Files'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_restore_active_htaccess_files' );
	
	$old_restoreroot = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
	$new_restoreroot = ABSPATH . '.htaccess';
	$old_restorewpadmin = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
	$new_restorewpadmin = ABSPATH . 'wp-admin/.htaccess';
	$permsRootHtaccess = @substr(sprintf('%o', fileperms($new_restoreroot)), -4);
	$sapi_type = php_sapi_name();		

	if ( $_POST['bpsrestorehtaccessfiles'] == 'restore-htaccess-files' ) { 
		
		if ( file_exists($old_restoreroot) ) { 
		
			if ( @substr($sapi_type, 0, 6) != 'apache' && @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777') { // Windows IIS, XAMPP, etc
				@chmod($new_restoreroot, 0644);
			}	
		
		if ( !copy($old_restoreroot, $new_restoreroot) ) {
			echo $bps_topDiv;
			echo '<font color="red"><strong>'.__('Failed to Restore Your Root htaccess File. Either you DO NOT currently have a Backed up Root htaccess file or your current active Root htaccess file permissions do not allow the file to be replaced/restored.', 'bulletproof-security').'</strong></font>';
   			echo $bps_bottomDiv;
		
		} else {
			
			if ( @substr($sapi_type, 0, 6) != 'apache' && $options['bps_root_htaccess_autolock'] != 'Off' || $options['bps_root_htaccess_autolock'] == 'On' ) {			
				@chmod($new_restoreroot, 0404);
			}
			
			echo $bps_topDiv;
			$textRoot = '<font color="green"><strong>'.__('Your Root htaccess file has been Restored successfully.', 'bulletproof-security').'</strong></font>';
			echo $textRoot;
			echo $bps_bottomDiv;
		}
		}
		
		if ( file_exists($old_restorewpadmin) ) { 	
		if ( !copy($old_restorewpadmin, $new_restorewpadmin) ) {
			echo $bps_topDiv;
			$text = '<font color="red"><strong>'.__('Failed to Restore Your wp-admin htaccess File. Either you DO NOT currently have a Backed up wp-admin htaccess file or your current active wp-admin htaccess file permissions do not allow the file to be replaced/restored.', 'bulletproof-security').'</strong></font>';
			echo $text;
   			echo $bps_bottomDiv;
		
		} else {
			
			echo $bps_topDiv;
			$textWpadmin = '<font color="green"><strong>'.__('Your wp-admin htaccess file has been Restored successfully.', 'bulletproof-security').'</strong></font>';
			echo $textWpadmin;
			echo $bps_bottomDiv;
		}
		}
	}
}

// General all purpose "Settings Saved." message for forms
if ( current_user_can('manage_options') && wp_script_is( 'bps-accordion', $list = 'queue' ) ) {
if ( @$_GET['settings-updated'] == true) {
	$text = '<p style="background-color:#ffffe0;font-size:1em;font-weight:bold;padding:5px;margin:0px;"><font color="green"><strong>'.__('Settings Saved', 'bulletproof-security').'</strong></font></p>';
	echo $text;
	}
}

require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-help-text.php' );
require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-htaccess-code.php' );

$bpsSpacePop = '-------------------------------------------------------------';

?>
</div>

<!-- jQuery UI Tabs Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative; top:0px; left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-security-shield.png'); ?>" style="float:left; padding:0px 8px 0px 0px; margin:-72px 0px 0px 0px;" /></div>
   
<style>
<!--
.bps-readme-table {background:#fff;vertical-align:text-top;margin:8px 0px 10px 0px;}
.bps-readme-table-td {padding:5px;}
-->
</style>

		<ul>
			<li><a href="#bps-tabs-1"><?php _e('Security Modes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-2"><?php _e('Security Status', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-5"><?php _e('Backup &amp; Restore', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-6"><?php _e('htaccess File Editor', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-7"><?php _e('Custom Code', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-9"><?php _e('Help &amp; FAQ', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-tabs-10"><?php _e('Whats New', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-11"><?php _e('My Notes', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-12"><?php _e('BPS Pro Features', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-13"><?php _e('Website Scanner', 'bulletproof-security'); ?></a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">
<h2><?php _e('htaccess File Security Modes ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Root Folder BulletProof Mode & wp-admin Folder BulletProof Mode', 'bulletproof-security'); ?></span></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

    <h2 style="margin:-15px 0px 5px 0px;border-bottom:1px solid #999999;"><?php _e('AutoMagic Buttons ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Click the', 'bulletproof-security'); echo '<strong>'; _e(' AutoMagic, Setup Steps & Other Help Info', 'bulletproof-security'); echo '</strong>'; _e(' Read Me help button below for setup steps', 'bulletproof-security'); ?></span></h2>

<h3><?php _e('AutoMagic, Setup Steps & Other Help Info', 'bulletproof-security'); ?>  <button id="bps-open-modal1" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content1" title="<?php _e('Setup Steps & AutoMagic', 'bulletproof-security'); ?>">
	<p><?php 
	$text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br>';
	echo $text;
	// Forum Help Links or of course both
	$text = '<strong><font color="blue">'.__('Forum Help Links: ', 'bulletproof-security').'</font></strong>'; 	
	echo $text;		
	?>
	<strong><a href="http://forum.ait-pro.com/forums/topic/read-me-first-free/#bps-free-general-troubleshooting" title="BPS Troubleshooting Steps" target="_blank"><?php _e('BPS Troubleshooting Steps', 'bulletproof-security'); ?></a></strong><br /><br />
	
	<?php echo $bps_modal_content1; ?></p>
</div>

<h3 style="padding-left:10px;"><?php $text = '<strong><a href="http://forum.ait-pro.com/video-tutorials/#setup-overview-free" target="_blank" title="Setup Wizard Link opens in a new Browser window">'.__('Setup & Overview Video Tutorial', 'bulletproof-security').'</a></strong>'; echo $text; ?></h3>

<?php if ( ! current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<div id="AutoMagic-buttons" style="">

<?php if ( ! is_multisite() ) { ?>

<form name="bps-auto-write-default" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
	<?php wp_nonce_field('bulletproof_security_auto_write_default'); ?>
	<input type="hidden" name="filename" value="bps-auto-write-default_write" />
	<div id="AutoMagic-buttons" style="float:left;padding-left:10px;padding-right:5px;">
	<input type="submit" name="bps-auto-write-default" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onclick="return confirm('<?php 
$text = __('Clicking OK will create a new customized default.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
	</div>
</form>

<form name="bps-auto-write-secure-root" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
	<?php wp_nonce_field('bulletproof_security_auto_write_secure_root'); ?>
	<input type="hidden" name="filename" value="bps-auto-write-secure_write" />
	<div id="AutoMagic-buttons" style="float:left;padding-left:10px;">
	<input type="submit" name="bps-auto-write-secure-root" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onclick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your website.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
	</div>
</form>

<?php } else { ?>

<form name="bps-auto-write-default-MUSDir" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
	<?php wp_nonce_field('bulletproof_security_auto_write_default_MUSDir'); ?>
	<input type="hidden" name="filename" value="bps-auto-write-default_write-MUSDir" />
	<div id="AutoMagic-buttons" style="float:left;padding-left:10px;">
	<input type="submit" name="bps-auto-write-default-MUSDir" value="<?php _e('Create default.htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onclick="return confirm('<?php $text = __('Clicking OK will create a new customized default.htaccess Master file for your Network/Multisite website.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('NOTE: Default Mode should ONLY be activated for Testing and Troubleshooting.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Create your new default.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
	</div>
</form>

<form name="bps-auto-write-secure-root-MUSDir" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
	<?php wp_nonce_field('bulletproof_security_auto_write_secure_root_MUSDir'); ?>
	<input type="hidden" name="filename" value="bps-auto-write-secure_write_MUSDir" />
	<div id="AutoMagic-buttons" style="float:left;padding-left:10px;">
	<input type="submit" name="bps-auto-write-secure-root-MUSDir" value="<?php _e('Create secure.htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onclick="return confirm('<?php $text = __('Clicking OK will create a new customized secure.htaccess Master file for your Network/Multisite website.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('This is only creating a Master file and NOT activating it. To activate Master files go to the Activate Security Modes section below.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Create your new secure.htaccess Master file or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
	</div>
</form>

<?php } ?>

</div>
<div style="clear:left;"></div>

<?php } ?>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

    <h2 style="border-bottom:1px solid #999999;"><?php _e('Activate|Deactivate Security Modes', 'bulletproof-security'); ?></h2>

    <h3><?php _e('Activate|Deactivate Root Folder BulletProof Mode (RBM)', 'bulletproof-security'); ?></h3>

<div id="WBM-Link"></div>

<div id="root-bulletproof-mode" style="padding-left:10px;border-bottom:1px solid #999999;">

<?php $bps_secureroot = ( isset( $_POST['Submit-Secure-Root'] ) ) ? $_POST['Submit-Secure-Root'] : ''; ?>

<form name="BulletProof-Root" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_root_copy'); ?>
	<label for="root-bulletproof-mode">
    <input name="bpsecureroot" type="radio" value="bulletproof" class="tog" <?php checked( $bps_secureroot, 'bulletproof' ); ?> /> 
	<?php $text = __('Activate Root Folder BulletProof Mode', 'bulletproof-security'); echo $text; ?></label><br /><br />
 	<label for="root-bulletproof-mode">
    <input name="bpsecureroot" type="radio" value="default" class="tog" <?php checked( $bps_secureroot, 'default' ); ?> />
	<?php $text = '<font color="red">'.__('Deactivate Root Folder BulletProof Mode (Default Mode) CAUTION: ', 'bulletproof-security').'</font>'.__('Use Default Mode for Testing, Troubleshooting or BPS removal.', 'bulletproof-security').'<br>'; echo $text; ?></label>
	<input type="submit" name="Submit-Secure-Root" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Activate|Deactivate', 'bulletproof-security') ?>" class="button bps-button" onclick="return confirm('<?php $text = __('Reminders:', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Did you create your Master htaccess files using the AutoMagic buttons?', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Did you backup your existing htaccess files?', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Do you have any custom htaccess code in your Root htaccess file that you want to save before Activating BulletProof Mode?', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Clicking OK will overwrite your existing Root htaccess file.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Activate BulletProof Mode for your Root folder or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</form>

</div>

<h3><?php _e('Activate|Deactivate wp-admin Folder BulletProof Mode (WBM)', 'bulletproof-security'); ?></h3>

<div id="PFWScan-Menu-Link"></div>

<div id="wpadmin-bulletproof-mode" style="padding-left:10px;border-bottom:1px solid #999999;">

<?php $bps_securewpadmin = ( isset( $_POST['Submit-Secure-wpadmin'] ) ) ? $_POST['Submit-Secure-wpadmin'] : ''; ?>

<form name="BulletProof-WPadmin" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_wpadmin_copy'); ?>
	<label for="wpadmin-bulletproof-mode">
    <input name="bpsecurewpadmin" type="radio" value="bulletproof" class="tog" <?php checked( $bps_securewpadmin ); ?> /> 
	<?php $text = __('Activate wp-admin Folder BulletProof Mode', 'bulletproof-security'); echo $text; ?></label><br /><br />
	<label for="wpadmin-bulletproof-mode">
    <input name="bpsecurewpadmin" type="radio" value="default" class="tog" <?php checked( $bps_securewpadmin ); ?> /> 
	<?php $text = '<font color="red">'.__('Deactivate wp-admin Folder BulletProof Mode CAUTION: ', 'bulletproof-security').'</font>'.__('Deactivate for Testing, Troubleshooting or BPS removal.', 'bulletproof-security').'<br>'; echo $text; ?></label>
	<input type="submit" name="Submit-Secure-wpadmin" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Activate|Deactivate', 'bulletproof-security') ?>" class="button bps-button" onclick="return confirm('<?php $text = __('Reminders:', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Did you backup your existing htaccess files?', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Do you have any custom htaccess code in your wp-admin htaccess file that you want to save before Activating BulletProof Mode?', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Clicking OK will overwrite your existing wp-admin htaccess file.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Activate BulletProof Mode for your wp-admin folder or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
</form>

<form name="wpadminEnableDisable" action="options.php" method="post">
	<?php settings_fields('bulletproof_security_options_htaccess_res'); ?> 
	<?php $BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res'); ?>
	<strong><label for="wpadmin-res"><?php _e('Enable|Disable wp-admin BulletProof Mode (GDMW Hosting):', 'bulletproof-security'); ?></label></strong><br />
	<strong><label for="wpadmin-res" style="color:#2ea2cc;"> <?php _e('This is a custom option (not required). Click the Read Me help button above.', 'bulletproof-security'); ?></label></strong><br />
<select name="bulletproof_security_options_htaccess_res[bps_wpadmin_restriction]" style="width:280px;margin-top:5px;">
<option value="enabled" <?php selected('enabled', $BPS_wpadmin_Options['bps_wpadmin_restriction']); ?>><?php _e('Enable wp-admin BulletProof Mode', 'bulletproof-security'); ?></option>
<option value="disabled" <?php selected('disabled', $BPS_wpadmin_Options['bps_wpadmin_restriction']); ?>><?php _e('Disable wp-admin BulletProof Mode', 'bulletproof-security'); ?></option>
</select>
<input type="submit" name="Submit-Enable-Disable-wpadmin" class="button bps-button" style="margin:5px 0px 10px 0px;" value="<?php esc_attr_e('Enable|Disable', 'bulletproof-security') ?>" />
</form>

</div>

    <h2><?php _e('Additional (Automated) BulletProof Modes ~ Manual Controls', 'bulletproof-security'); ?></h2>

    <?php $text = __('These additional automated BulletProof Modes are activated automatically.', 'bulletproof-security').'<br>'.__('Use these manual controls if your Server type does not allow these files to be created automatically.', 'bulletproof-security').'<br>'.__('Click the Read Me help button below for additional help information.', 'bulletproof-security');
	echo '<div style="font-size:12px;">';
	echo $text;
	echo '</div>';
	?>
	
<h3><?php _e('Additional (Automated) BulletProof Modes', 'bulletproof-security'); ?>  <button id="bps-open-modal6" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content6" title="<?php _e('Additional (Automated) BulletProof Modes', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content6; ?></p>
</div>

<div id="uaeg-bulletproof-mode" style="padding-left:10px;">

<?php $bps_secure_master_folder = ( isset( $_POST['Submit-Master-Folder'] ) ) ? $_POST['Submit-Master-Folder'] : ''; ?>

<form name="BulletProof-deny-all-htaccess" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_master'); ?>
	<label for="denyall-bulletproof-mode">
    <input name="bpssecuremaster" type="radio" value="bulletproof" class="tog" <?php checked( $bps_secure_master_folder ); ?> /> 
	<?php $text = __('Activate Master htaccess BulletProof Mode', 'bulletproof-security'); echo $text; ?></label><br />
	<input type="submit" name="Submit-Master-Folder" class="button bps-button" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</form>

<?php $bps_secure_backup_folder = ( isset( $_POST['Submit-Backup-Folder'] ) ) ? $_POST['Submit-Backup-Folder'] : ''; ?>

<form name="BulletProof-deny-all-backup" action="admin.php?page=bulletproof-security/admin/core/options.php" method="post">
<?php wp_nonce_field('bulletproof_security_denyall_bpsbackup'); ?>
	<label for="denyall-bulletproof-mode">
	<input name="bpssecurebackup" type="radio" value="bulletproof" class="tog" <?php checked( $bps_secure_backup_folder ); ?> /> 
	<?php $text = __('Activate BPS Backup BulletProof Mode', 'bulletproof-security'); echo $text; ?></label><br />
	<input type="submit" name="Submit-Backup-Folder" class="button bps-button" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Activate', 'bulletproof-security') ?>" />
</form>

</div>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

<?php } ?>
</div>
            
<div id="bps-tabs-2" class="bps-tab-page">
<h2><?php _e('htaccess Files Security Status ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Reset|Recheck Dismiss Notices, Turn On|Off Inpage Status Display, Additional Website Security Measures', 'bulletproof-security'); ?></span></h2>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-status_table">
  <tr>
    <td width="49%" class="bps-table_title"><?php _e('Activated BulletProof Security htaccess Files', 'bulletproof-security'); ?></td>
    <td width="2%">&nbsp;</td>
    <td width="49%" class="bps-table_title"><?php _e('Additional Website Security Measures|Dismiss Notices|Status Display','bulletproof-security'); ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell_status">

<?php 
	echo bps_root_htaccess_status();
	echo bps_denyall_htaccess_status_master();
	echo bps_denyall_htaccess_status_backup();
	echo bps_wpadmin_htaccess_status();
?>
    <td>&nbsp;</td>
    <td class="bps-table_cell_status">
<?php 
	echo bps_wpdb_errors_off();
	echo bps_wp_remove_version();
	echo bps_check_admin_username();
	echo bps_filesmatch_check_readmehtml();
	echo bps_filesmatch_check_installphp();

// Reset/Recheck Dismiss Notices
function bpsDeleteUserMetaDismiss() {
	if ( isset( $_POST['bpsResetDismissSubmit'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_reset_dismiss_notices' );	  

	global $current_user;
	$user_id = $current_user->ID;

	if ( ! delete_user_meta($user_id, 'bps_ignore_iis_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Windows IIS Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Windows IIS check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_bonus_code_dismiss_all_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Dismiss All Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Dismiss All Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_brute_force_login_protection_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Brute Force Login Protection Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Brute Force Login Protection Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_speed_boost_cache_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Speed Boost Cache Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Speed Boost Cache Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_author_enumeration_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Author Enumeration BOT Probe Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Author Enumeration BOT Probe Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_xmlrpc_ddos_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: XML-RPC DDoS Protection Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: XML-RPC DDoS Protection Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_referer_spam_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Referer Spam|Phishing Protection Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Referer Spam|Phishing Protection Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_sniff_driveby_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: Mime Sniffing|Drive-by Download Attack Protection Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: Mime Sniffing|Drive-by Download Attack Protection Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_iframe_clickjack_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The Bonus Custom Code: External iFrame|Clickjacking Protection Code Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Bonus Custom Code: External iFrame|Clickjacking Protection Code Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_ignore_PhpiniHandler_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The PHP/php.ini handler htaccess code check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The PHP/php.ini handler htaccess code check Notice is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_ignore_Permalinks_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Custom Permalinks HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Custom Permalinks HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_ignore_sucuri_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Sucuri 1-click Hardening wp-content HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Sucuri 1-click Hardening wp-content HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

	if ( ! delete_user_meta($user_id, 'bps_ignore_wpfirewall2_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('The WordPress Firewall 2 Plugin Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The WordPress Firewall 2 Plugin check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}	

	if ( ! delete_user_meta($user_id, 'bps_ignore_BLC_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The Broken Link Checker plugin HEAD Request Method filter HUD Check Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The Broken Link Checker plugin HEAD Request Method filter HUD Check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}

/* maybe later version - not now
	if ( ! delete_user_meta($user_id, 'bps_ignore_public_username_notice') ) {
		$text = '<div id="message" class="updated fade" style="color:#000000; font-weight:bold; border:1px solid #999999; margin-left:70px; background-color:#ffffe0;"><p>'.__('The username/user account Public Display Dismiss Notice is NOT set. Nothing to reset.', 'bulletproof-security').'</p></div>';
		echo $text;
	} else {
		$text = '<div id="message" class="updated fade" style="color:#008000; font-weight:bold; border:1px solid #999999; margin-left:70px;background-color:#ffffe0;"><p>'.__('Success! The username/user account Public Display check is reset.', 'bulletproof-security').'</p><div class="bps-message-button" style="width:90px;margin-bottom:9px;"><a href="admin.php?page=bulletproof-security/admin/core/options.php">'.__('Refresh Status', 'bulletproof-security').'</a></div></div>';
		echo $text;
	}
*/
	}
}

?>

<div id="ResetDismissNotices" style="position:relative;top:0px;left:0px;">
<form name="bpsResetDismissNotices" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-2" method="post">
<?php wp_nonce_field('bulletproof_security_reset_dismiss_notices'); ?>
    <p><strong><label for="Status-Display" style="color:#000;"><?php _e('Reset|Recheck Dismiss Notices: ', 'bulletproof-security'); ?></label>
<input type="hidden" name="bpsRDN" value="bps-RDN" />
<input type="submit" name="bpsResetDismissSubmit" class="button bps-button" value="<?php esc_attr_e('Reset|Recheck', 'bulletproof-security') ?>" />
</strong></p>
<?php echo bpsDeleteUserMetaDismiss(); ?>
</form>
</div>

<div id="Status-Display" style="">
<form name="Inpage-Status-Display" action="options.php#bps-tabs-2" method="post">
	<?php settings_fields('bulletproof_security_options_status_display'); ?> 
	<?php $bps_status_display = get_option('bulletproof_security_options_status_display'); ?>

	<label for="Status-Display" style="color:#000;"><?php _e('Turn On|Off The Inpage Status Display:', 'bulletproof-security'); ?></label><br />
<select name="bulletproof_security_options_status_display[bps_status_display]" style="width:250px;background:#fff;">
<option value="On" <?php selected('On', $bps_status_display['bps_status_display']); ?>><?php _e('Turn On Status Display', 'bulletproof-security'); ?></option>
<option value="Off" <?php selected('Off', $bps_status_display['bps_status_display']); ?>><?php _e('Turn Off Status Display', 'bulletproof-security'); ?></option>
</select>
<input type="submit" name="Submit-Status-Display" class="button bps-button" style="margin:0px 0px 15px 0px;" value="<?php esc_attr_e('Save Option', 'bulletproof-security') ?>" />
</form>
</div>

  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
    <td>&nbsp;</td>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

<?php } ?>
<br />
</div>
            
<div id="bps-tabs-5" class="bps-tab-page">
<h2><?php _e('htaccess File Backup & Restore ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Backup htaccess Files & Restore htaccess Files', 'bulletproof-security'); ?></span></h2>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3 style="margin:0px 0px 5px 0px;"><?php _e('Backup & Restore htaccess Files', 'bulletproof-security'); ?> <button id="bps-open-modal10" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content10" title="<?php _e('Backup & Restore htaccess Files', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content10; ?></p>
</div>

<?php $bps_backup_htaccess_files = ( isset( $_POST['Submit-Backup-htaccess-Files'] ) ) ? $_POST['Submit-Backup-htaccess-Files'] : ''; ?>

<form name="BulletProof-Backup" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-5" method="post">
<?php wp_nonce_field('bulletproof_security_backup_active_htaccess_files'); ?>
	<label for="backup-htaccess-files" style="font-size:1.13em;">
    <input name="bpsbackuphtaccessfiles" type="radio" value="backup-htaccess-files" class="tog" <?php checked( $bps_backup_htaccess_files ); ?> />
	<?php _e('Backup htaccess Files', 'bulletproof-security'); ?></label><br />
	<input type="submit" name="Submit-Backup-htaccess-Files" class="button bps-button" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Backup htaccess Files', 'bulletproof-security') ?>" />
</form>

<?php $bps_restore_htaccess_files = ( isset( $_POST['Submit-Restore-htaccess-Files'] ) ) ? $_POST['Submit-Restore-htaccess-Files'] : ''; ?>

<form name="BulletProof-Restore" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-5" method="post">
	<?php wp_nonce_field('bulletproof_security_restore_active_htaccess_files'); ?>
	<label for="restore-htaccess-files" style="font-size:1.13em;">
    <input name="bpsrestorehtaccessfiles" type="radio" value="restore-htaccess-files" class="tog" <?php checked( $bps_restore_htaccess_files ); ?> />
	<?php _e('Restore htaccess Files', 'bulletproof-security'); ?></label><br />
	<input type="submit" name="Submit-Restore-htaccess-Files" class="button bps-button" style="margin:10px 0px 10px 0px;" value="<?php esc_attr_e('Restore htaccess Files', 'bulletproof-security') ?>" />
</form>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

<?php } ?>
</div>
        
<div id="bps-tabs-6" class="bps-tab-page">
<h2><?php _e('htaccess File Editor ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Edit BPS Master htaccess Files & Currently Active htaccess Files', 'bulletproof-security'); ?></span></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell">    

<h3 style="margin:0px 0px 5px 5px;"><?php _e('htaccess File Editing', 'bulletproof-security'); ?>  <button id="bps-open-modal14" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>
    
<div id="bps-modal-content14" title="<?php _e('htaccess File Editing', 'bulletproof-security'); ?>">
	<p><?php echo $bps_modal_content14; ?></p>
</div>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { ?>

<table width="100%" border="0">
  <tr>
    <td colspan="2">
    <div id="bps_file_editor" class="bps_file_editor_update">

<?php
echo bps_secure_htaccess_file_check();
echo bps_default_htaccess_file_check();
echo bps_wpadmin_htaccess_file_check();

// Perform File Open and Write test first by appending a literal blank space
// or nothing at all to end of the htaccess files.
// If append write test is successful file is writable on submit
if ( current_user_can('manage_options') ) {
$secure_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
$write_test = "";
	
	if ( is_writable($secure_htaccess_file) ) {
    if ( ! $handle = fopen($secure_htaccess_file, 'a+b') ) {
    	exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
    	exit;
    }
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('File Open and Write test successful! The secure.htaccess Master file is writable.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
	
	if ( file_exists($secure_htaccess_file) ) {
		$text = '<font color="red style="font-size:12px;""><strong>'.__('Cannot write to file: ', 'bulletproof-security').$secure_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit1'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_1' );
		$newcontent1 = stripslashes($_POST['newcontent1']);
	
	if ( is_writable($secure_htaccess_file) ) {
		$handle = fopen($secure_htaccess_file, 'w+b');
		fwrite($handle, $newcontent1);
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Success! The secure.htaccess Master file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$default_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';
$write_test = "";
	
	if ( is_writable($default_htaccess_file) ) {
    if ( !$handle = fopen($default_htaccess_file, 'a+b') ) {
    	exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
    	exit;
    }
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('File Open and Write test successful! The default.htaccess Master file is writable.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
	
	if ( file_exists($default_htaccess_file) ) {
		$text = '<font color="red" style="font-size:12px;"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$default_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit2'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_2' );
		$newcontent2 = stripslashes($_POST['newcontent2']);
	
	if ( is_writable($default_htaccess_file) ) {
		$handle = fopen($default_htaccess_file, 'w+b');
		fwrite($handle, $newcontent2);
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Success! The default.htaccess Master file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$wpadmin_htaccess_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
$write_test = "";
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		$text = '<strong>'.__('wpadmin-secure.htaccess file writing is disabled.', 'bulletproof-security').'</strong><br>';
		echo $text;
	
	} else {

	if ( is_writable($wpadmin_htaccess_file) ) {
    if ( !$handle = fopen($wpadmin_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('File Open and Write test successful! The wpadmin-secure.htaccess Master file is writable.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
	
	if ( file_exists($wpadmin_htaccess_file) ) {
		$text = '<font color="red" style="font-size:12px;"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$wpadmin_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	}
	
	if ( isset( $_POST['submit4'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_4' );
		$newcontent4 = stripslashes($_POST['newcontent4']);
	
	if ( is_writable($wpadmin_htaccess_file) ) {
		$handle = fopen($wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent4);
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Success! The wpadmin-secure.htaccess Master file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$root_htaccess_file = ABSPATH . '.htaccess';
$write_test = "";
	
	if ( is_writable($root_htaccess_file) ) {
    if ( !$handle = fopen($root_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('File Open and Write test successful! Your currently active root .htaccess file is writable.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
	
	if ( file_exists($root_htaccess_file) ) {
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Your root .htaccess file is Locked with Read Only Permissions.', 'bulletproof-security').'<br>'.__('Use the Lock and Unlock buttons below to Lock or Unlock your root .htaccess file for editing.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="red" style="font-size:12px;"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$root_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	
	if ( isset( $_POST['submit5'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_5' );
		$newcontent5 = stripslashes($_POST['newcontent5']);
	
	if ( !is_writable($root_htaccess_file) ) {
		$text = '<font color="red" style="font-size:12px;"><strong>'.__('Error: Unable to write to the Root .htaccess file. If your Root .htaccess file is locked you must unlock first.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}	
	
	if ( is_writable($root_htaccess_file) ) {
		$handle = fopen($root_htaccess_file, 'w+b');
		fwrite($handle, $newcontent5);
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Success! Your currently active root .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

if ( current_user_can('manage_options') ) {
$current_wpadmin_htaccess_file = ABSPATH . 'wp-admin/.htaccess';
$write_test = "";
	
	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' ) {
		$text = '<strong>'.__('wp-admin active htaccess file writing is disabled.', 'bulletproof-security').'</strong><br>';
		echo $text;
	
	} else {

	if ( is_writable($current_wpadmin_htaccess_file) ) {
    if ( !$handle = fopen($current_wpadmin_htaccess_file, 'a+b') ) {
	    exit;
    }
    if ( fwrite($handle, $write_test) === FALSE ) {
	    exit;
    }
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('File Open and Write test successful! Your currently active wp-admin .htaccess file is writable.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
	
	if ( file_exists($current_wpadmin_htaccess_file) ) {
		$text = '<font color="red" style="font-size:12px;"><strong>'.__('Cannot write to file: ', 'bulletproof-security').$current_wpadmin_htaccess_file.'</strong></font><br>';
		echo $text;
	}
	}
	}
	}

	if ( isset( $_POST['submit6'] ) && current_user_can('manage_options') ) {
		check_admin_referer( 'bulletproof_security_save_settings_6' );
		$newcontent6 = stripslashes($_POST['newcontent6']);
	
	if ( is_writable($current_wpadmin_htaccess_file) ) {
		$handle = fopen($current_wpadmin_htaccess_file, 'w+b');
		fwrite($handle, $newcontent6);
		$text = '<font color="green" style="font-size:12px;"><strong>'.__('Success! Your currently active wp-admin .htaccess file has been updated.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
    fclose($handle);
	}
}

// Lock and Unlock Root .htaccess file 
if ( isset( $_POST['submit-ProFlockLock'] ) && current_user_can('manage_options' )) {
	check_admin_referer( 'bulletproof_security_flock_lock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
	
	if ( file_exists($bpsRootHtaccessOL) ) {
		chmod($bpsRootHtaccessOL, 0404);
		$text = '<font color="green" style="font-size:12px;"><strong><br>'.__('Your Root .htaccess file has been Locked.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="red" style="font-size:12px;"><strong><br>'.__('Unable to Lock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}
	
if ( isset( $_POST['submit-ProFlockUnLock'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bulletproof_security_flock_unlock' );
	$bpsRootHtaccessOL = ABSPATH . '.htaccess';
		
	if ( file_exists($bpsRootHtaccessOL) ) {
		chmod($bpsRootHtaccessOL, 0644);
		$text = '<font color="green" style="font-size:12px;"><strong><br>'.__('Your Root .htaccess file has been Unlocked.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	} else {
		$text = '<font color="red" style="font-size:12px;"><strong><br>'.__('Unable to Unlock your Root .htaccess file.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	}
}
?>
</div>
</td>
<td width="33%" align="center" valign="top"></td>
  </tr>
  <tr>
    <td width="22%">

<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if ( @substr($sapi_type, 0, 6) != 'apache' ) {	
?>    
 
 	<div style="margin: 5px;">  
<form name="bpsFlockLockForm" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_lock'); ?>
	<input type="submit" name="submit-ProFlockLock" value="<?php _e('Lock htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onClick="return confirm('<?php $text = __('Click OK to Lock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Unlocked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
	
<form name="bpsRootAutoLock-On" action="options.php#bps-tabs-6" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
	<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="On" />
	<input type="submit" name="submit-RootHtaccessAutoLock-On" value="<?php _e('Turn On AutoLock', 'bulletproof-security'); ?>" class="button bps-button" onClick="return confirm('<?php $text = __('Turning AutoLock On will allow BPS Pro to automatically lock your Root .htaccess file. For some folks this causes a problem because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock On or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />

<?php 
if ( $options['bps_root_htaccess_autolock'] == '' || $options['bps_root_htaccess_autolock'] == 'On' ) { echo '<div id="autolock_status">'.__('On', 'bulletproof-security').'</div>'; } ?>
</form>

</div>

<?php } ?>

</td>
    <td width="45%">

<?php // Detect the SAPI - display form submit button only if sapi is cgi
	$sapi_type = php_sapi_name();
	if ( @substr($sapi_type, 0, 6) != 'apache' ) {	
?>        

	<div style="margin: 5px;">    
<form name="bpsFlockUnLockForm" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_flock_unlock'); ?>

	<input type="submit" name="submit-ProFlockUnLock" value="<?php _e('Unlock htaccess File', 'bulletproof-security'); ?>" class="button bps-button" onClick="return confirm('<?php $text = __('Click OK to Unlock your Root htaccess file or click Cancel.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Note: The File Open and Write Test window will still display the last status of the file as Locked. To see the current status refresh your browser.', 'bulletproof-security'); echo $text; ?>')" />
</form>
<br />
    
<form name="bpsRootAutoLock-Off" action="options.php#bps-tabs-6" method="post">
    <?php settings_fields('bulletproof_security_options_autolock'); ?>
	<?php $options = get_option('bulletproof_security_options_autolock'); ?>
	<input type="hidden" name="bulletproof_security_options_autolock[bps_root_htaccess_autolock]" value="Off" />
	<input type="submit" name="submit-RootHtaccessAutoLock-Off" value="<?php _e('Turn Off AutoLock', 'bulletproof-security'); ?>" class="button bps-button" onClick="return confirm('<?php $text = __('Turning AutoLock Off will prevent BPS Pro from automatically locking your Root .htaccess file. For some folks this is necessary because their Web Hosts do not allow the Root .htaccess file to be locked. For most folks allowing BPS Pro to AutoLock the Root .htaccess file works fine.', 'bulletproof-security').'\n\n'.$bpsSpacePop.'\n\n'.__('Click OK to Turn AutoLock Off or click Cancel.', 'bulletproof-security'); echo $text; ?>')" />
	
<?php 
if ( $options['bps_root_htaccess_autolock'] == 'Off') { echo '<div id="autolock_status">'.__('Off', 'bulletproof-security').'</div>'; } ?>
</form>
</div>

<?php } ?>

</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2">
    
    <!-- jQuery UI File Editor Tab Menu -->
<div id="bps-edittabs" class="bps-edittabs-class">
		<ul>
			<li><a href="#bps-edittabs-1"><?php _e('secure.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-2"><?php _e('default.htaccess', 'bulletproof-security'); ?></a></li>
			<li><a href="#bps-edittabs-4"><?php _e('wpadmin-secure.htaccess', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-5"><?php _e('Your Current Root htaccess File', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-edittabs-6"><?php _e('Your Current wp-admin htaccess File', 'bulletproof-security'); ?></a></li>
        </ul>
       
<?php 
$scrollto1 = isset($_REQUEST['scrollto1']) ? (int) $_REQUEST['scrollto1'] : 0; 
$scrollto2 = isset($_REQUEST['scrollto2']) ? (int) $_REQUEST['scrollto2'] : 0;
$scrollto4 = isset($_REQUEST['scrollto4']) ? (int) $_REQUEST['scrollto4'] : 0;
$scrollto5 = isset($_REQUEST['scrollto5']) ? (int) $_REQUEST['scrollto5'] : 0;
$scrollto6 = isset($_REQUEST['scrollto6']) ? (int) $_REQUEST['scrollto6'] : 0;
?>

<div id="bps-edittabs-1" class="bps-edittabs-page-class">
<form name="template1" id="template1" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_1'); ?>
    <div>
    <textarea class="bps-text-area-600x700" name="newcontent1" id="newcontent1" tabindex="1"><?php echo bps_get_secure_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($secure_htaccess_file) ?>" />
	<input type="hidden" name="scrollto1" id="scrollto1" value="<?php echo $scrollto1; ?>" />
    <p class="submit">
	<input type="submit" name="submit1" class="button bps-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template1').submit(function(){ $('#scrollto1').val( $('#newcontent1').scrollTop() ); });
	$('#newcontent1').scrollTop( $('#scrollto1').val() ); 
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-2" class="bps-edittabs-page-class">
<form name="template2" id="template2" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_2'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent2" id="newcontent2" tabindex="2"><?php echo bps_get_default_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($default_htaccess_file) ?>" />
	<input type="hidden" name="scrollto2" id="scrollto2" value="<?php echo $scrollto2; ?>" />
    <p class="submit">
	<input type="submit" name="submit2" class="button bps-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template2').submit(function(){ $('#scrollto2').val( $('#newcontent2').scrollTop() ); });
	$('#newcontent2').scrollTop( $('#scrollto2').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-4" class="bps-edittabs-page-class">
<form name="template4" id="template4" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_4'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent4" id="newcontent4" tabindex="4"><?php echo bps_get_wpadmin_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto4" id="scrollto4" value="<?php echo $scrollto4; ?>" />
    <p class="submit">
	<input type="submit" name="submit4" class="button bps-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template4').submit(function(){ $('#scrollto4').val( $('#newcontent4').scrollTop() ); });
	$('#newcontent4').scrollTop( $('#scrollto4').val() );
});
/* ]]> */
</script>     
</div>

<?php
// File Editor Root .htaccess file Lock check with pop up Confirm message
function bpsStatusRHE() {
clearstatcache();
$file = ABSPATH . '.htaccess';
$perms = @substr(sprintf('%o', fileperms($file)), -4);
$sapi_type = php_sapi_name();
	
	if ( file_exists($file) && @substr( $sapi_type, 0, 6) != 'apache' ) {		
	return $perms;
	}
}
?>

<div id="bps-edittabs-5" class="bps-edittabs-page-class">
<form name="template5" id="template5" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_5'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent5" id="newcontent5" tabindex="5"><?php echo bps_get_root_htaccess(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($root_htaccess_file) ?>" />
	<input type="hidden" name="scrollto5" id="scrollto5" value="<?php echo $scrollto5; ?>" />
    <p class="submit">
    
	<?php if ( @bpsStatusRHE($perms) == '0404' ) { ?>
	<input type="submit" name="submit5" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" class="button bps-button" onClick="return confirm('<?php $text = __('YOUR ROOT HTACCESS FILE IS LOCKED.', 'bulletproof-security').'\n\n'.__('YOUR FILE EDITS|CHANGES CANNOT BE SAVED.', 'bulletproof-security').'\n\n'.__('Click Cancel, copy the file editing changes you made to save them and then click the Unlock .htaccess File button to unlock your Root .htaccess file. After your Root .htaccess file is unlocked paste your file editing changes back into your Root .htaccess file and click this Update File button again to save your file edits/changes.', 'bulletproof-security'); echo $text; ?>')" />
	<?php } else { ?>
	<input type="submit" name="submit5" class="button bps-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
<?php } ?>

</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template5').submit(function(){ $('#scrollto5').val( $('#newcontent5').scrollTop() ); });
	$('#newcontent5').scrollTop( $('#scrollto5').val() );
});
/* ]]> */
</script>     
</div>

<div id="bps-edittabs-6" class="bps-edittabs-page-class">
<form name="template6" id="template6" action="admin.php?page=bulletproof-security/admin/core/options.php#bps-tabs-6" method="post">
<?php wp_nonce_field('bulletproof_security_save_settings_6'); ?>
	<div>
    <textarea class="bps-text-area-600x700" name="newcontent6" id="newcontent6" tabindex="6"><?php echo bps_get_current_wpadmin_htaccess_file(); ?></textarea>
	<input type="hidden" name="action" value="update" />
    <input type="hidden" name="filename" value="<?php echo esc_attr($current_wpadmin_htaccess_file) ?>" />
	<input type="hidden" name="scrollto6" id="scrollto6" value="<?php echo $scrollto6; ?>" />
    <p class="submit">
	<input type="submit" name="submit6" class="button bps-button" value="<?php esc_attr_e('Update File', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#template6').submit(function(){ $('#scrollto6').val( $('#newcontent6').scrollTop() ); });
	$('#newcontent6').scrollTop( $('#scrollto6').val() );
});
/* ]]> */
</script>     
</div>
</div>

</td>
  </tr>
</table>

<?php } ?>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-7" class="bps-tab-page">
<h2><?php _e('htaccess File Custom Code ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Save Custom htaccess Code for your Root and wp-admin htaccess Files', 'bulletproof-security'); ?> <br /> <span class="cc-read-me-text"><?php _e('* Click the Read Me help button for Custom Code Setup Steps', 'bulletproof-security'); ?></span></span></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<h3 style="margin:0px 0px 5px 0px;"><?php _e('Add Custom htaccess Code For Your Root and wp-admin htaccess Files', 'bulletproof-security'); ?>  <button id="bps-open-modal16" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content16" title="<?php _e('Custom Code', 'bulletproof-security'); ?>">
	<p>
	<?php
        $text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br>';
		echo $text; 
		// Forum Help Links or of course both
		$text = '<strong><font color="blue">'.__('Forum Help Links: ', 'bulletproof-security').'</font></strong>'; 	
		echo $text;	
	?>
	<strong><a href="http://forum.ait-pro.com/forums/topic/protect-login-page-from-brute-force-login-attacks/" title="Brute Force Login Page Protection code" target="_blank"><?php _e('Brute Force Login Page Protection code', 'bulletproof-security'); ?></a></strong><br /><br />	
	
	<?php echo $bps_modal_content16; ?>
    </p>
</div>

<h3><?php $text = '<strong><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank" title="Link opens in a new Browser window">'.__('Custom Code Video Tutorial', 'bulletproof-security').'</a></strong>'; echo $text; ?></h3>
<h3><?php $text = '<strong><a href="http://forum.ait-pro.com/read-me-first/" target="_blank" title="Link opens in a new Browser window">'.__('BulletProof Security Forum', 'bulletproof-security').'</a></strong>'; echo $text; ?></h3>

<?php 
if ( ! current_user_can('manage_options') ) { 
	_e('Permission Denied', 'bulletproof-security'); 
	
	} else { 

	require_once( WP_PLUGIN_DIR . '/bulletproof-security/admin/core/core-custom-code.php' );
}
?>
<br />

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>

<div id="bps-tabs-9">
<h2><?php _e('Help & FAQ', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
   <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" class="bps-table_cell_help_links"><a href="http://www.ait-pro.com/aitpro-blog/category/bulletproof-security-contributors/" target="_blank"><?php _e('Contributors Page', 'bulletproof-security'); ?></a></td>
    <td width="50%" class="bps-table_cell_help_links"><a href="http://www.ait-pro.com/aitpro-blog/2304/wordpress-tips-tricks-fixes/permalinks-wordpress-custom-permalinks-wordpress-best-wordpress-permalinks-structure/" target="_blank"><?php _e('WP Permalinks - Custom Permalink Structure Help Info', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help_links"><a href="http://forum.ait-pro.com/forums/topic/security-log-event-codes/" target="_blank"><?php _e('Security Log Event Codes', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help_links"><a href="http://www.ait-pro.com/aitpro-blog/2239/bulletproof-security-plugin-support/adding-a-custom-403-forbidden-page-htaccess-403-errordocument-directive-examples/" target="_blank"><?php _e('Adding a Custom 403 Forbidden Page For Your Website', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help_links"><a href="http://forum.ait-pro.com/forums/topic/plugin-conflicts-actively-blocked-plugins-plugin-compatibility/" target="_blank"><?php _e('Forum: Search, Troubleshooting Steps & Post Questions For Assistance', 'bulletproof-security'); ?></a></td>
    <td class="bps-table_cell_help_links"><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank"><?php _e('Custom Code Video Tutorial', 'bulletproof-security'); ?></a></td>
  </tr>
  <tr>
    <td class="bps-table_cell_help_links">&nbsp;</td>
    <td class="bps-table_cell_help_links">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-10">
<h2><?php _e('Whats New in ~ ', 'bulletproof-security'); ?><?php echo $bps_version; ?></h2>
<h3><?php _e('The Whats New page lists new changes made in each new version release of BulletProof Security', 'bulletproof-security'); ?></h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-whats_new_table">
  <tr>
   <td width="1%" class="bps-table_title_no_border">&nbsp;</td>
   <td width="99%" class="bps-table_title_no_border">&nbsp;</td>
  </tr>
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Submenu Name Change|Addition:', 'bulletproof-security').'</strong></h3>'.__('BPS Main Menu > UI|UX Submenu name has been changed to:', 'bulletproof-security').'<br>UI|UX|Theme Skin<br>Processing Spinner<br>WP Toolbar|SLF'; echo $text; ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Feature Name Change: RSK naming convention changed to Script|Style Loader Filter (SLF)', 'bulletproof-security').'</strong></h3>'.__('RSK is a bit too aggressive and is a somewhat offensive naming convention. Cool, but not cool at the same time. Script|Style Loader Filter (SLF) is a logical naming convention and is non-offensive. See the SLF Mod|Description below for additional info.', 'bulletproof-security'); echo $text; ?></td>
  </tr>  
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('SLF Mod|Description:', 'bulletproof-security').'</strong></h3>'.__('In some cases, filtering other plugin and theme scripts from loading in BPS plugin pages causes the BPS plugin pages to hang severely, which means that a new issue/problem is created that is worse than the original issue/problem that SLF was designed to fix/solve. Original problem: BPS plugin pages not displaying visually correct due to other plugin or theme scripts loading in BPS plugin pages. SLF is set to Off by default. SLF has an On|Off setting under the UI|UX menu/page. See the UI Theme Skin|Processing Spinner|WP Toolbar|SLF Read Me help button for additional information.', 'bulletproof-security'); echo $text; ?></td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Bonus Custom Code Dismiss Notice Enhancement|Improvement:', 'bulletproof-security').'</strong></h3>'.__('An additional Dismiss All Notices link|feature has been added to dismiss all Bonus Custom Code notices at the same time. Displayed message: Click the links below to get Bonus Custom Code or click the Dismiss Notice links or click this Dismiss All Notices link. To Reset Dismiss Notices click the Reset|Recheck Dismiss Notices button on the Security Status page.', 'bulletproof-security'); echo $text; ?></td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes|Code Corrections|Enhancements|Misc|CSS|Visual|Other:', 'bulletproof-security').'</strong></h3>'.__('&bull; Cosmetic: Undefined index PHP error suppressed for ISL and ACE User Role checkboxes when WP_DEBUG is turned On.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h2><strong>'.__('Whats New in BPS .52', 'bulletproof-security').'</strong></h2>'; echo $text; ?>
	</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Menu|Page: Idle Session Logout|Auth Cookie Expiration', 'bulletproof-security').'</strong></h3>'.__('BPS Security Main Menu > Idle Session Logout Cookie Expiration', 'bulletproof-security'); echo $text; ?></td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Feature: Idle Session Logout (ISL)', 'bulletproof-security').'</strong></h3><strong><a href="http://forum.ait-pro.com/forums/topic/idle-session-logout-isl-and-authentication-cookie-expiration-ace/" target="_blank" title="ISL|ACE Forum Topic">'.__('ISL|ACE Forum Topic', 'bulletproof-security').'</a></strong>: '.__('Automatically logout idle/inactive Users. ISL uses javascript Event Listeners to monitor Users activity for these ISL events: keyboard key is pressed, mouse button is pressed, mouse is moved, mouse wheel is rolled up or down, finger is placed on the touch surface/screen and finger already placed on the screen is moved across the screen. Option Settings: Turn On|Off, Idle Session Logout Time in Minutes, Idle Session Logout Page URL, User Account Exceptions, Enable|Disable Idle Session Logouts For These User Roles: Administrator, Editor, Author, Contributor, Subscriber and Enable|Disable Idle Session Logouts For TinyMCE Editors. Click the Idle Session Logout|Auth Cookie Expiration Read Me help button for full details.', 'bulletproof-security'); echo $text; ?></td>
  </tr>  
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Feature: Auth Cookie Expiration (ACE)', 'bulletproof-security').'</strong></h3><strong><a href="http://forum.ait-pro.com/forums/topic/idle-session-logout-isl-and-authentication-cookie-expiration-ace/" target="_blank" title="ISL|ACE Forum Topic">'.__('ISL|ACE Forum Topic', 'bulletproof-security').'</a></strong>: '.__('Change the WordPress Authentication Cookie Expiration time. The default WordPress Authentication Cookie Expiration time is 2880 Minutes/2 Days and 20160 Minutes/14 Days if a User checks the Remember Me checkbox when they login. You can change the WordPress Authentication Cookie Expiration time to whatever expiration time setting that you choose. Option Settings: Turn On|Off, Auth Cookie Expiration Time in Minutes, Remember Me Auth Cookie Expiration Time in Minutes, User Account Exceptions, Enable|Disable Auth Cookie Expiration Time For These User Roles: Administrator, Editor, Author, Contributor, Subscriber. Click the Idle Session Logout|Auth Cookie Expiration Read Me help button for full details.', 'bulletproof-security'); echo $text; ?></td>
  </tr>  
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Feature & Root htaccess File Addition: 410 ErrorDocument root htaccess code and template logging file', 'bulletproof-security').'</strong></h3><strong><a href="http://forum.ait-pro.com/forums/topic/410-htaccess-redirect-redirect-html-files-redirect-query-strings-redirect-posts-or-categories/" target="_blank" title="410 Gone Usage Info">'.__('410 Gone Usage Info', 'bulletproof-security').'</a></strong>: '.__('A 410.php template logging file has been created to handle 410 Gone Requests. 410 Gone Requests are logged in the BPS Security Log file. See the 410 Gone Usage Info link above for full details on usage.', 'bulletproof-security'); echo $text; ?></td>
  </tr>  
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Core Enhancement|Improvement: Rogue Script Killer', 'bulletproof-security').'</strong></h3><strong><a href="http://forum.ait-pro.com/forums/topic/remove-plugin-scripts-from-loading-in-other-plugin-pages/" target="_blank" title="Roque Script Killer">'.__('Roque Script Killer Info', 'bulletproof-security').'</a></strong>: '.__('Additional filters added to kill/null Roque scripts and styles in other plugins and themes from loading in BPS plugin pages and breaking BPS plugin js and css scripts. Nulls/Kills Rogue Scripts from loading in BPS plugin pages. Nulls/Kills Rogue Styles from loading in BPS plugin pages. Note: If you are seeing 404 errors in your Security log like this: jquery-ui.piklist.css-roque-script-nulled then see the link above for how to prevent these 404 errors from being logged in your Security Log.', 'bulletproof-security'); echo $text; ?></td>
  </tr>  
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes|Code Corrections|Enhancements|Misc|CSS|Visual|Other:', 'bulletproof-security').'</strong></h3>'.__('&bull; jQuery Custom Classes added to all BPS jQuery code.<br>&bull; CSS and js file name changes: -ui- used in naming convention.<br>&bull; jQuery UI Dialog Read Me Help button hide effect changed from explode to blind.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h2><strong>'.__('Whats New in BPS .51.9', 'bulletproof-security').'</strong></h2>'; echo $text; ?>
	</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('Login Security & Monitoring Automated Email Alert Enhancement|Improvement:', 'bulletproof-security').'</strong></h3>'.__('Special Thanks to: ', 'bulletproof-security').'<a href="https://wordpress.org/support/profile/mewkazoid" title="mewkazoid WordPress Member" target="_blank">'.__('mewkazoid', 'bulletproof-security').'</a>'.__(' for pointing out this useful improvement to BPS Login Security & Monitoring automated email alerts.', 'bulletproof-security').'<br><br>'.__('The Login Security & Monitoring Automated Email Alert now contains additional help information about what to do if your User Account is being repeatedly locked.', 'bulletproof-security').'<br><br><strong>'.__('Brute Force Attack General Info: ', 'bulletproof-security').'</strong>'.__('Automated Brute Force Login attacks by spambots and hackerbots are a regular and ongoing type of website attack. The volume and frequency of Brute Force Login attacks are steadily increasing and will continue to increase. Brute Force attacks make up somewhere in the neighborhood of 85 percent (probably more like 90 percent to 95 percent) of the total of all types of ongoing website attacks these days.  BPS Login Security & Monitoring protects the WordPress Login page from Brute Force attacks, but if your username is publicly known/displayed or can be harvested by automated bots then your user account may get locked very frequently. Here are some additional things you can do to prevent your user account from being locked repeatedly: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/user-account-locked/#post-12634" title="Additional Brute Force Attack Protection Methods" target="_blank">'.__('Additional Brute Force Attack Protection Methods', 'bulletproof-security').'</a>'; echo $text; ?>
     </td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes|Code Corrections|Enhancements|Misc|CSS|Visual|Other:', 'bulletproof-security').'</strong></h3>&bull; BugFix: File Permissions cache issue: Root htaccess file not being re-locked when AutoLock is turned On. Special Thanks to: <a href="http://mike-harrison.com/" title="Mike Harrison" target="_blank">'.__('Mike Harrison', 'bulletproof-security').'</a>'.__(' for reporting this bug.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
   <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h2><strong>'.__('Whats New in BPS .51.8', 'bulletproof-security').'</strong></h2>'; echo $text; ?>
	</td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Feature: Setup Wizard', 'bulletproof-security').'</strong></h3>'.__('The BPS plugin can be setup with literally only 1 click now on the new Setup Wizard page. Setup Wizard Pre-Installation Checks are automatically performed and displayed on the Setup Wizard page. Green font messages mean everything is good. Red and blue font messages are displayed with an exact description of the issue and how to correct the issue. Red font error messages need to be fixed before running the Setup Wizard. Blue font messages can either be a recommendation or a notice about something. Blue font messages do not need to be fixed before running the Setup Wizard. You can re-run the Setup Wizard again at any time. Your existing settings will NOT be overwritten and will be re-saved. Any new or additional settings that the Setup Wizard finds on your website will be saved/setup. A link to the Setup Wizard has been created on the WordPress Plugins page under the BulletProof Security plugin.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Feature: jQuery UI Dialog Form BPS Uninstall Options', 'bulletproof-security').'</strong></h3>'.__('An Uninstall Options link has been created on the WordPress Plugins page under the BulletProof Security plugin. Clicking the Uninstall Options link loads a jQuery UI Dialog Form with 2 uninstall options: BPS Pro Upgrade Uninstall option - If you are upgrading to BPS Pro, select the BPS Pro Upgrade Uninstall option and click the Save Option button or just click the Close button below and do a normal plugin uninstall. Complete BPS Plugin Uninstall option - If you want to completely delete the BPS plugin, all files, Custom Code and BPS database settings, select the Complete BPS Plugin Uninstall option and click the Save Option button.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Option: Login Security Attempts Remaining option and Core Functionality Improvements', 'bulletproof-security').'</strong></h3>'.__('<strong>New Option Attempts Remaining:</strong> You can choose to display a "Login Attempts Remaining X" message when an incorrect password is entered. X is the total number of login attempts left/remaining before the User Account is locked. This new option is enabled by default during BPS upgrades and new installations.', 'bulletproof-security').'<br><br>'.__('<strong>Core Functionality Improvements:</strong> When a User Account is locked out and previous User Account logins were logged|stored in the DB, those previously logged logins and data for those DB Rows is not changed|updated and instead a new DB Row is inserted. This allows for better chronological login tracking and monitoring. Affects both Logging Options - Log All Account Logins and Log Only Account Lockouts options and allows for switching between these Logging Options without affecting functionality or causing issues/problems.', 'bulletproof-security'); echo $text; ?>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('New Bonus Custom Code|Bonus Custom Code Dismiss Notice function Consolidation', 'bulletproof-security').'</strong></h3>'.__('<strong>Bonus Custom Code Dismiss Notice Consolidation:</strong> Combined|consolidated all Bonus Custom Code Notices into 1 Bonus Custom Code Notice function with 1 displayed Notice message instead of having several different displayed Notices. Each Bonus Custom Code contains a link to the Bonus Custom Code and a Dismiss Notice link.', 'bulletproof-security').'<br><a href="http://forum.ait-pro.com/forums/topic/block-referer-spammers-semalt-kambasoft-ranksonic-buttons-for-website/" target="_blank" title="Referer Spammers|Phishing Protection">'.__('Referer Spammers|Phishing Protection', 'bulletproof-security').'</a><br><a href="http://forum.ait-pro.com/forums/topic/mime-sniffing-data-sniffing-content-sniffing-drive-by-download-attack-protection/" target="_blank" title="Mime Sniffing, Data Sniffing, Content Sniffing, Drive-by Download Attack Protection">'.__('Mime Sniffing, Data Sniffing, Content Sniffing, Drive-by Download Attack Protection', 'bulletproof-security').'</a><br><a href="http://forum.ait-pro.com/forums/topic/rssing-com-good-or-bad/" target="_blank" title="External iFrame and Clickjacking Protection">'.__('External iFrame and Clickjacking Protection', 'bulletproof-security').'</a>'; echo $text; ?>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_no_border">&bull;</td>
    <td class="bps-table_cell_no_border"><?php $text = '<h3><strong>'.__('BugFixes|Code Corrections|Enhancements|Misc|CSS|Visual|Other:', 'bulletproof-security').'</strong></h3>'.__('&bull; New BPS Setup & Overview Video tutorial created: <a href="http://forum.ait-pro.com/video-tutorials/#setup-overview-free" target="_blank" title="BPS Setup & Overview Video Tutorial">'.__('BPS Setup & Overview Video Tutorial', 'bulletproof-security').'</a> - link added on the Setup Wizard page and htaccess Core Security Modes page.<br>&bull; WP 4.2 Bug Reported|Ticket created with POC (Proof Of Concept) and solution provided: ', 'bulletproof-security').'<a href="https://core.trac.wordpress.org/ticket/31758" target="_blank" title="WP 4.2 hash anchor Bug">'.__('WP 4.2 hash anchor Bug', 'bulletproof-security').'</a>'.__(' Hash anchors were being stripped of URI\'s. Solution provided to WP folks. Solution implemented by WP folks. No other issues or problems found with WP 4.2 and BPS Pro versions.<br>&bull; WP flush_rewrite_rules function added to BPS complete plugin uninstall function. Creates new default generic WP root htaccess file on BPS complete plugin uninstall.<br>&bull; Dismiss Notice link correction when basename == wp-admin on first Dashboard login.<br>&bull; Custom Code inpage check for default WordPress Rewrite code added in Custom Code text boxes.', 'bulletproof-security'); echo $text; ?>
     </td>
  </tr> 
  <tr>
    <td class="bps-table_cell_no_border">&nbsp;</td>
    <td class="bps-table_cell_no_border">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
    <td class="bps-table_cell_bottom_no_border">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-11" class="bps-tab-page">
<h2><?php _e('My Notes ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('Save Personal Notes and htaccess Code Notes to your WordPress Database', 'bulletproof-security'); ?></span></h2>

<?php if ( !current_user_can('manage_options') ) { _e('Permission Denied', 'bulletproof-security'); } else { 
	$scrolltoNotes = isset($_REQUEST['scrolltoNotes']) ? (int) $_REQUEST['scrolltoNotes'] : 0;
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">

<form name="myNotes" action="options.php#bps-tabs-11" method="post">
	<?php settings_fields('bulletproof_security_options_mynotes'); ?>
	<?php $options = get_option('bulletproof_security_options_mynotes'); ?>
<div>
    <textarea class="bps-text-area-600x700" name="bulletproof_security_options_mynotes[bps_my_notes]" tabindex="1"><?php echo $options['bps_my_notes']; ?></textarea>
    <input type="hidden" name="scrolltoNotes" value="<?php echo $scrolltoNotes; ?>" />
    <p class="submit">
	<input type="submit" name="myNotes_submit" class="button bps-button" value="<?php esc_attr_e('Save My Notes', 'bulletproof-security') ?>" /></p>
</div>
</form>
<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($){
	$('#myNotes').submit(function(){ $('#scrolltoNotes').val( $('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop() ); });
	$('#bulletproof_security_options_mynotes[bps_my_notes]').scrollTop( $('#scrolltoNotes').val() ); 
});
/* ]]> */
</script>
</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
<?php } ?>
</div>

<div id="bps-tabs-12">
<h2><?php _e('BulletProof Security Pro Feature Highlights', 'bulletproof-security'); ?></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td colspan="2" class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td width="62%" valign="top" class="bps-table_cell_help">

<div id="bpsProLogo" style="position:relative; top:0px; left:0px;"><a href="http://affiliates.ait-pro.com/po/" target="_blank" title="Get BulletProof Security Pro">
<img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-pro-logo.png'); ?>" style="float:left;width:361px;-moz-box-shadow:4px 4px 4px #888888;-webkit-box-shadow:4px 4px 4px #888888;box-shadow:4px 4px 4px #888888;" /></a>
</div>

<div id="bpsProText" style="margin:0px 0px 15px 0px;font-size:22px;font-weight:bold;font-style:italic;line-height:22px;text-align:center;">
<?php echo _e('The Ultimate Security Protection', 'bulletproof-security'); ?>

<div id="bpsProLinks" style="margin:15px 0px 10px 0px;font-size:12px;font-weight:bold;font-style:normal;line-height:12px;">
<div class="pro-links"><a href="http://forum.ait-pro.com/video-tutorials/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro One-Click Setup Wizard & Demo Video Tutorial', 'bulletproof-security'); ?></a></div><br /><br />
<div class="pro-links"><a href="http://affiliates.ait-pro.com/" target="_blank" title="Link Opens in New Browser Window"><?php _e('BPS Pro Affiliate Program', 'bulletproof-security'); ?></a></div><br /><br />
<div class="pro-links"><a href="http://www.ait-pro.com/bulletproof-security-pro-flash/bulletproof.html" target="_blank" title="Link Opens in New Browser Window"><?php _e('View All BPS Pro Features', 'bulletproof-security'); ?></a></div>
</div>
</div>

<div id="bpsProFeatures" style="float:left;position:relative;top:0px;left:0px;font-size:14px;">

<?php $text = '<h4><strong>'.__('BulletProof Security Pro Website Security Suite is the complete website security package
for hacker and spammer protection', 'bulletproof-security').'</strong></h4>'; echo $text; ?>

<?php echo '<strong>'; _e('One-Click Setup Wizard|Unlimited Installations: ', 'bulletproof-security'); echo '</strong>'; _e('All BPS Pro security features are setup by the one-click BPS Pro Setup Wizard in less than 1 minute.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('One-Click Upgrades|Unlimited Upgrades: ', 'bulletproof-security'); echo '</strong>'; _e('BPS Pro Plugin upgrade notifications are displayed in your WordPress Dashboard exactly the same way as all other WordPress plugins. All BPS Pro files are automatically updated during the upgrade process and no additional setup steps are required when upgrading. When new features and options are added to new BPS Pro versions those new features and options are automatically setup during BPS Pro upgrades and do not require any additional setup or configuration by you.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('AutoRestore|Quarantine Intrusion Detection and Prevention System (IDPS): ', 'bulletproof-security'); echo '</strong>'; _e('ARQ is a real-time file monitor that automatically AutoRestores and/or Quarantines files. ARQ utilizes countermeasure website security that has the capability to protect all of your website files, both WordPress and non-WordPress files, even if your Web Host Server is hacked or if your FTP password is cracked or stolen. Quarantine Options: Restore File, Delete File and View File. AutoRestore|Quarantine includes Displayed Alerts, Email Alerts and Logging. AutoRestore|Quarantine works seamlessly with WordPress Automatic Updates. The BPS Pro Security Log logs all WP files that were installed and backed up automatically during WordPress Automatic Update installations.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('DB Monitor Intrusion Detection System (IDS): ', 'bulletproof-security'); echo '</strong>'; _e('The DB Monitor (DBM) is an Intrusion Detection System (IDS) that alerts you via email anytime a change/modification occurs in your WordPress database or a new database table is created in your WordPress database. The DB Monitor email alert contains information about what database change/modification occurred and other relevant help info. Your DB Monitor Log also logs any changes/modifications to your WordPress database and other relevant help info. The DBM IDS is similar to the ARQ IDPS where it is the most powerful last line of website security protection defense. If all other outer and inner layers of security protection are penetrated then the most powerful DBM IDS and ARQ IDPS systems kick in and protect your website from attacks/hackers. Even if these powerful security measures are never utilized the most significant benefit is that you know for sure that neither your website files or your WordPress database have been tampered with.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('DB Diff Tool: ', 'bulletproof-security'); echo '</strong>'; _e('The DB Diff Tool compares old database tables from DB backups to current database tables and displays any differences in the data/content of those 2 database tables. The DB Diff Tool allows you to check your WordPress Database if you receive a DB Monitor email alert and do not recognize the database table name change/modification.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Plugin Firewall|Plugin Firewall AutoPilot Mode: ', 'bulletproof-security'); echo '</strong>'; _e('The Plugin Firewall|Plugins BulletProof Mode prevents/blocks/forbids Remote Access to the plugins folder from external sources (remote script execution, hacker recon, remote scanning, remote accessibility, etc.) and only allows internal access to the plugins folder based on this criteria: Domain name, Server IP Address and Public IP|Your Computer IP Address. True IP based Firewall that updates your IP addres in real-time when it changes. AutoPilot Mode automatically creates plugin whitelist rules in real-time.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Uploads Folder Anti-Exploit Guard (UAEG): ', 'bulletproof-security'); echo '</strong>'; _e('The Uploads Folder Anti-Exploit Guard|Uploads Folder BulletProof Mode allows ONLY safe image files with valid image file extensions such as jpg, gif, png, etc. to be accessed, opened or viewed from the uploads folder. UAEG prevents/blocks/forbids files by file extension names in the uploads folder from being accessed, opened, viewed, processed or executed.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('JTC Anti-Spam|Anti-Hacker: ', 'bulletproof-security'); echo '</strong>'; _e('Hacker Protection|Spammer Protection|DoS/DDoS Attack Protection|Brute Force Login Attack Protection|SpamBot Trap. JTC Anti-Spam|Anti-Hacker provides website security protection as well as website Anti-Spam protection. JTC Anti-Spam|Anti-Hacker is user friendly Anti-Spam|Anti-Hacker Protection. You can customize and personalize your JTC ToolTip message and CAPTCHA to match your website concept. JTC Anti-Spam|Anti-Hacker protects these website pages/Forms: Login page|Form, Registration page|Form, Lost Password page|Form, Comment page|Form, BuddyPress Register page|Form and the BuddyPress Sidebar Login Form with a user friendly & customizable jQuery ToolTip CAPTCHA.', 'bulletproof-security'); ?><br /><br />

<?php  echo '<strong>'; _e('S-Monitor Displayed Alerts, Email Alerting & Log File Options: ', 'bulletproof-security'); echo '</strong>'; _e('S-Monitor displayed alerting options allow you to choose how you want real-time alerts displayed to you: WP Dashboard, BPS Pro pages only or turned off. Choose whether or not to have email alerts sent when Log files log events. Choose to either automatically Zip and Email Log files to you when they reach the maximum size limit option that you choose or just automatically delete log files when they reach the the maximum size limit option that you choose.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('F-Lock: ', 'bulletproof-security'); echo '</strong>'; _e('Lock and Unlock WordPress Mission Critical files from within your WordPress Dashboard.', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Custom php.ini|ini_set Options: ', 'bulletproof-security'); echo '</strong>'; _e('Quickly create a custom php.ini file for your website or use ini_set Options to increase security and performance with just a few clicks. Additional P-Security Features: All-purpose File Manager, All-purpose File Editor, Protected PHP Error Log, PHP Error Alerts, Secure phpinfo Viewer...', 'bulletproof-security'); ?><br /><br />

<?php echo '<strong>'; _e('Advanced Real-Time Alerting & Heads Up Dashboard Status Display: ', 'bulletproof-security'); echo '</strong>';  _e('BPS Pro checks and displays error, warning, notifications and alert messages in real time. You can choose how you want these messages displayed to you with S-Monitor Monitoring &amp; Alerting Options - Display in your WP Dashboard, BPS Pro pages only, Turned off, Email Alerts, Logging...', 'bulletproof-security'); echo '<br><br>'; ?>
<img src="<?php echo plugins_url('/bulletproof-security/admin/images/dashboard-status-display.png'); ?>" style="-moz-box-shadow:4px 4px 4px #888888;-webkit-box-shadow:4px 4px 4px #888888;box-shadow:4px 4px 4px #888888;"" />
<br /><br />

<?php echo '<strong>'; _e('Pro-Tools: ', 'bulletproof-security'); echo '</strong>'; _e('Pro-Tools is a set of versatile 16 website tools (16 mini-plugins): Online Base64 Decoder, Offline Base64 Decode|Encode, Mcrypt Decrypt|Encrypt, Crypt Encryption, Scheduled Crons, String Finder, String Replacer|Remover, DB String Finder, DB Table Cleaner|Remover, DNS Finder, Ping Website, cURL Scan, Website Headers, WP Automatic Update, Plugin Update Check, XML-RPC Exploit Checker', 'bulletproof-security'); ?><br />
</div>	

    </td>
    <td width="38%" valign="top" class="bps-table_cell_help">

<style>
<!--
#bpsProVersions .pro-links {
	padding:0px 0px 5px 0px;
}
-->
</style> 

<div id="bpsProVersions" style="padding-left:5px;">
<div class="pro-links"><a href="http://forum.ait-pro.com/forums/topic/bulletproof-security-pro-version-release-dates/" target="_blank" title="Link Opens in New Browser Window" style="font-size:22px;"><?php _e('BPS Pro Version Release Dates', 'bulletproof-security'); ?></a></div><br />

<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5169/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10.6', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5157/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10.4/10.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5150/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10.3', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5141/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5109/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5094/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-10/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 10', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5087/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-9-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.9.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5080/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.9', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5075/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.8', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5066/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.7', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5062/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.6', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5056/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5046/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.3/9.4', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5039/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5027/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/5009/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-9-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 9.0', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4994/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.3', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4953/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4940/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4926/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-8-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 8.0', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4916/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.9', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4905/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.8', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4900/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.7', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4895/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.6', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4889/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4876/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-7-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 7.0', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-6-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 6.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4827/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-6-0/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 6.0', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4811/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.9', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4780/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.8/5.8.1/5.8.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4744/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.7/5.7.1/5.7.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4709/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.6/5.6.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4683/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4653/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.4/5.4.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4628/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-3/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.3/5.3.1/5.3.2/5.3.3', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4563/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.2/5.2.1/5.2.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4442/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-9/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.9', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4197/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-8/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.8/5.1.8.1/5.1.8.2/5.1.8.3/5.1.8.4', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4144/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-7/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.7', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/4029/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-6/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.6', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3845/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-5/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.5', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3732/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-4/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.4', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3605/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-3" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.3', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3529/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-2/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.2', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/3510/bulletproof-security-pro/whats-new-in-bulletproof-security-pro-5-1-1/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.1', 'bulletproof-security'); ?></a></div>
<div class="pro-links"><a href="http://www.ait-pro.com/aitpro-blog/2835/bulletproof-security-pro/bulletproof-security-pro-features/" target="_blank" title="Link Opens in New Browser Window"><?php _e('Whats New in BPS Pro 5.0', 'bulletproof-security'); ?></a></div>
</div>  
    
    </td>
  </tr>
   <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
   <tr>
    <td colspan="2" class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>

<div id="bps-tabs-13">
<h2><?php _e('Sucuri SiteCheck - Free website malware & blacklist scan', 'bulletproof-security'); ?></h2>
<h3>
<?php $text = __('BPS is designed to protect your website from being hacked.', 'bulletproof-security').'<br>'.__('If your website was already hacked prior to installing BPS then BPS will not automatically clean it up.', 'bulletproof-security').'<br>'.__('Sucuri offers hacked website cleanup services.', 'bulletproof-security'); echo $text; ?>
</h3>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    
    <div id="SucuriLogo" style="position:relative;top:0px;left:0px;">
    <img src="<?php echo plugins_url('/bulletproof-security/admin/images/sucuri-logo.png'); ?>" style="float:left;padding:0px 10px 0px 0px;margin:0px;" />
    <h3 style="font-size:14px;padding-top:10px;">
	
	<?php 
	$text = '<em>"...'.__('the sheer nature of malware makes it very challenging to give you 100% certainty you will not get infected. The good news though is that we are doing everything in our power to ensure that 1 - you do not get infected, but 2 - if you do, we have the best solution to get you back on your feet.', 'bulletproof-security').'"</em><br> -- '.__('Tony Perez, CFO Sucuri, LLC', 'bulletproof-security');
	echo $text;
	?>
    
    </h3>
    <div class="pro-links">
    <a href="http://sitecheck.sucuri.net/" target="_blank" title="Link opens in new browser window" style="float:left;">Sucuri SiteCheck Scanner</a>
    </div>
    </div>    
    
    </td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>
</div>
        
<div id="AITpro-link">BulletProof Security <?php echo BULLETPROOF_VERSION; ?> Plugin by <a href="http://www.ait-pro.com/" target="_blank" title="AITpro Website Security">AITpro Website Security</a>
</div>
</div>
</div>
</div>