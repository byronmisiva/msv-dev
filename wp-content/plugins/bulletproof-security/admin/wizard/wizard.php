<?php
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

<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#000;">

<?php

$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );
$bpsSpacePop = '-------------------------------------------------------------';

function bpsPro_network_domain_check_wizard() {
	global $wpdb;
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->site'" ) )
		return $wpdb->get_var( "SELECT domain FROM $wpdb->site ORDER BY id ASC LIMIT 1" );
	return false;
}

function bpsPro_get_clean_basedomain_wizard() {
	if ( $existing_domain = bpsPro_network_domain_check_wizard() )
		return $existing_domain;
	$domain = preg_replace( '|https?://|', '', get_option( 'siteurl' ) );
	if ( $slash = strpos( $domain, '/' ) )
		$domain = substr( $domain, 0, $slash );
	return $domain;
}

// Setup Wizard - Create the secure.htaccess Master file and copy it to the WordPress installation folder 
function bpsSetupWizardCreateRootHtaccess() {
global $bps_version;

$bps_get_domain_root = bpsGetDomainRoot();
$bps_get_wp_root_default = bps_wp_get_root_folder();
// Replace ABSPATH = wp-content/plugins
$bps_plugin_dir = str_replace( ABSPATH, '', WP_PLUGIN_DIR );
// Replace ABSPATH = wp-content
$bps_wpcontent_dir = str_replace( ABSPATH, '', WP_CONTENT_DIR );
$successTextBegin = '<font color="green"><strong>';
$successTextEnd = '</strong></font><br>';
$failTextBegin = '<font color="red"><strong>';
$failTextEnd = '</strong></font><br>';	
	
	if ( is_multisite() ) {
	
	$hostname          = bpsPro_get_clean_basedomain_wizard();
	$slashed_home      = trailingslashit( get_option( 'home' ) );
	$base              = parse_url( $slashed_home, PHP_URL_PATH );
	$document_root_fix = str_replace( '\\', '/', realpath( $_SERVER['DOCUMENT_ROOT'] ) );
	$abspath_fix       = str_replace( '\\', '/', ABSPATH );
	$home_path         = 0 === strpos( $abspath_fix, $document_root_fix ) ? $document_root_fix . $base : get_home_path();
	$wp_siteurl_subdir = preg_replace( '#^' . preg_quote( $home_path, '#' ) . '#', '', $abspath_fix );
	$rewrite_base      = ! empty( $wp_siteurl_subdir ) ? ltrim( trailingslashit( $wp_siteurl_subdir ), '/' ) : '';
	$subdomain_install = is_subdomain_install();
	$subdir_match          = $subdomain_install ? '' : '([_0-9a-zA-Z-]+/)?';
	$subdir_replacement_01 = $subdomain_install ? '' : '$1';
	$subdir_replacement_12 = $subdomain_install ? '$1' : '$2';
		
		$ms_files_rewriting = '';
		if ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) {
			$ms_files_rewriting = "\n# uploaded files\nRewriteRule ^";
			$ms_files_rewriting .= $subdir_match . "files/(.+) {$rewrite_base}wp-includes/ms-files.php?file={$subdir_replacement_12} [L]" . "\n";
		}
	}

$BPSCustomCodeOptions = get_option('bulletproof_security_options_customcode');
$bps_get_wp_root_secure = bps_wp_get_root_folder();
$bps_auto_write_secure_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/secure.htaccess';
$bps_auto_write_secure_file_root = ABSPATH . '.htaccess';

$bpsSuccessMessageSec = '<font color="green"><strong>'.__('The secure.htaccess Root Master htaccess file was created successfully.', 'bulletproof-security').'<br>'.__('Root Folder BulletProof Mode activated successfully.', 'bulletproof-security').'</strong></font><br>';

$bpsFailMessageSec = '<font color="red"><strong>'.__('Error: The secure.htaccess Root Master htaccess file and root .htaccess file cannot be created. Root Folder BulletProof Mode has NOT been activated.', 'bulletproof-security').'</strong></font><br><strong>'.__('If your Server configuration is DSO you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').' <a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window">'.__('DSO Setup Steps', 'bulletproof-security').'</a></strong><br>';

if ( ! is_multisite() && $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'] != '' ) {        
$bpsBeginWP = "# CUSTOM CODE WP REWRITE LOOP START\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'], ENT_QUOTES ) . "\n\n";
} else {
$bpsBeginWP = "# WP REWRITE LOOP START
RewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]\n";
}

// Network/Multisite all site types and versions
if ( is_multisite() ) {
if ( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'] != '' ) {    
$bpsMUSDirTop = "# CUSTOM CODE WP REWRITE LOOP START\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'], ENT_QUOTES ) . "\n\n";
} else {
$bpsMUSDirTop = "# WP REWRITE LOOP START
RewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]\n
{$ms_files_rewriting}
# add a trailing slash to /wp-admin
RewriteRule ^{$subdir_match}wp-admin$ {$subdir_replacement_01}wp-admin/ [R=301,L]\n\n";
}

// Network/Multisite all site types and versions
if ( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_end'] != '' ) {    
$bpsMUSDirBottom = "# CUSTOM CODE WP REWRITE LOOP END\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_end'], ENT_QUOTES ) . "\n\n";
} else {
$bpsMUSDirBottom = "RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^{$subdir_match}(wp-(content|admin|includes).*) {$rewrite_base}{$subdir_replacement_12} [L]
RewriteRule ^{$subdir_match}(.*\.php)$ {$rewrite_base}$subdir_replacement_12 [L]
RewriteRule . index.php [L]
# WP REWRITE LOOP END\n";
}
}

$bps_secure_content_top = "#   BULLETPROOF $bps_version >>>>>>> SECURE .HTACCESS     \n\n";

if ( $BPSCustomCodeOptions['bps_customcode_one'] != '' ) {
$bps_secure_phpini_cache = "# CUSTOM CODE TOP PHP/PHP.INI HANDLER/CACHE CODE\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_one'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_phpini_cache = "# PHP/PHP.INI HANDLER/CACHE CODE
# Use BPS Custom Code to add php/php.ini Handler and Cache htaccess code and to save it permanently.
# Most Hosts do not have/use/require php/php.ini Handler htaccess code\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_server_signature'] != '' ) {
$bps_server_signature = "# CUSTOM CODE TURN OFF YOUR SERVER SIGNATURE\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_server_signature'], ENT_QUOTES ) . "\n\n";
} else {
$bps_server_signature = "# TURN OFF YOUR SERVER SIGNATURE
# Suppresses the footer line server version number and ServerName of the serving virtual host
ServerSignature Off\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_directory_index'] != '' ) {        
$bps_secure_directory_list_index = "# CUSTOM CODE DIRECTORY LISTING/DIRECTORY INDEX\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_directory_index'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_directory_list_index = "# DO NOT SHOW DIRECTORY LISTING
# Disallow mod_autoindex from displaying a directory listing
# If a 500 Internal Server Error occurs when activating Root BulletProof Mode 
# copy the entire DO NOT SHOW DIRECTORY LISTING and DIRECTORY INDEX sections of code 
# and paste it into BPS Custom Code and comment out Options -Indexes 
# by adding a # sign in front of it.
# Example: #Options -Indexes
Options -Indexes\n
# DIRECTORY INDEX FORCE INDEX.PHP
# Use index.php as default directory index file. index.html will be ignored.
# If a 500 Internal Server Error occurs when activating Root BulletProof Mode 
# copy the entire DO NOT SHOW DIRECTORY LISTING and DIRECTORY INDEX sections of code 
# and paste it into BPS Custom Code and comment out DirectoryIndex 
# by adding a # sign in front of it.
# Example: #DirectoryIndex index.php index.html /index.php
DirectoryIndex index.php index.html /index.php\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_server_protocol'] != '' ) {        
$bps_secure_brute_force_login = "# CUSTOM CODE BRUTE FORCE LOGIN PAGE PROTECTION\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_server_protocol'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_brute_force_login = "# BRUTE FORCE LOGIN PAGE PROTECTION
# PLACEHOLDER ONLY
# Use BPS Custom Code to add Brute Force Login protection code and to save it permanently.
# See this link: http://forum.ait-pro.com/forums/topic/protect-login-page-from-brute-force-login-attacks/
# for more information.\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_error_logging'] != '' ) {        
$bps_secure_error_logging = "# CUSTOM CODE ERROR LOGGING AND TRACKING\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_error_logging'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_error_logging = "# BPS ERROR LOGGING AND TRACKING
# Use BPS Custom Code to modify/edit/change this code and to save it permanently.
# BPS has premade 403 Forbidden, 400 Bad Request, 410 Gone and 404 Not Found files that are used 
# to track and log 403, 400, 410 and 404 errors that occur on your website. When a hacker attempts to
# hack your website the hackers IP address, Host name, Request Method, Referering link, the file name or
# requested resource, the user agent of the hacker and the query string used in the hack attempt are logged.
# All BPS log files are htaccess protected so that only you can view them. 
# The 400.php, 403.php, 404.php and 410.php files are located in /$bps_plugin_dir/bulletproof-security/
# The 400, 410 and 403 Error logging files are already set up and will automatically start logging errors
# after you install BPS and have activated BulletProof Mode for your Root folder.
# If you would like to log 404 errors you will need to copy the logging code in the BPS 404.php file
# to your Theme's 404.php template file. Simple instructions are included in the BPS 404.php file.
# You can open the BPS 404.php file using the WP Plugins Editor.
# NOTE: By default WordPress automatically looks in your Theme's folder for a 404.php Theme template file.\n
ErrorDocument 400 " . $bps_get_wp_root_secure . $bps_plugin_dir . "/bulletproof-security/400.php
ErrorDocument 401 default
ErrorDocument 403 " . $bps_get_wp_root_secure . $bps_plugin_dir . "/bulletproof-security/403.php
ErrorDocument 404 " . $bps_get_wp_root_secure . "404.php
ErrorDocument 410 " . $bps_get_wp_root_secure . $bps_plugin_dir . "/bulletproof-security/410.php\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_deny_dot_folders'] != '' ) {        
$bps_secure_dot_server_files = "# CUSTOM CODE DENY ACCESS TO PROTECTED SERVER FILES AND FOLDERS\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_deny_dot_folders'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_dot_server_files = "# DENY ACCESS TO PROTECTED SERVER FILES AND FOLDERS
# Use BPS Custom Code to modify/edit/change this code and to save it permanently.
# Files and folders starting with a dot: .htaccess, .htpasswd, .errordocs, .logs
RedirectMatch 403 \.(htaccess|htpasswd|errordocs|logs)$\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_admin_includes'] != '' ) {        
$bps_secure_content_wpadmin = "# CUSTOM CODE WP-ADMIN/INCLUDES\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_admin_includes'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_content_wpadmin = "# WP-ADMIN/INCLUDES
# Use BPS Custom Code to remove this code permanently.
RewriteEngine On
RewriteBase $bps_get_wp_root_secure
RewriteRule ^wp-admin/includes/ - [F]
RewriteRule !^wp-includes/ - [S=3]
RewriteRule ^wp-includes/[^/]+\.php$ - [F]
RewriteRule ^wp-includes/js/tinymce/langs/.+\.php - [F]
RewriteRule ^wp-includes/theme-compat/ - [F]\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_request_methods'] != '' ) {        
$bps_secure_request_methods = "# CUSTOM CODE REQUEST METHODS FILTERED\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_request_methods'], ENT_QUOTES)."\n\n";
} else {
$bps_secure_request_methods = "\n# REQUEST METHODS FILTERED
# If you want to allow HEAD Requests use BPS Custom Code and 
# remove/delete HEAD| from the Request Method filter.
# Example: RewriteCond %{REQUEST_METHOD} ^(TRACE|DELETE|TRACK|DEBUG) [NC]
# The TRACE, DELETE, TRACK and DEBUG Request methods should never be removed.
RewriteCond %{REQUEST_METHOD} ^(HEAD|TRACE|DELETE|TRACK|DEBUG) [NC]
RewriteRule ^(.*)$ - [F]\n\n";
}

$bps_secure_begin_plugins_skip_rules_text = "# PLUGINS/THEMES AND VARIOUS EXPLOIT FILTER SKIP RULES
# To add plugin/theme skip/bypass rules use BPS Custom Code.
# The [S] flag is used to skip following rules. Skip rule [S=12] will skip 12 following RewriteRules.
# The skip rules MUST be in descending consecutive number order: 12, 11, 10, 9...
# If you delete a skip rule, change the other skip rule numbers accordingly.
# Examples: If RewriteRule [S=5] is deleted than change [S=6] to [S=5], [S=7] to [S=6], etc.
# If you add a new skip rule above skip rule 12 it will be skip rule 13: [S=13]\n\n";

// AutoMagic - Plugin/Theme skip/bypass rules
$bps_secure_plugins_themes_skip_rules = '';
if ( $BPSCustomCodeOptions['bps_customcode_two'] != '' ) {
$bps_secure_plugins_themes_skip_rules = "# CUSTOM CODE PLUGIN/THEME SKIP/BYPASS RULES\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_two'], ENT_QUOTES ) . "\n\n";
}

$bps_secure_default_skip_rules = "# Adminer MySQL management tool data populate
RewriteCond %{REQUEST_URI} ^" . $bps_get_wp_root_secure . $bps_plugin_dir . "/adminer/ [NC]
RewriteRule . - [S=12]
# Comment Spam Pack MU Plugin - CAPTCHA images not displaying 
RewriteCond %{REQUEST_URI} ^". $bps_get_wp_root_secure . $bps_wpcontent_dir . "/mu-plugins/custom-anti-spam/ [NC]
RewriteRule . - [S=11]
# Peters Custom Anti-Spam display CAPTCHA Image
RewriteCond %{REQUEST_URI} ^" . $bps_get_wp_root_secure . $bps_plugin_dir . "/peters-custom-anti-spam-image/ [NC] 
RewriteRule . - [S=10]
# Status Updater plugin fb connect
RewriteCond %{REQUEST_URI} ^" . $bps_get_wp_root_secure . $bps_plugin_dir . "/fb-status-updater/ [NC] 
RewriteRule . - [S=9]
# Stream Video Player - Adding FLV Videos Blocked
RewriteCond %{REQUEST_URI} ^" . $bps_get_wp_root_secure . $bps_plugin_dir . "/stream-video-player/ [NC]
RewriteRule . - [S=8]
# XCloner 404 or 403 error when updating settings
RewriteCond %{REQUEST_URI} ^" . $bps_get_wp_root_secure . $bps_plugin_dir . "/xcloner-backup-and-restore/ [NC]
RewriteRule . - [S=7]
# BuddyPress Logout Redirect
RewriteCond %{QUERY_STRING} action=logout&redirect_to=http%3A%2F%2F(.*) [NC]
RewriteRule . - [S=6]
# redirect_to=
RewriteCond %{QUERY_STRING} redirect_to=(.*) [NC]
RewriteRule . - [S=5]
# Login Plugins Password Reset And Redirect 1
RewriteCond %{QUERY_STRING} action=resetpass&key=(.*) [NC]
RewriteRule . - [S=4]
# Login Plugins Password Reset And Redirect 2
RewriteCond %{QUERY_STRING} action=rp&key=(.*) [NC]
RewriteRule . - [S=3]\n\n";

if ( $BPSCustomCodeOptions['bps_customcode_timthumb_misc'] != '' ) {        
$bps_secure_timthumb_misc = "# CUSTOM CODE TIMTHUMB FORBID RFI and MISC FILE SKIP/BYPASS RULE\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_timthumb_misc'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_timthumb_misc = "# TIMTHUMB FORBID RFI and MISC FILE SKIP/BYPASS RULE
# Use BPS Custom Code to modify/edit/change this code and to save it permanently.
# Remote File Inclusion (RFI) security rules
# Note: Only whitelist your additional domains or files if needed - do not whitelist hacker domains or files
RewriteCond %{QUERY_STRING} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC,OR]
RewriteCond %{THE_REQUEST} ^.*(http|https|ftp)(%3A|:)(%2F|/)(%2F|/)(w){0,3}.?(blogger|picasa|blogspot|tsunami|petapolitik|photobucket|imgur|imageshack|wordpress\.com|img\.youtube|tinypic\.com|upload\.wikimedia|kkc|start-thegame).*$ [NC]
RewriteRule .* index.php [F]
# 
# Example: Whitelist additional misc files: (example\.php|another-file\.php|phpthumb\.php|thumb\.php|thumbs\.php)
RewriteCond %{REQUEST_URI} (timthumb\.php|phpthumb\.php|thumb\.php|thumbs\.php) [NC]
# Example: Whitelist additional website domains: RewriteCond %{HTTP_REFERER} ^.*(YourWebsite.com|AnotherWebsite.com).*
RewriteCond %{HTTP_REFERER} ^.*" . $bps_get_domain_root . ".*
RewriteRule . - [S=1]\n\n";
}

if ( $BPSCustomCodeOptions['bps_customcode_bpsqse'] != '' ) {        
$bps_secure_BPSQSE = "# CUSTOM CODE BPSQSE BPS QUERY STRING EXPLOITS\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_bpsqse'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_BPSQSE = "# BEGIN BPSQSE BPS QUERY STRING EXPLOITS
# The libwww-perl User Agent is forbidden - Many bad bots use libwww-perl modules, but some good bots use it too.
# Good sites such as W3C use it for their W3C-LinkChecker. 
# Use BPS Custom Code to add or remove user agents temporarily or permanently from the 
# User Agent filters directly below or to modify/edit/change any of the other security code rules below.
RewriteCond %{HTTP_USER_AGENT} (havij|libwww-perl|wget|python|nikto|curl|scan|java|winhttp|clshttp|loader) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_USER_AGENT} (;|<|>|'|".'"'."|\)|\(|%0A|%0D|%22|%27|%28|%3C|%3E|%00).*(libwww-perl|wget|python|nikto|curl|scan|java|winhttp|HTTrack|clshttp|archiver|loader|email|harvest|extract|grab|miner) [NC,OR]
RewriteCond %{THE_REQUEST} (\?|\*|%2a)+(%20+|\\\\s+|%20+\\\\s+|\\\\s+%20+|\\\\s+%20+\\\\s+)HTTP(:/|/) [NC,OR]
RewriteCond %{THE_REQUEST} etc/passwd [NC,OR]
RewriteCond %{THE_REQUEST} cgi-bin [NC,OR]
RewriteCond %{THE_REQUEST} (%0A|%0D|\\"."\\"."r|\\"."\\"."n) [NC,OR]
RewriteCond %{REQUEST_URI} owssvr\.dll [NC,OR]
RewriteCond %{HTTP_REFERER} (%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{HTTP_REFERER} \.opendirviewer\. [NC,OR]
RewriteCond %{HTTP_REFERER} users\.skynet\.be.* [NC,OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=http:// [NC,OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=(\.\.//?)+ [NC,OR]
RewriteCond %{QUERY_STRING} [a-zA-Z0-9_]=/([a-z0-9_.]//?)+ [NC,OR]
RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC,OR]
RewriteCond %{QUERY_STRING} (\.\./|%2e%2e%2f|%2e%2e/|\.\.%2f|%2e\.%2f|%2e\./|\.%2e%2f|\.%2e/) [NC,OR]
RewriteCond %{QUERY_STRING} ftp\: [NC,OR]
RewriteCond %{QUERY_STRING} http\: [NC,OR] 
RewriteCond %{QUERY_STRING} https\: [NC,OR]
RewriteCond %{QUERY_STRING} \=\|w\| [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)/self/(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} ^(.*)cPath=http://(.*)$ [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^s]*s)+cript.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*embed.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^e]*e)+mbed.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*object.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^o]*o)+bject.*(>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (\<|%3C).*iframe.*(\>|%3E) [NC,OR]
RewriteCond %{QUERY_STRING} (<|%3C)([^i]*i)+frame.*(>|%3E) [NC,OR] 
RewriteCond %{QUERY_STRING} base64_encode.*\(.*\) [NC,OR]
RewriteCond %{QUERY_STRING} base64_(en|de)code[^(]*\([^)]*\) [NC,OR]
RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2}) [OR]
RewriteCond %{QUERY_STRING} ^.*(\(|\)|<|>|%3c|%3e).* [NC,OR]
RewriteCond %{QUERY_STRING} ^.*(\\x00|\\x04|\\x08|\\x0d|\\x1b|\\x20|\\x3c|\\x3e|\\x7f).* [NC,OR]
RewriteCond %{QUERY_STRING} (NULL|OUTFILE|LOAD_FILE) [OR]
RewriteCond %{QUERY_STRING} (\.{1,}/)+(motd|etc|bin) [NC,OR]
RewriteCond %{QUERY_STRING} (localhost|loopback|127\.0\.0\.1) [NC,OR]
RewriteCond %{QUERY_STRING} (<|>|'|%0A|%0D|%27|%3C|%3E|%00) [NC,OR]
RewriteCond %{QUERY_STRING} concat[^\(]*\( [NC,OR]
RewriteCond %{QUERY_STRING} union([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} union([^a]*a)+ll([^s]*s)+elect [NC,OR]
RewriteCond %{QUERY_STRING} \-[sdcr].*(allow_url_include|allow_url_fopen|safe_mode|disable_functions|auto_prepend_file) [NC,OR]
RewriteCond %{QUERY_STRING} (;|<|>|'|".'"'."|\)|%0A|%0D|%22|%27|%3C|%3E|%00).*(/\*|union|select|insert|drop|delete|update|cast|create|char|convert|alter|declare|order|script|set|md5|benchmark|encode) [NC,OR]
RewriteCond %{QUERY_STRING} (sp_executesql) [NC]
RewriteRule ^(.*)$ - [F]
# END BPSQSE BPS QUERY STRING EXPLOITS\n";
}

$bps_secure_wp_rewrite_loop_end = "RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . " . $bps_get_wp_root_secure . "index.php [L]
# WP REWRITE LOOP END\n";

if ( $BPSCustomCodeOptions['bps_customcode_deny_files'] != '' ) {        
$bps_secure_deny_browser_access = "# CUSTOM CODE DENY BROWSER ACCESS TO THESE FILES\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_deny_files'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_deny_browser_access = "\n# DENY BROWSER ACCESS TO THESE FILES 
# Use BPS Custom Code to modify/edit/change this code and to save it permanently.
# wp-config.php, bb-config.php, php.ini, php5.ini, readme.html
# Replace 88.77.66.55 with your current IP address and remove the  
# pound sign # in front of the Allow from line of code below to be able to access
# any of these files directly from your Browser.\n
<FilesMatch ".'"'."^(wp-config\.php|php\.ini|php5\.ini|readme\.html|bb-config\.php)".'"'.">
Order Allow,Deny
Deny from all
#Allow from 88.77.66.55
</FilesMatch>\n\n";
}

// AutoMagic - CUSTOM CODE BOTTOM
$bps_secure_bottom_misc_code = '';
if ( $BPSCustomCodeOptions['bps_customcode_three'] != '' ) {
$bps_secure_bottom_misc_code = "# CUSTOM CODE BOTTOM HOTLINKING/FORBID COMMENT SPAMMERS/BLOCK BOTS/BLOCK IP/REDIRECT CODE\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_three'], ENT_QUOTES ) . "\n\n";
} else {
$bps_secure_bottom_misc_code = "# HOTLINKING/FORBID COMMENT SPAMMERS/BLOCK BOTS/BLOCK IP/REDIRECT CODE
# PLACEHOLDER ONLY
# Use BPS Custom Code to add custom code and save it permanently here.\n";
}

	// A root htaccess file does NOT exist - create it
	// Do not lock the root htaccess file and do not display a message that the root htaccess file is not locked
	if ( ! file_exists($bps_auto_write_secure_file_root) ) {
		
		// Single/Standard WordPress site type: Create secure.htaccess Master File
		if ( ! is_multisite() ) {

			$stringReplace = file_get_contents($bps_auto_write_secure_file);

			if ( file_exists($bps_auto_write_secure_file) ) {
				$stringReplace = $bps_secure_content_top.$bps_secure_phpini_cache.$bps_server_signature.$bps_secure_directory_list_index.$bps_secure_brute_force_login.$bps_secure_error_logging.$bps_secure_dot_server_files.$bps_secure_content_wpadmin.$bpsBeginWP.$bps_secure_request_methods.$bps_secure_begin_plugins_skip_rules_text.$bps_secure_plugins_themes_skip_rules.$bps_secure_default_skip_rules.$bps_secure_timthumb_misc.$bps_secure_BPSQSE.$bps_secure_wp_rewrite_loop_end.$bps_secure_deny_browser_access.$bps_secure_bottom_misc_code;		
		
				if ( file_put_contents( $bps_auto_write_secure_file, $stringReplace ) ) {
					@copy($bps_auto_write_secure_file, $bps_auto_write_secure_file_root);
    		
					echo $bpsSuccessMessageSec;
		
				} else {
		
    				echo $bpsFailMessageSec;
				}
			}
		}

		// Network site type: Create secure.htaccess Master File
		if ( is_multisite() && is_super_admin() ) { 

			$stringReplace = file_get_contents($bps_auto_write_secure_file);

			if ( file_exists($bps_auto_write_secure_file) ) {
				$stringReplace = $bps_secure_content_top.$bps_secure_phpini_cache.$bps_server_signature.$bps_secure_directory_list_index.$bps_secure_brute_force_login.$bps_secure_error_logging.$bps_secure_dot_server_files.$bpsMUSDirTop.$bps_secure_request_methods.$bps_secure_begin_plugins_skip_rules_text.$bps_secure_plugins_themes_skip_rules.$bps_secure_default_skip_rules.$bps_secure_timthumb_misc.$bps_secure_BPSQSE.$bpsMUSDirBottom.$bps_secure_deny_browser_access.$bps_secure_bottom_misc_code;		
		
				if ( file_put_contents( $bps_auto_write_secure_file, $stringReplace ) ) {
					@copy($bps_auto_write_secure_file, $bps_auto_write_secure_file_root);
    		
					echo $bpsSuccessMessageSec;
		
				} else {
		
    				echo $bpsFailMessageSec;
				}
			}
		}
	} // end if ( ! file_exists($bps_auto_write_secure_file_root) ) {

	// A root htaccess file exists - backup the existing root htaccess file first.
	// Only create a new root htaccess file if the PHP/php.ini handler issue does not exist else return.
	// root htaccess file backup to /master-backups
	$bps_master_backup_root_file = WP_CONTENT_DIR . '/bps-backup/master-backups/root.htaccess';
	
	if ( is_dir( WP_CONTENT_DIR . '/bps-backup/master-backups' ) ) {
		@copy($bps_auto_write_secure_file_root, $bps_master_backup_root_file);
		echo $successTextBegin.$bps_master_backup_root_file.__(' Root .htaccess File backup Successful!', 'bulletproof-security').$successTextEnd;
	}

	// PHP/php.ini handler check: continue or return and do not create a root htaccess file
	$rootHtaccessContents = @file_get_contents($bps_auto_write_secure_file_root);
	
	preg_match_all( '/AddHandler|SetEnv PHPRC|suPHP_ConfigPath|Action application/', $rootHtaccessContents, $Rootmatches );
	preg_match_all( '/AddHandler|SetEnv PHPRC|suPHP_ConfigPath|Action application/', $BPSCustomCodeOptions['bps_customcode_one'], $DBmatches );
		
	if ( $Rootmatches[0] && ! $DBmatches[0] ) {
		echo '<br><font color="red"><strong>'.__('Error: PHP/php.ini handler htaccess code check', 'bulletproof-security').'</strong></font><br>'.__('PHP/php.ini handler htaccess code was found in your root .htaccess file, but was NOT found in BPS Custom Code. A new root .htaccess file was NOT created to prevent a possible problem occurring on your website. Click this Forum Link ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/pre-installation-wizard-checks-phpphp-ini-handler-htaccess-code-check/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('Add PHP/php.ini handler htaccess code to BPS Custom Code', 'bulletproof-security').'</a></strong>'.__(' for instructions on how to copy your PHP/php.ini handler htaccess code to BPS Custom Code.', 'bulletproof-security').'<br><br>';	
	
	return;
	}		
		
	$permsRootHtaccess = @substr(sprintf('%o', fileperms($bps_auto_write_secure_file_root)), -4);
	$sapi_type = php_sapi_name();
	
	if 	( file_exists( $bps_auto_write_secure_file_root) && @$permsRootHtaccess == '0404' ) {
		$lock = '0404';
	} elseif ( file_exists( $bps_auto_write_secure_file_root) && @$permsRootHtaccess == '0444' ) {
		$lock = '0444';			
	}

	if ( file_exists( $bps_auto_write_secure_file_root) && @substr( $sapi_type, 0, 6) != 'apache' && @$permsRootHtaccess != '0666' || @$permsRootHtaccess != '0777' ) { 
		@chmod($bps_auto_write_secure_file_root, 0644);
	}	

	// Single/Standard WordPress site type: Create secure.htaccess Master File
	if ( ! is_multisite() ) {

		$stringReplace = file_get_contents($bps_auto_write_secure_file);

		if ( file_exists($bps_auto_write_secure_file) ) {
			$stringReplace = $bps_secure_content_top.$bps_secure_phpini_cache.$bps_server_signature.$bps_secure_directory_list_index.$bps_secure_brute_force_login.$bps_secure_error_logging.$bps_secure_dot_server_files.$bps_secure_content_wpadmin.$bpsBeginWP.$bps_secure_request_methods.$bps_secure_begin_plugins_skip_rules_text.$bps_secure_plugins_themes_skip_rules.$bps_secure_default_skip_rules.$bps_secure_timthumb_misc.$bps_secure_BPSQSE.$bps_secure_wp_rewrite_loop_end.$bps_secure_deny_browser_access.$bps_secure_bottom_misc_code;		
		
			if ( file_put_contents( $bps_auto_write_secure_file, $stringReplace ) ) {
				@copy($bps_auto_write_secure_file, $bps_auto_write_secure_file_root);
    		
				echo $bpsSuccessMessageSec;
		
			} else {
		
    			echo $bpsFailMessageSec;
			}
		}

		if ( $lock == '0404' ) {	
			@chmod($bps_auto_write_secure_file_root, 0404);
			echo $successTextBegin.__('Root .htaccess File writing completed. File Locked with 404 file permissions.', 'bulletproof-security').$successTextEnd;
		}
		if ( $lock == '0444' ) {	
			@chmod($bps_auto_write_secure_file_root, 0444);
			echo $successTextBegin.__('Root .htaccess File writing completed. File Locked with 444 file permissions.', 'bulletproof-security').$successTextEnd;
		}
	}

	// Network site type: Create secure.htaccess Master File
	if ( is_multisite() && is_super_admin() ) { 

		$stringReplace = file_get_contents($bps_auto_write_secure_file);

		if ( file_exists($bps_auto_write_secure_file) ) {
			$stringReplace = $bps_secure_content_top.$bps_secure_phpini_cache.$bps_server_signature.$bps_secure_directory_list_index.$bps_secure_brute_force_login.$bps_secure_error_logging.$bps_secure_dot_server_files.$bpsMUSDirTop.$bps_secure_request_methods.$bps_secure_begin_plugins_skip_rules_text.$bps_secure_plugins_themes_skip_rules.$bps_secure_default_skip_rules.$bps_secure_timthumb_misc.$bps_secure_BPSQSE.$bpsMUSDirBottom.$bps_secure_deny_browser_access.$bps_secure_bottom_misc_code;		
		
			if ( file_put_contents( $bps_auto_write_secure_file, $stringReplace ) ) {
				@copy($bps_auto_write_secure_file, $bps_auto_write_secure_file_root);
    		
				echo $bpsSuccessMessageSec;
		
			} else {
		
    			echo $bpsFailMessageSec;
			}
		}
	
		if ( $lock == '0404' ) {	
			@chmod($bps_auto_write_secure_file_root, 0404);
			echo $successTextBegin.__('Root .htaccess File writing completed. File Locked with 404 file permissions.', 'bulletproof-security').$successTextEnd;
		}
		if ( $lock == '0444' ) {	
			@chmod($bps_auto_write_secure_file_root, 0444);
			echo $successTextBegin.__('Root .htaccess File writing completed. File Locked with 444 file permissions.', 'bulletproof-security').$successTextEnd;
		}	
	}

	// AutoLock: Off by default or echo saved DB option
	// For 444 permissions do not do anything with lock or autolock settings
	// Pending: add a condition for 444 permissions throughout all BPS code
	if ( $lock != '0444' ) {	
	
		$BPS_autolock_options = get_option('bulletproof_security_options_autolock');
		$bps_autolock_options = 'bulletproof_security_options_autolock';

		if ( ! get_option( $bps_autolock_options ) ) {	
		
			$bps_autolock_values = array( 'bps_root_htaccess_autolock' => 'Off' );
		
			foreach( $bps_autolock_values as $key => $value ) {
				update_option('bulletproof_security_options_autolock', $bps_autolock_values);
				echo $successTextBegin.$key.__(' DB Option created or updated Successfully!', 'bulletproof-security').$successTextEnd;	
			}
	
		} else {

			$bps_autolock_values = array( 'bps_root_htaccess_autolock' => $BPS_autolock_options['bps_root_htaccess_autolock'] );
		
			foreach( $bps_autolock_values as $key => $value ) {
				update_option('bulletproof_security_options_autolock', $bps_autolock_values);
				echo $successTextBegin.$key.__(' DB Option created or updated Successfully!', 'bulletproof-security').$successTextEnd;	
			}
		}
	}
}

// Setup Wizard - Create wpadmin-secure.htaccess htaccess file and copy it to the /wp-admin folder
function bpsSetupWizardCreateWpadminHtaccess() {
$options = get_option('bulletproof_security_options_customcode_WPA');  

$bpsSuccessMessageSec = '<font color="green"><strong>'.__('The wpadmin-secure.htaccess wp-admin Master htaccess file was created successfully.', 'bulletproof-security').'<br>'.__('wp-admin Folder BulletProof Mode activated successfully.', 'bulletproof-security').'</strong></font><br>';

$bpsFailMessageSec = '<font color="red"><strong>'.__('Error: The wpadmin-secure.htaccess wp-admin Master htaccess file and wp-admin .htaccess file cannot be created. wp-admin Folder BulletProof Mode has NOT been activated.', 'bulletproof-security').'</strong></font><br><strong>'.__('If your Server configuration is DSO you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').' <a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window">'.__('DSO Setup Steps', 'bulletproof-security').'</a></strong><br>';

	$BPS_wpadmin_Options = get_option('bulletproof_security_options_htaccess_res');
	$GDMW_options = get_option('bulletproof_security_options_GDMW');	
	
	if ( $BPS_wpadmin_Options['bps_wpadmin_restriction'] == 'disabled' || $GDMW_options['bps_gdmw_hosting'] == 'yes' ) {
		$text = '<font color="blue"><strong>'.__('Go Daddy Managed WordPress Hosting option is set to Yes or wp-admin BulletProof Mode is disabled on the Security Modes page. GDMW hosting does not allow wp-admin htaccess files.', 'bulletproof-security').'</strong></font><br>';
		echo $text;
	return;
	}

	$wpadminMasterHtaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/wpadmin-secure.htaccess';
	$bps_master_backup_wpadmin_file = WP_CONTENT_DIR . '/bps-backup/master-backups/wpadmin.htaccess';
	$wpadminActiveHtaccess = ABSPATH . 'wp-admin/.htaccess';
	$permsHtaccess = @substr(sprintf('%o', fileperms($wpadminActiveHtaccess)), -4);
	$sapi_type = php_sapi_name();
	$bpsString1 = "# CCWTOP";
	$bpsString2 = "# CCWPF";
	$bpsString3 = '/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s';
	$bpsString4 = '/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s';
	$bpsReplace1 = htmlspecialchars_decode($options['bps_customcode_one_wpa'], ENT_QUOTES);
	$bpsReplace2 = htmlspecialchars_decode($options['bps_customcode_two_wpa'], ENT_QUOTES);
	$bpsReplace3 = htmlspecialchars_decode($options['bps_customcode_deny_files_wpa'], ENT_QUOTES);	
	$bpsReplace4 = htmlspecialchars_decode($options['bps_customcode_bpsqse_wpa'], ENT_QUOTES);	
	
	// backup an existing wp-admin htaccess file first.
	if ( file_exists($wpadminActiveHtaccess) ) {

		if ( is_dir( WP_CONTENT_DIR . '/bps-backup/master-backups' ) ) {
			@copy($wpadminActiveHtaccess, $bps_master_backup_wpadmin_file);
			echo '<font color="green"><strong>'.$bps_master_backup_wpadmin_file.__(' wp-admin .htaccess File backup Successful!', 'bulletproof-security').'</strong></font><br>';
		}
	}
	
	if ( @substr($sapi_type, 0, 6) != 'apache' || @$permsHtaccess != '0666' || @$permsHtaccess != '0777') { // Windows IIS, XAMPP, etc
		@chmod($wpadminActiveHtaccess, 0644);
	}

	if ( @copy($wpadminMasterHtaccess, $wpadminActiveHtaccess) ) {
		echo $bpsSuccessMessageSec;
	} else {
		echo $bpsFailMessageSec;	
	}
	
	if ( file_exists($wpadminActiveHtaccess) ) {
		$bpsBaseContent = @file_get_contents($wpadminActiveHtaccess);
		
		if ( $options['bps_customcode_deny_files_wpa'] != '') {        
			$bpsBaseContent = preg_replace('/#\sBEGIN\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES(.*)#\sEND\sBPS\sWPADMIN\sDENY\sACCESS\sTO\sFILES/s', $bpsReplace3, $bpsBaseContent);
		}
		
		if ( $options['bps_customcode_bpsqse_wpa'] != '') {        
			$bpsBaseContent = preg_replace('/#\sBEGIN\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS(.*)#\sEND\sBPSQSE-check\sBPS\sQUERY\sSTRING\sEXPLOITS\sAND\sFILTERS/s', $bpsReplace4, $bpsBaseContent);
		}
		$bpsBaseContent = str_replace($bpsString1, $bpsReplace1, $bpsBaseContent);
		$bpsBaseContent = str_replace($bpsString2, $bpsReplace2, $bpsBaseContent);
		@file_put_contents($wpadminActiveHtaccess, $bpsBaseContent);

	}
}

// Setup Wizard - Create the default.htaccess htaccess file
function bpsSetupWizardCreateDefaultHtaccess() {
global $bps_version;

$bps_get_wp_root_default = bps_wp_get_root_folder();
	
	if ( is_multisite() ) {
	
	$hostname          = bpsPro_get_clean_basedomain_wizard();
	$slashed_home      = trailingslashit( get_option( 'home' ) );
	$base              = parse_url( $slashed_home, PHP_URL_PATH );
	$document_root_fix = str_replace( '\\', '/', realpath( $_SERVER['DOCUMENT_ROOT'] ) );
	$abspath_fix       = str_replace( '\\', '/', ABSPATH );
	$home_path         = 0 === strpos( $abspath_fix, $document_root_fix ) ? $document_root_fix . $base : get_home_path();
	$wp_siteurl_subdir = preg_replace( '#^' . preg_quote( $home_path, '#' ) . '#', '', $abspath_fix );
	$rewrite_base      = ! empty( $wp_siteurl_subdir ) ? ltrim( trailingslashit( $wp_siteurl_subdir ), '/' ) : '';
	$subdomain_install = is_subdomain_install();
	$subdir_match          = $subdomain_install ? '' : '([_0-9a-zA-Z-]+/)?';
	$subdir_replacement_01 = $subdomain_install ? '' : '$1';
	$subdir_replacement_12 = $subdomain_install ? '$1' : '$2';
		
		$ms_files_rewriting = '';
		if ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) {
			$ms_files_rewriting = "\n# uploaded files\nRewriteRule ^";
			$ms_files_rewriting .= $subdir_match . "files/(.+) {$rewrite_base}wp-includes/ms-files.php?file={$subdir_replacement_12} [L]" . "\n";
		}
	}

$BPSCustomCodeOptions = get_option('bulletproof_security_options_customcode');

$bpsSuccessMessageSec = '<font color="green"><strong>'.__('The default.htaccess Default Mode Master htaccess file was created successfully.', 'bulletproof-security').'</strong></font><br>';

$bpsFailMessageSec = '<font color="red"><strong>'.__('Error: The default.htaccess Default Mode Master htaccess file cannot be created.', 'bulletproof-security').'</strong></font><br><strong>'.__('If your Server configuration is DSO you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').' <a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window">'.__('DSO Setup Steps', 'bulletproof-security').'</a></strong><br>';

$bps_default_content_top = "#   BULLETPROOF DEFAULT .HTACCESS      \n
# WARNING!!! THE default.htaccess FILE DOES NOT PROTECT YOUR WEBSITE AGAINST HACKERS
# This is a standard generic htaccess file that does NOT provide any website security
# The DEFAULT .HTACCESS file should be used for testing and troubleshooting purposes only\n
# BEGIN BPS WordPress\n";

$bps_default_content_bottom = "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . " . $bps_get_wp_root_default . "index.php [L]
</IfModule>\n
# END BPS WordPress";

$bpsMUEndWP = "# END BPS WordPress";

// Network/Multisite all site types and versions
if ( is_multisite() ) {
if ( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'] != '' ) {    
$bpsMUSDirTop = "# CUSTOM CODE WP REWRITE LOOP START\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_start'], ENT_QUOTES ) . "\n\n";
} else {
$bpsMUSDirTop = "# WP REWRITE LOOP START
RewriteEngine On
RewriteBase $bps_get_wp_root_default
RewriteRule ^index\.php$ - [L]\n
{$ms_files_rewriting}
# add a trailing slash to /wp-admin
RewriteRule ^{$subdir_match}wp-admin$ {$subdir_replacement_01}wp-admin/ [R=301,L]\n\n";
}

// Network/Multisite all site types and versions
if ( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_end'] != '' ) {    
$bpsMUSDirBottom = "# CUSTOM CODE WP REWRITE LOOP END\n" . htmlspecialchars_decode( $BPSCustomCodeOptions['bps_customcode_wp_rewrite_end'], ENT_QUOTES ) . "\n\n";
} else {
$bpsMUSDirBottom = "RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]
RewriteRule ^{$subdir_match}(wp-(content|admin|includes).*) {$rewrite_base}{$subdir_replacement_12} [L]
RewriteRule ^{$subdir_match}(.*\.php)$ {$rewrite_base}$subdir_replacement_12 [L]
RewriteRule . index.php [L]
# WP REWRITE LOOP END\n";
}
}

	$bps_auto_write_default_file = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/default.htaccess';

	// Single/Standard WordPress site type: Create default.htaccess Master File
	if ( ! is_multisite() ) {

		$stringReplace = file_get_contents($bps_auto_write_default_file);

	if ( file_exists($bps_auto_write_default_file) ) {
		$stringReplace = $bps_default_content_top.$bps_default_content_bottom;
		
		if ( file_put_contents( $bps_auto_write_default_file, $stringReplace ) ) {
    		
			echo $bpsSuccessMessageSec;
		
		} else {
		
    		echo $bpsFailMessageSec;
		}
	}
	}

	// Network site type: Create default.htaccess Master File
	if ( is_multisite() && is_super_admin() ) {

		$stringReplace = file_get_contents($bps_auto_write_default_file);

	if ( file_exists($bps_auto_write_default_file) ) {
		$stringReplace = $bps_default_content_top.$bpsMUSDirTop.$bpsMUSDirBottom.$bpsMUEndWP;
		
		if ( file_put_contents( $bps_auto_write_default_file, $stringReplace ) ) {
    		
			echo $bpsSuccessMessageSec;
		
		} else {
		
    		echo $bpsFailMessageSec;
		}
	}
	}
}

// Pre-Installation Pre-Checks - Check if php/php.ini handler code exists in root .htaccess file, but not in Custom Code
// The bpsSetupWizardCreateRootHtaccess() function will ensure that Custom Code DB options already exist if a php.ini handler is found in the root .htaccess file
// This additional insurance check is needed in cases where users re-run the wizard at a later time & for making error/troubleshooting simpler
function bpsSetupWizardPhpiniHandlerCheck() {
$options = get_option('bulletproof_security_options_customcode');	
$file = ABSPATH . '.htaccess';
$file_contents = @file_get_contents($file);
$successTextBegin = '<font color="green"><strong>';
$successTextEnd = '</strong></font><br>';
$failTextBegin = '<font color="red"><strong>';
$failTextEnd = '</strong></font><br>';	

	if ( file_exists($file) ) {		

		preg_match_all( '/AddHandler|SetEnv PHPRC|suPHP_ConfigPath|Action application/', $file_contents, $matches );
		preg_match_all( '/AddHandler|SetEnv PHPRC|suPHP_ConfigPath|Action application/', $options['bps_customcode_one'], $DBmatches );
		
		if ( ! $matches[0] ) {
		echo $successTextBegin.__('Pass! PHP/php.ini handler htaccess code check: Currently not in use, required or needed for your website/server', 'bulletproof-security').$successTextEnd;
		return;
		}
	
		if ( $matches[0] && $DBmatches[0] ) {
		echo $successTextBegin.__('Pass! PHP/php.ini handler htaccess code was found in your root .htaccess file AND in BPS Custom Code', 'bulletproof-security').$successTextEnd;
		}
		
		if ( $matches[0] && ! $DBmatches[0] ) {
			echo '<br>'.$failTextBegin.__('Error: PHP/php.ini handler htaccess code check', 'bulletproof-security').$failTextEnd.'<br>'.__('PHP/php.ini handler htaccess code was found in your root .htaccess file, but was NOT found in BPS Custom Code. Do NOT click the Setup Wizard button yet and instead click this Forum Link ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/pre-installation-wizard-checks-phpphp-ini-handler-htaccess-code-check/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('Add php.ini handler htaccess code to BPS Custom Code', 'bulletproof-security').'</a></strong>'.__(' for instructions on how to copy your PHP/php.ini handler htaccess code to BPS Custom Code before running the Setup Wizard.', 'bulletproof-security').'<br><br>';	
		
		}
	}
}

// Setup Wizard - DB Backup is setup in admin.php on BPS installation.
// if someone uninstalls BPS and runs the setup wizard again then the db options need to be updated
// with the db backup folder and db backup download URL
function bpsSetupWizard_dbbackup_folder_check() {
$successTextBegin = '<font color="green"><strong>';
$dbb_successMessage = __(' DB Option created or updated Successfully!', 'bulletproof-security');
$successMessage2 = __(' Folder created Successfully!', 'bulletproof-security');
$successTextEnd = '</strong></font><br>';
$failTextBegin = '<font color="red"><strong>';
$failTextEnd = '</strong></font><br>';

	if ( current_user_can('manage_options') ) {

		$DBBoptions = get_option('bulletproof_security_options_db_backup');
	
	if ( $DBBoptions['bps_db_backup_folder'] != '' ) {	
		
		$DBB_Options = array(
		'bps_db_backup' 						=> $DBBoptions['bps_db_backup'], 
		'bps_db_backup_description' 			=> $DBBoptions['bps_db_backup_description'], 
		'bps_db_backup_folder' 					=> $DBBoptions['bps_db_backup_folder'], 
		'bps_db_backup_download_link' 			=> $DBBoptions['bps_db_backup_download_link'], 
		'bps_db_backup_job_type' 				=> $DBBoptions['bps_db_backup_job_type'], 
		'bps_db_backup_frequency' 				=> $DBBoptions['bps_db_backup_frequency'], 
		'bps_db_backup_start_time_hour' 		=> $DBBoptions['bps_db_backup_start_time_hour'], 
		'bps_db_backup_start_time_weekday' 		=> $DBBoptions['bps_db_backup_start_time_weekday'],  
		'bps_db_backup_start_time_month_date' 	=> $DBBoptions['bps_db_backup_start_time_month_date'], 
		'bps_db_backup_email_zip' 				=> $DBBoptions['bps_db_backup_email_zip'], 
		'bps_db_backup_delete' 					=> $DBBoptions['bps_db_backup_delete'], 
		'bps_db_backup_status_display' 			=> $DBBoptions['bps_db_backup_status_display'] 
		);
		
		echo $successTextBegin.$DBBoptions['bps_db_backup_folder'].$successMessage2.$successTextEnd;	
		
		foreach( $DBB_Options as $key => $value ) {
			update_option('bulletproof_security_options_db_backup', $DBB_Options);
			echo $successTextBegin.$key.$dbb_successMessage.$successTextEnd;	
		}		
	
	} else {

		$source = WP_CONTENT_DIR . '/bps-backup';

		if ( is_dir($source) ) {
		
			$iterator = new DirectoryIterator($source);
			
			foreach ( $iterator as $folder ) {
			
				if ( $folder->isDir() && ! $folder->isDot() && preg_match( '/backups_[a-zA-Z0-9]/', $folder ) ) {

					$bps_db_backup_folder = addslashes($source.DIRECTORY_SEPARATOR.$folder);
					$bps_db_backup_download_link = content_url( '/bps-backup/' ) . $folder . '/';
			
					$DBB_Options = array( 
					'bps_db_backup' 						=> 'On', 
					'bps_db_backup_description' 			=> $DBBoptions['bps_db_backup_description'], 
					'bps_db_backup_folder' 					=> $bps_db_backup_folder, 
					'bps_db_backup_download_link' 			=> $bps_db_backup_download_link, 
					'bps_db_backup_job_type' 				=> $DBBoptions['bps_db_backup_job_type'], 
					'bps_db_backup_frequency' 				=> $DBBoptions['bps_db_backup_frequency'], 
					'bps_db_backup_start_time_hour' 		=> $DBBoptions['bps_db_backup_start_time_hour'], 
					'bps_db_backup_start_time_weekday' 		=> $DBBoptions['bps_db_backup_start_time_weekday'], 
					'bps_db_backup_start_time_month_date' 	=> $DBBoptions['bps_db_backup_start_time_month_date'], 
					'bps_db_backup_email_zip' 				=> $DBBoptions['bps_db_backup_email_zip'], 
					'bps_db_backup_delete' 					=> $DBBoptions['bps_db_backup_delete'], 
					'bps_db_backup_status_display' 			=> $DBBoptions['bps_db_backup_status_display'] 
					);
	
					echo $successTextBegin.$bps_db_backup_folder.$successMessage2.$successTextEnd;

					foreach( $DBB_Options as $key => $value ) {
						update_option('bulletproof_security_options_db_backup', $DBB_Options);
						echo $successTextBegin.$key.$dbb_successMessage.$successTextEnd;	
					}			
				}
			}
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

// Setup Wizard - Create/Recreate the User Agent filters in the 403.php file
function bpsSetupWizard_autoupdate_useragent_filters() {		
global $wpdb;
$table_name = $wpdb->prefix . "bpspro_seclog_ignore";
$blankFile = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/blank.txt';
$userAgentMaster = WP_CONTENT_DIR . '/bps-backup/master-backups/UserAgentMaster.txt';
$bps403File = WP_PLUGIN_DIR . '/bulletproof-security/403.php';
$search = '';		

	if ( ! file_exists($bps403File) ) {
		return;
	}
	
	if ( file_exists($blankFile) ) {
		copy($blankFile, $userAgentMaster);
	}

	$getSecLogTable = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_agent_bot LIKE %s", "%$search%" ) );
	$UserAgentRules = array();
	
	if ( $wpdb->num_rows == 0 ) {
		$text = '<strong><font color="green">'.__('Security Log User Agent Filter Check Successful! 0 User Agent Filters to update.', 'bulletproof-security').'</font></strong><br>';
		echo $text;	
	}
	
	if ( $wpdb->num_rows != 0 ) {

		foreach ( $getSecLogTable as $row ) {
			$UserAgentRules[] = "(.*)".$row->user_agent_bot."(.*)|";
			file_put_contents($userAgentMaster, $UserAgentRules);
		
			$text = '<strong><font color="green">'.__('Security Log User Agent Filter ', 'bulletproof-security').$row->user_agent_bot.__(' created or updated Successfully!', 'bulletproof-security').'</font></strong><br>';
			echo $text;
		}
	
	$UserAgentRulesT = file_get_contents($userAgentMaster);
	$stringReplace = file_get_contents($bps403File);

	$stringReplace = preg_replace('/# BEGIN USERAGENT FILTER(.*)# END USERAGENT FILTER/s', "# BEGIN USERAGENT FILTER\nif ( !preg_match('/".trim($UserAgentRulesT, "|")."/', \$_SERVER['HTTP_USER_AGENT']) ) {\n# END USERAGENT FILTER", $stringReplace);
		
	file_put_contents($bps403File, $stringReplace);
		
	}
}

/**************************************************/
// BEGIN BPS Setup Wizard Pre-Installation Checks
/**************************************************/

function bpsSetupWizardPrechecks() {

$successTextBegin = '<font color="green"><strong>';
$successMessage = __(' DB Table created Successfully!', 'bulletproof-security');
$successTextEnd = '</strong></font><br>';
$failTextBegin = '<font color="red"><strong>';
$failMessage = __('Error: Unable to create DB Table ', 'bulletproof-security');
$failTextEnd = '</strong></font><br>';
$sapi_type = php_sapi_name();

	echo '<h3>'.__('Setup Wizard Pre-Installation Checks:', 'bulletproof-security').'</h3>
	<div style="font-size:12px;margin:-10px 0px 10px 0px;font-weight:bold;">'.__('If you see any Red font or Blue font messages displayed below, click the Read Me help button above and read the "Notes" help section before clicking the Setup Wizard button.', 'bulletproof-security').'</div>';   
	
	echo '<div id="Wizard-background" style="max-height:250px;width:85%;overflow:auto;margin:0px;padding:10px;border:2px solid black;background-color:#ffffe0;">';
	
	echo '<span class="setup-wizard-checks-text">';

	if ( @substr($sapi_type, 0, 6) != 'apache' && get_filesystem_method() == 'direct') {
		echo $successTextBegin.__('Pass! Compatible Server Configuration: Server API: CGI | WP Filesystem API Method: direct.', 'bulletproof-security').$successTextEnd;
	}
	elseif ( @substr($sapi_type, 0, 6) == 'apache' && preg_match('#\\\\#', ABSPATH, $matches) && get_filesystem_method() == 'direct') {
		echo $successTextBegin.__('Pass! Compatible Server Configuration: Server Type Apache: XAMPP, WAMP, MAMP or LAMP | WP Filesystem API Method: direct.', 'bulletproof-security').$successTextEnd;	
	}
	elseif ( @substr($sapi_type, 0, 6) == 'apache' && ! preg_match('#\\\\#', ABSPATH, $matches) && get_filesystem_method() == 'direct') {
		echo $successTextBegin.__('Pass! Compatible Server Configuration: Server API: DSO | WP Filesystem API Method: direct.', 'bulletproof-security').$successTextEnd;		
	}
	elseif ( @substr($sapi_type, 0, 6) == 'apache' && get_filesystem_method() != 'direct') {
		echo $failTextBegin.__('Server API: Apache DSO Server Configuration | WP Filesystem API Method: ', 'bulletproof-security').get_filesystem_method().$failTextEnd.'<br>'.__('Your Server type is DSO and the WP Filesystem API Method is NOT "direct". You can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a></strong><br><br>';			
	}
	
	$memoryLimitM = get_cfg_var('memory_limit');
	$memoryLimit = str_replace('M', '', $memoryLimitM);

	if ( $memoryLimit == '' || ! $memoryLimitM ) {
		echo '<strong><font color="blue">'.__('Unable to get the PHP Configuration Memory Limit value from the Server. It is recommended that your PHP Configuration Memory Limit is set to at least 128M. Contact your Web Host and ask them what your PHP Configuration Memory Limit is for your website.', 'bulletproof-security').'</font></strong><br>';

	} else {

switch ( $memoryLimit ) {
    case $memoryLimit >= '128':
		echo $successTextBegin.__('Pass! PHP Configuration Memory Limit is set to: ', 'bulletproof-security').$memoryLimit.'M'.$successTextEnd;		
		break;
    case $memoryLimit >= '64' && $memoryLimit < '128':
		echo $successTextBegin.__('Pass! PHP Configuration Memory Limit is set to: ', 'bulletproof-security').$memoryLimit.'M. '.__('It is recommended that you increase your memory limit to at least 128M. Contact your Web Host and ask them to increase your memory limit to the maximum memory limit setting allowed by your Host.', 'bulletproof-security').$successTextEnd;
		break;
    case $memoryLimit > '0' && $memoryLimit < '64':
		echo '<br>'.$failTextBegin.__('Error: Your PHP Configuration Memory Limit is set to: ', 'bulletproof-security').$memoryLimit.'M. '.__('WordPress needs a bare minimum Memory Limit setting of 64M to perform well. Contact your Web Host and ask them to increase your memory limit to the maximum memory limit setting allowed by your Host.', 'bulletproof-security').$failTextEnd.'<br>';	
		break;
 	}
	}

	// PHP/php.ini htaccess code pre-check - Check if root .htaccess file has php.ini handler code and if that code has been added to BPS Custom Code
	bpsSetupWizardPhpiniHandlerCheck();
	
	// writable checks:
	// folders: /bps-backup/ and /htaccess/ folder
	// files: default.htaccess, secure.htaccess and wpadmin-secure.htaccess
	$htaccess_dir = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess';
	$bps_backup_dir = WP_CONTENT_DIR . '/bps-backup';
	$secureHtaccess = $htaccess_dir . '/secure.htaccess';
	$wpadminHtaccess = $htaccess_dir . '/wpadmin-secure.htaccess';
	$defaultHtaccess = $htaccess_dir . '/default.htaccess';	

	if ( is_writable($htaccess_dir) ) {
		echo $successTextBegin.__('Pass! The ', 'bulletproof-security').$htaccess_dir.__(' Folder is writable.', 'bulletproof-security').$successTextEnd;
	} else {
 		echo $failTextBegin.__('Error: The ', 'bulletproof-security').$htaccess_dir.__(' Folder is NOT writable. If your Server type is DSO and the WP Filesystem API Method is NOT "direct" you can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a>'.__(' If your Server type is CGI check the folder permissions. Folder permissions should be either 755 or 705.', 'bulletproof-security').$failTextEnd.'<br>';
	}

	if ( is_writable($bps_backup_dir) ) {
		echo $successTextBegin.__('Pass! The ', 'bulletproof-security').$bps_backup_dir.__(' Folder is writable.', 'bulletproof-security').$successTextEnd;
	} else {
 		echo $failTextBegin.__('Error: The ', 'bulletproof-security').$bps_backup_dir.__(' Folder is NOT writable. If your Server type is DSO and the WP Filesystem API Method is NOT "direct" you can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a>'.__(' If your Server type is CGI check the folder permissions. Folder permissions should be either 755 or 705.', 'bulletproof-security').$failTextEnd.'<br>';
	}

	if ( is_writable($secureHtaccess) ) {
		echo $successTextBegin.__('Pass! The ', 'bulletproof-security').$secureHtaccess.__(' File is writable.', 'bulletproof-security').$successTextEnd;
	} else {
 		echo $failTextBegin.__('Error: The ', 'bulletproof-security').$secureHtaccess.__(' File is NOT writable. If your Server type is DSO and the WP Filesystem API Method is NOT "direct" you can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a>'.__(' If your Server type is CGI check the file permissions. File permissions should be either 644 or 604.', 'bulletproof-security').$failTextEnd.'<br>';
	}
	
	if ( is_writable($wpadminHtaccess) ) {
		echo $successTextBegin.__('Pass! The ', 'bulletproof-security').$wpadminHtaccess.__(' File is writable.', 'bulletproof-security').$successTextEnd;
	} else {
 		echo $failTextBegin.__('Error: The ', 'bulletproof-security').$wpadminHtaccess.__(' File is NOT writable. If your Server type is DSO and the WP Filesystem API Method is NOT "direct" you can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a>'.__(' If your Server type is CGI check the file permissions. File permissions should be either 644 or 604.', 'bulletproof-security').$failTextEnd.'<br>';
	}

	if ( is_writable($defaultHtaccess) ) {
		echo $successTextBegin.__('Pass! The ', 'bulletproof-security').$defaultHtaccess.__(' File is writable.', 'bulletproof-security').$successTextEnd;
	} else {
 		echo $failTextBegin.__('Error: The ', 'bulletproof-security').$defaultHtaccess.__(' File is NOT writable. If your Server type is DSO and the WP Filesystem API Method is NOT "direct" you can use the Setup Wizard, but you must first make some one-time manual changes to your website before running the Setup Wizard. Please click this Forum Link for instructions: ', 'bulletproof-security').'<a href="http://forum.ait-pro.com/forums/topic/dso-setup-steps/" target="_blank" title="Link opens in a new Browser window"><strong>'.__('DSO Setup Steps', 'bulletproof-security').'</a>'.__(' If your Server type is CGI check the file permissions. File permissions should be either 644 or 604.', 'bulletproof-security').$failTextEnd.'<br>';
	}

	echo '</span>';
	echo '</div>';
}

/**************************************************/
// END BPS Setup Wizard Pre-Installation Checks
/**************************************************/

/****************************************/
// BEGIN BPS Setup Wizard
/****************************************/

function bpsSetupWizard() {

if ( isset( $_POST['Submit-Setup-Wizard'] ) && current_user_can('manage_options') ) {
	check_admin_referer( 'bps_setup_wizard' );
	set_time_limit(300);

global $wpdb, $wp_version, $bps_version;

$time_start = microtime( true );

$Stable_name = $wpdb->prefix . "bpspro_seclog_ignore";
$Ltable_name = $wpdb->prefix . "bpspro_login_security";
$DBBtable_name = $wpdb->prefix . "bpspro_db_backup";

$successTextBegin = '<font color="green"><strong>';
$successMessage = __(' DB Table created Successfully!', 'bulletproof-security');
$successTextEnd = '</strong></font><br>';
$failTextBegin = '<font color="red"><strong>';
$failMessage = __('Error: Unable to create DB Table ', 'bulletproof-security');
$failTextEnd = '</strong></font><br>';

	$bps_setup_wizard = 'bulletproof_security_options_wizard_free';
	$BPS_Wizard = array( 'bps_wizard_free' => 'wizard' );	
	
	if ( ! get_option( $bps_setup_wizard ) ) {	
		
		foreach( $BPS_Wizard as $key => $value ) {
			update_option('bulletproof_security_options_wizard_free', $BPS_Wizard);
		}
	
	} else {

		foreach( $BPS_Wizard as $key => $value ) {
			update_option('bulletproof_security_options_wizard_free', $BPS_Wizard);
		}
	}

	echo '<h3>'.__('BPS Setup Verification & Error Checks', 'bulletproof-security').'</h3>';
	
	echo '<div style="font-size:12px;margin:-10px 0px 10px 0px;font-weight:bold;">'.__('If you see all Green font messages displayed below, the Setup Wizard setup completed successfully.', 'bulletproof-security').'<br>'.__('If you see any Red font or Blue font messages displayed below, click the Read Me help button above and read the "Notes" help section.', 'bulletproof-security').'<br>'.__('Click the Read Me help button above for a list of recommended BPS Video Tutorials to watch.', 'bulletproof-security').'</div>';
	
	echo '<div id="Wizard-background" style="max-height:250px;width:85%;overflow:auto;margin-bottom:20px;padding:10px;border:2px solid black;background-color:#ffffe0;">';
	
	echo '<span class="setup-wizard-checks-text">';

	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Database Tables Setup', 'bulletproof-security').'</div>';
	echo '<div id="SWDBTables" style="border-top:3px solid #999999;margin-top:-10px;"><p>';
	
	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $Stable_name ) ) == $Stable_name ) {
		echo $successTextBegin.$Stable_name.$successMessage.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage.$Stable_name.$failTextEnd;	
	}

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $Ltable_name ) ) == $Ltable_name ) {
		echo $successTextBegin.$Ltable_name.$successMessage.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage.$Ltable_name.$failTextEnd;	
	}

	if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $DBBtable_name ) ) == $DBBtable_name ) {
		echo $successTextBegin.$DBBtable_name.$successMessage.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage.$DBBtable_name.$failTextEnd;	
	}

	echo '</p></div>';	
	
	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Core Folders Setup', 'bulletproof-security').'</div>';
	echo '<div id="SWFolders" style="border-top:3px solid #999999;margin-top:-10px;"><p>';
	
	$successMessage2 = __(' Folder created Successfully!', 'bulletproof-security');
	$failMessage2 = __('Error: Unable to create Folder ', 'bulletproof-security');

	if ( is_dir( WP_CONTENT_DIR . '/bps-backup' ) ) {	
		echo $successTextBegin.WP_CONTENT_DIR . '/bps-backup'.$successMessage2.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage2.WP_CONTENT_DIR . '/bps-backup'.$failTextEnd;	
	}	

	if ( is_dir( WP_CONTENT_DIR . '/bps-backup' ) ) {	
		echo $successTextBegin.WP_CONTENT_DIR . '/bps-backup/master-backups'.$successMessage2.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage2.WP_CONTENT_DIR . '/bps-backup/master-backups'.$failTextEnd;	
	}
	
	if ( is_dir( WP_CONTENT_DIR . '/bps-backup/logs' ) ) {	
		echo $successTextBegin.WP_CONTENT_DIR . '/bps-backup/logs'.$successMessage2.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage2.WP_CONTENT_DIR . '/bps-backup/logs'.$failTextEnd;	
	}

	echo '</p></div>';
	
	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Core Files Setup', 'bulletproof-security').'</div>';
	echo '<div id="SWFiles" style="border-top:3px solid #999999;margin-top:-10px;"><p>';

	$successMessage3 = __(' File created or updated Successfully!', 'bulletproof-security');
	$failMessage3 = __('Error: Unable to create or update File ', 'bulletproof-security');	
	
	bpsSetupWizardCreateRootHtaccess();
	bpsSetupWizardCreateWpadminHtaccess();
	bpsSetupWizardCreateDefaultHtaccess();
	
	$htaccess_dir = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess';
	$secureHtaccess = $htaccess_dir . '/secure.htaccess';
	$wpadminHtaccess = $htaccess_dir . '/wpadmin-secure.htaccess';
	$defaultHtaccess = $htaccess_dir . '/default.htaccess';	
	
	if ( is_writable($secureHtaccess) ) {
		echo $successTextBegin.$secureHtaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$secureHtaccess.$failTextEnd;	
	}

	if ( is_writable($wpadminHtaccess) ) {
		echo $successTextBegin.$wpadminHtaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$wpadminHtaccess.$failTextEnd;	
	}

	if ( is_writable($defaultHtaccess) ) {
		echo $successTextBegin.$defaultHtaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$defaultHtaccess.$failTextEnd;	
	}

	$bps_ARHtaccess = WP_CONTENT_DIR . '/bps-backup/.htaccess';	
	$bpsProDBBLogARQ = WP_CONTENT_DIR . '/bps-backup/logs/db_backup_log.txt';
	$bpsProSecLogARQ = WP_CONTENT_DIR . '/bps-backup/logs/http_error_log.txt';
	
	if ( file_exists($bps_ARHtaccess) ) {
		echo $successTextBegin.$bps_ARHtaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$bps_ARHtaccess.$failTextEnd;	
	}
		
	if ( file_exists($bpsProDBBLogARQ) ) {
		echo $successTextBegin.$bpsProDBBLogARQ.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$bpsProDBBLogARQ.$failTextEnd;	
	}

	if ( file_exists($bpsProSecLogARQ) ) {
		echo $successTextBegin.$bpsProSecLogARQ.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$bpsProSecLogARQ.$failTextEnd;	
	}

	$bps_denyall_htaccess_renamed = WP_PLUGIN_DIR . '/bulletproof-security/admin/htaccess/.htaccess';
	$security_log_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/security-log/.htaccess';
	$system_info_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/system-info/.htaccess';
	$login_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/login/.htaccess';
	$MMode_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/maintenance/.htaccess';
	$DBB_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/db-backup-security/.htaccess';
	$core_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/core/.htaccess';
	$wizard_denyall_htaccess = WP_PLUGIN_DIR . '/bulletproof-security/admin/wizard/.htaccess';	

	if ( file_exists($bps_denyall_htaccess_renamed) ) {
		echo $successTextBegin.$bps_denyall_htaccess_renamed.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$bps_denyall_htaccess_renamed.$failTextEnd;	
	}

	if ( file_exists($security_log_denyall_htaccess) ) {
		echo $successTextBegin.$security_log_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$security_log_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($system_info_denyall_htaccess) ) {
		echo $successTextBegin.$system_info_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$system_info_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($login_denyall_htaccess) ) {
		echo $successTextBegin.$login_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$login_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($MMode_denyall_htaccess) ) {
		echo $successTextBegin.$MMode_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$MMode_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($DBB_denyall_htaccess) ) {
		echo $successTextBegin.$DBB_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$DBB_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($core_denyall_htaccess) ) {
		echo $successTextBegin.$core_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$core_denyall_htaccess.$failTextEnd;	
	}

	if ( file_exists($wizard_denyall_htaccess) ) {
		echo $successTextBegin.$wizard_denyall_htaccess.$successMessage3.$successTextEnd;
	} else {
		echo $failTextBegin.$failMessage3.$wizard_denyall_htaccess.$failTextEnd;	
	}

	echo '</p></div>';

	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security DB Backup Setup', 'bulletproof-security').'</div>';
	echo '<div id="DBBackup" style="border-top:3px solid #999999;margin-top:-10px;"><p>';

	bpsSetupWizard_dbbackup_folder_check();
	
	$successMessage4 = __(' DB Option created or updated Successfully!', 'bulletproof-security');

	$bps_option_name_dbb = 'bulletproof_security_options_DBB_log';
	$bps_new_value_dbb = bpsPro_DBB_LogLastMod_wp_secs();
	$BPS_Options_dbb = array( 'bps_dbb_log_date_mod' => $bps_new_value_dbb );

	if ( ! get_option( $bps_option_name_dbb ) ) {	
		update_option('bulletproof_security_options_DBB_log', $BPS_Options_dbb);
		echo $successTextBegin.$bps_option_name_dbb.$successMessage4.$successTextEnd;
	} else {
		update_option('bulletproof_security_options_DBB_log', $BPS_Options_dbb);
		echo $successTextBegin.$bps_option_name_dbb.$successMessage4.$successTextEnd;
	}	
	
	echo '</p></div>';
	
	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Security Log User Agent Filter Setup', 'bulletproof-security').'</div>';
	echo '<div id="SLuserAgentFilter" style="border-top:3px solid #999999;margin-top:-10px;"><p>';
	bpsSetupWizard_autoupdate_useragent_filters();
	echo '</p></div>';
	
	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Email Alerting & Log File Options Setup', 'bulletproof-security').'</div>';
	echo '<div id="SWSmonitor" style="border-top:3px solid #999999;margin-top:-10px;"><p>';	
	
	$admin_email = get_option('admin_email');
	$successMessage7 = __(' DB Option created or updated Successfully!', 'bulletproof-security');
	
	$bps_option_name7 = 'bulletproof_security_options_email';
	$bps_new_value7 = $admin_email;
	$bps_new_value7_1 = $admin_email;	
	$bps_new_value7_2 = '';
	$bps_new_value7_3 = '';
	$bps_new_value7_4 = 'lockoutOnly';
	$bps_new_value7_9 = '500KB';
	$bps_new_value7_10 = 'email';
	$bps_new_value7_18 = 'email';
	$bps_new_value7_19 = '500KB';	

	$BPS_Options7 = array(
	'bps_send_email_to' 		=> $bps_new_value7, 
	'bps_send_email_from' 		=> $bps_new_value7_1, 
	'bps_send_email_cc' 		=> $bps_new_value7_2, 
	'bps_send_email_bcc' 		=> $bps_new_value7_3, 
	'bps_login_security_email' 	=> $bps_new_value7_4, 
	'bps_security_log_size' 	=> $bps_new_value7_9, 
	'bps_security_log_emailL' 	=> $bps_new_value7_10, 
	'bps_dbb_log_email' 		=> $bps_new_value7_18, 
	'bps_dbb_log_size' 			=> $bps_new_value7_19 
	);

	if ( ! get_option( $bps_option_name7 ) ) {	
		
		foreach( $BPS_Options7 as $key => $value ) {
			update_option('bulletproof_security_options_email', $BPS_Options7);
			echo $successTextBegin.$key.$successMessage7.$successTextEnd;	
		}
	
	} else {

		$BPS_Email_Options = get_option('bulletproof_security_options_email');		
		
		$BPS_Options7 = array(
		'bps_send_email_to' 		=> $BPS_Email_Options['bps_send_email_to'], 
		'bps_send_email_from' 		=> $BPS_Email_Options['bps_send_email_from'], 
		'bps_send_email_cc' 		=> $BPS_Email_Options['bps_send_email_cc'], 
		'bps_send_email_bcc' 		=> $BPS_Email_Options['bps_send_email_bcc'], 
		'bps_login_security_email' 	=> $BPS_Email_Options['bps_login_security_email'], 
		'bps_security_log_size' 	=> $BPS_Email_Options['bps_security_log_size'], 
		'bps_security_log_emailL' 	=> $BPS_Email_Options['bps_security_log_emailL'], 
		'bps_dbb_log_email' 		=> $BPS_Email_Options['bps_dbb_log_email'], 
		'bps_dbb_log_size' 			=> $BPS_Email_Options['bps_dbb_log_size'] 
		);

		foreach( $BPS_Options7 as $key => $value ) {
			update_option('bulletproof_security_options_email', $BPS_Options7);
			echo $successTextBegin.$key.$successMessage7.$successTextEnd;	
		}
	}

	echo '</p></div>';	
	
	echo '<div style="color:black;font-size:1.13em;font-weight:bold;margin-bottom:15px;">'.__('BulletProof Security Login Security & Monitoring Options Setup', 'bulletproof-security').'</div>';
	echo '<div id="SWLoginSecurity" style="border-top:3px solid #999999;margin-top:-10px;"><p>';	
	
	$successMessage8 = __(' DB Option created or updated Successfully!', 'bulletproof-security');

	$bps_option_name8 = 'bulletproof_security_options_login_security';
	$bps_new_value8 = '3';
	$bps_new_value8_1 = '60';	
	$bps_new_value8_2 = '60';
	$bps_new_value8_3 = '';
	$bps_new_value8_4 = 'On';
	$bps_new_value8_5 = 'logLockouts';
	$bps_new_value8_6 = 'wpErrors';
	$bps_new_value8_7 = 'On';
	$bps_new_value8_8 = 'enable';
	$bps_new_value8_9 = 'ascending';
	
	$BPS_Options8 = array(
	'bps_max_logins' 				=> $bps_new_value8, 
	'bps_lockout_duration' 			=> $bps_new_value8_1, 
	'bps_manual_lockout_duration' 	=> $bps_new_value8_2, 
	'bps_max_db_rows_display' 		=> $bps_new_value8_3, 
	'bps_login_security_OnOff' 		=> $bps_new_value8_4, 
	'bps_login_security_logging' 	=> $bps_new_value8_5, 
	'bps_login_security_errors' 	=> $bps_new_value8_6, 
	'bps_login_security_remaining' 	=> $bps_new_value8_7, 
	'bps_login_security_pw_reset' 	=> $bps_new_value8_8,  
	'bps_login_security_sort' 		=> $bps_new_value8_9 
	);

	if ( ! get_option( $bps_option_name8 ) ) {	
		
		foreach( $BPS_Options8 as $key => $value ) {
			update_option('bulletproof_security_options_login_security', $BPS_Options8);
			echo $successTextBegin.$key.$successMessage8.$successTextEnd;	
		}
	
	} else {

		$BPS_LSM_Options = get_option('bulletproof_security_options_login_security');
		
		$BPS_Options_lsm = array(
		'bps_max_logins' 				=> $BPS_LSM_Options['bps_max_logins'], 
		'bps_lockout_duration' 			=> $BPS_LSM_Options['bps_lockout_duration'], 
		'bps_manual_lockout_duration' 	=> $BPS_LSM_Options['bps_manual_lockout_duration'], 
		'bps_max_db_rows_display' 		=> $BPS_LSM_Options['bps_max_db_rows_display'], 
		'bps_login_security_OnOff' 		=> $BPS_LSM_Options['bps_login_security_OnOff'], 
		'bps_login_security_logging' 	=> $BPS_LSM_Options['bps_login_security_logging'], 
		'bps_login_security_errors' 	=> $BPS_LSM_Options['bps_login_security_errors'], 
		'bps_login_security_remaining' 	=> $BPS_LSM_Options['bps_login_security_remaining'], 
		'bps_login_security_pw_reset' 	=> $BPS_LSM_Options['bps_login_security_pw_reset'],  
		'bps_login_security_sort' 		=> $BPS_LSM_Options['bps_login_security_sort'] 
		);

		foreach( $BPS_Options_lsm as $key => $value ) {
			update_option('bulletproof_security_options_login_security', $BPS_Options_lsm);
			echo $successTextBegin.$key.$successMessage8.$successTextEnd;	
		}
	}	
	
	echo '</p></div>';	
	
	echo '</span>';

	echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
	$text = '<strong><font color="green">'.__('The Setup Wizard has completed BPS Setup.', 'bulletproof-security').'<br>'.__('Check the "BPS Setup Verification & Error Checks" section below for any errors in Red Font.', 'bulletproof-security').'</font></strong><br>';
	echo $text;
	echo '</p></div>';

	$time_end = microtime( true );
	$wizard_run_time = $time_end - $time_start;
	$wizard_time_display = '<strong>'.__('Setup Wizard Completion Time: ', 'bulletproof-security').'</strong>'. round( $wizard_run_time, 2 ) . ' Seconds';	
	
	echo '<div id="message" class="updated" style="border:1px solid #999999;margin-left:70px;background-color:#ffffe0;"><p>';
	echo bpsPro_memory_resource_usage();
	echo $wizard_time_display;
	echo '</p></div>';

	echo '</div>';
	} // end if (isset($_POST['Submit-Setup-Wizard'])
}
/****************************************/
// END BPS Setup Wizard
/****************************************/
?>

</div>

<h2 style="margin-left:70px;"><?php _e('BulletProof Security ~ Setup Wizard', 'bulletproof-security'); ?></h2>

<!-- jQuery UI Tab Menu -->
<div id="bps-container">
	<div id="bps-tabs" class="bps-menu">
    <div id="bpsHead" style="position:relative;top:0px;left:0px;"><img src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-security-shield.png'); ?>" style="float:left;padding:0px 8px 0px 0px;margin:-72px 0px 0px 0px;" />
    
<style>
<!--
.bps-spinner {
    visibility:visible;
	position:fixed;
    top:7%;
    left:45%;
 	width:240px;
	background:#fff;
	border:4px solid black;
	padding:2px 0px 4px 8px;   
	z-index:99999;
}

.bps-readme-table {background:#fff;vertical-align:text-top;margin:8px 0px 10px 0px;}
.bps-readme-table-td {padding:5px;}
-->
</style> 

    <div id="bps-spinner" class="bps-spinner" style="visibility:hidden;">
    	<img id="bps-img-spinner" src="<?php echo plugins_url('/bulletproof-security/admin/images/bps-spinner.gif'); ?>" style="float:left;margin:0px 20px 0px 0px;" />
        <div id="bps-spinner-text-btn" style="padding:20px 0px 26px 0px;font-size:14px;">Processing...<br><button style="margin:10px 0px 0px 10px;" onclick="javascript:history.go(-1)">Cancel</button>
		</div>
    </div> 
    
<script type="text/javascript">
/* <![CDATA[ */
function bpsSpinnerSWizard() {
	
    var r = confirm("You can re-run the Setup Wizard again at any time. Your existing settings will NOT be overwritten and will be re-saved. Any new or additional settings that the Setup Wizard finds on your website will be saved/setup.\n\n-------------------------------------------------------------\n\nClick OK to Run the Setup Wizard or click Cancel.");
	
	var img = document.getElementById("bps-spinner"); 

	if (r == true) {
 	
		img.style.visibility = "visible";
	
	} else {
	
		history.go(-1);
	}
}
/* ]]> */
</script>  

    </div>
		<ul>
            <li><a href="#bps-tabs-1"><?php _e('Setup Wizard', 'bulletproof-security'); ?></a></li>
            <li><a href="#bps-tabs-2"><?php _e('Setup Wizard Options', 'bulletproof-security'); ?></a></li>
		</ul>
            
<div id="bps-tabs-1" class="bps-tab-page">

<h2><?php _e('Setup Wizard ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('One-Click Complete Setup', 'bulletproof-security'); ?></span></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    
<h3 style="margin:0px 0px 5px 0px;"><?php _e('Setup Wizard', 'bulletproof-security'); ?>  <button id="bps-open-modal1" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content1" title="<?php _e('Setup Wizard', 'bulletproof-security'); ?>">

 <table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-readme-table">
  <tr>
    <td class="bps-readme-table-td">
	<?php 
	$text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong>'.__('Setup Wizard Steps: ', 'bulletproof-security').'</strong><br>'.__('1. Click the Setup Wizard button.', 'bulletproof-security').'<br><br><strong>'.__('Recommended Video Tutorials: ', 'bulletproof-security').'</strong><br>'; 
	echo $text; 
	?>
	<strong><a href="http://forum.ait-pro.com/video-tutorials/#custom-code" title="Custom Code Video Tutorial" target="_blank"><?php _e('Custom Code Video Tutorial', 'bulletproof-security'); ?></a></strong><br /> 
	<strong><a href="http://forum.ait-pro.com/video-tutorials/#security-log-firewall" title="Security Log Video Tutorial" target="_blank"><?php _e('Security Log Video Tutorial', 'bulletproof-security'); ?></a></strong><br /><br />
	<?php 
	$text = '<strong>'.__('Notes: ', 'bulletproof-security').'</strong><br>'.__('Setup Wizard Pre-Installation Checks are automatically performed and displayed on the Setup Wizard page. Green font messages mean everything is good. Red and blue font messages are displayed with an exact description of the issue and how to correct the issue. Red font error messages need to be fixed before running the Setup Wizard. Blue font messages can either be a recommendation or a notice about something. Blue font messages do not need to be fixed before running the Setup Wizard.', 'bulletproof-security').'<br><br>'.__('You can re-run the Setup Wizard again at any time. Your existing settings will NOT be overwritten and will be re-saved. Any new or additional settings that the Setup Wizard finds on your website will be saved/setup.', 'bulletproof-security').'<br><br>'.__('When the Setup Wizard has completed you will see "The Setup Wizard has completed BPS Setup."', 'bulletproof-security').'<br><br>'.__('Your existing Root and wp-admin htaccess files are backed up before new Root and wp-admin htaccess files are created by the Setup Wizard. The BPS backup folder is here: ', 'bulletproof-security');
	echo $text;
	echo '/' . $bps_wpcontent_dir . '/bps-backup/master-backups/';
	$text = __(' and the backed up htaccess file names are: root.htaccess and wpadmin.htaccess.', 'bulletproof-security'); 
	echo $text;
	?>
    </td>
  </tr> 
</table> 
   
</div>

<?php
$text = '<span class="setup-wizard-inpage-text"><div class="setup-wizard-video-link" style="margin:15px 0px 20px 0px;"><a href="http://forum.ait-pro.com/video-tutorials/#setup-overview-free" target="_blank" title="This Setup Wizard link opens in a new Browser window">'.__('Setup Wizard & Overview Video Tutorial', 'bulletproof-security').'</a></div></span>';
echo $text;

bpsSetupWizardPrechecks();

?>

<form name="bpsSetupWizard" action="admin.php?page=bulletproof-security/admin/wizard/wizard.php" method="post">
	<?php wp_nonce_field('bps_setup_wizard'); ?>

<input type="submit" name="Submit-Setup-Wizard" style="margin:15px 0px 20px 0px;" value="<?php esc_attr_e('Setup Wizard', 'bulletproof-security') ?>" class="button bps-button" onclick="bpsSpinnerSWizard()" />
<?php bpsSetupWizard(); ?>
</form>

</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>
        
<div id="bps-tabs-2" class="bps-tab-page">

<h2><?php _e('Setup Wizard Options ~ ', 'bulletproof-security'); ?><span style="font-size:.75em;"><?php _e('GoDaddy Managed WordPress Hosting (GDMW)', 'bulletproof-security'); ?></span></h2>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-help_faq_table">
  <tr>
    <td class="bps-table_title">&nbsp;</td>
  </tr>
  <tr>
    <td class="bps-table_cell_help">
    
<h3 style="margin:0px 0px 5px 0px;"><?php _e('Setup Wizard Options', 'bulletproof-security'); ?>  <button id="bps-open-modal2" class="button bps-modal-button"><?php _e('Read Me', 'bulletproof-security'); ?></button></h3>

<div id="bps-modal-content2" title="<?php _e('Setup Wizard Options', 'bulletproof-security'); ?>">
	
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="bps-readme-table">
  <tr>
    <td class="bps-readme-table-td">	
	<?php $dialog_text = '<strong>'.__('This Read Me Help window is draggable (top) and resizable (bottom right corner)', 'bulletproof-security').'</strong><br><br><strong>'.__('There is only one Setup Wizard Option setting at this point. More Option settings may be added in a later version of BPS.', 'bulletproof-security').'</strong><br><br>';
	echo $dialog_text;
	$dialog_text = '<strong>'.__('Forum Help Links:', 'bulletproof-security').'</strong><br>';
	echo $dialog_text;
	?>
	<strong><a href="http://forum.ait-pro.com/forums/topic/gdmw/" title="Go Daddy Managed WordPress Hosting (GDMW)" target="_blank"><?php _e('Go Daddy Managed WordPress Hosting (GDMW)', 'bulletproof-security'); ?></a></strong><br /><br />	
	<?php
    $dialog_text = '<strong>'.__('Go Daddy Managed WordPress Hosting (GDMW):', 'bulletproof-security').'</strong><br>'.__('This option is ONLY for a special type of Go Daddy Hosting account called "Managed WordPress Hosting" and is NOT for regular/standard Go Daddy Hosting account types. Leave the default setting set to No, unless you have a Go Daddy Managed WordPress Hosting account. See the Forum Help Links section above for more information.', 'bulletproof-security'); 
	echo $dialog_text; 
	?>
    </td>
  </tr> 
</table> 

</div>

<form name="SetupWizardGDMW" action="options.php#bps-tabs-2" method="post">
	<?php settings_fields('bulletproof_security_options_GDMW'); ?> 
	<?php $GDMWoptions = get_option('bulletproof_security_options_GDMW'); ?>
    
	<label for="wizard-curl"><?php _e('Go Daddy Managed WordPress Hosting (GDMW):', 'bulletproof-security'); ?></label><br />
<select name="bulletproof_security_options_GDMW[bps_gdmw_hosting]" style="width:300px;">
<option value="no" <?php selected('no', $GDMWoptions['bps_gdmw_hosting']); ?>><?php _e('No (default setting)', 'bulletproof-security'); ?></option>
<option value="yes" <?php selected('yes', $GDMWoptions['bps_gdmw_hosting']); ?>><?php _e('Yes (ONLY if you have Managed WordPress Hosting)', 'bulletproof-security'); ?></option>
</select><br />
<input type="submit" name="Submit-Wizard-GDMW" class="button bps-button" style="margin:10px 0px 20px 0px;" value="<?php esc_attr_e('Save GDMW Option', 'bulletproof-security') ?>" />
</form>    

	</td>
  </tr>
  <tr>
    <td class="bps-table_cell_bottom">&nbsp;</td>
  </tr>
</table>

</div>    

<div id="AITpro-link">BulletProof Security <?php echo BULLETPROOF_VERSION; ?> Plugin by <a href="http://forum.ait-pro.com/" target="_blank" title="AITpro Website Security">AITpro Website Security</a>
</div>

</div>
</div>
<style>
<!--
.bps-spinner {visibility:hidden;}
-->
</style>
</div>