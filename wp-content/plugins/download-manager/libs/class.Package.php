<?php
/**
 * Not Completed Yet!
 */

class WPDMPackage {

    public $ID;
    public $PackageData;

    function __construct($id = null){
        $this->ID = $id;
        return $this;
    }

    function Prepare($ID)
    {
        if (isset($this->PackageData['formatted'])) return $this;

        $vars = get_post($ID, ARRAY_A);

        $vars['title'] = stripcslashes($vars['post_title']);
        $vars['description'] = stripcslashes($vars['post_content']);
        $vars['description'] = wpautop(stripslashes($vars['description']));
        $vars['description'] = do_shortcode(stripslashes($vars['description']));
        $vars['excerpt'] = stripcslashes(strip_tags($vars['post_excerpt']));
        $src = wp_get_attachment_image_src(get_post_thumbnail_id($vars['ID']), 'full', false, '');
        $vars['preview'] = $src['0'];
        $vars['create_date'] = isset($vars['post_date'])?date_i18n(get_option('date_format'), strtotime($vars['post_date'])):'';
        $vars['categories'] = get_the_term_list( $vars['ID'], 'wpdmcategory', '', ', ', '' );

        //print_r($vars); die();
        $data = wpdm_custom_data($vars['ID']);

        $vars = array_merge($vars, $data);

        $vars['update_date'] = isset($vars['post_modified'])?date_i18n(get_option('date_format'), strtotime($vars['post_modified'])):'';

        //$vars['description'] = apply_filters('the_content',stripslashes($wpdm_package['description']));
        $vars['files'] = get_post_meta($vars['ID'], '__wpdm_files', true);
        $vars['file_count'] = count($vars['files']);
        $vars['file_list'] = wpdm_package_file_list($vars);
        $vars['file_list_extended'] = wpdm_package_file_list_alt($vars);
        $vars['link_label'] = isset($vars['link_label']) ? $vars['link_label'] : __('Download', 'wpdmpro');
        $vars['page_link'] = "<a href='" . get_permalink($vars['ID']) . "'>{$vars['title']}</a>";
        $vars['page_url'] = get_permalink($vars['ID']);
        $tags = get_the_tags($vars['ID']);
        $taghtml = "";
        if(is_array($tags)){
            foreach ($tags as $tag)
            {
                $taghtml .= "<a class='btn btn-default btn-xs' style='margin:0 5px 5px 0' href=\""
                    . get_tag_link($tag->term_id)
                    . "\"><i class='fa fa-tag'></i> &nbsp; ".$tag->name."</a> &nbsp;";
            }}
        $vars['tags'] = $taghtml;


        if (count($vars['files']) > 1) $vars['file_ext'] = 'zip';
        if (is_array($vars['files']) && count($vars['files']) == 1) { $tmpdata = explode(".", $vars['files'][0]); $vars['file_ext'] = end($tmpdata); }
        $vars['file_size'] = wpdm_package_size($vars['ID']);

        //$vars['create_date'] = $vars['create_date']?@date(get_option('date_format'),$vars['create_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'create_date'));
        //$vars['update_date'] = $vars['update_date']?@date(get_option('date_format'),$vars['update_date']):@date(get_option('date_format'),get_wpdm_meta($vars['ID'],'update_date'));

        $type = (get_post_type() != 'wpdmpro' || !array_key_exists(get_option('__wpdm_purl_base', 'download'), $wp_query->query_vars)) ? 'link' : 'page';
        $vars['audio_player'] = wpdm_audio_playlist($vars, true);
        $vars['audio_player_single'] = wpdm_audio_player($vars, true);
        //$vars['quick_download'] = wpdm_ddl_button($vars,$type=='link');
        //$vars['email_download'] = wpdm_email_button($vars,$type=='link');

        if (!isset($vars['icon']) || $vars['icon'] == '') {
            if(is_array($vars['files'])){
                $ifn = @end($vars['files']);
                $ifn = @explode('.', $ifn);
                $ifn = @end($ifn);
            }
            else
                $ifn = '_blank';

            $vars['icon'] = '<img class="wpdm_icon" src="' . plugins_url('download-manager/file-type-icons/') . (@count($vars['files']) <= 1 ? $ifn : 'zip') . '.png" onError=\'this.src="' . plugins_url('download-manager/file-type-icons/_blank.png') . '";\' />';
        }
        else if (!strpos($vars['icon'], '://'))
            $vars['icon'] = '<img class="wpdm_icon"   src="' . plugins_url($vars['icon']) . '" />';
        else if (!strpos($vars['icon'], ">"))
            $vars['icon'] = '<img class="wpdm_icon"   src="' . $vars['icon'] . '" />';

        if (isset($vars['preview']) && $vars['preview'] != '') {
            $vars['thumb'] = "<img title='' src='" . wpdm_dynamic_thumb($vars['preview'], array(400, 300)) . "'/>";
        } else
            $vars['thumb'] = $vars['thumb_page'] = $vars['thumb_gallery'] = $vars['thumb_widget'] = "";

        $k = 1;
        $vars['additional_previews'] = isset($vars['more_previews']) ? $vars['more_previews'] : array();
        $img = "<img id='more_previews_{$k}' title='' class='more_previews' src='" . wpdm_dynamic_thumb($vars['preview'], array(575, 170)) . "'/>\n";
        $tmb = "<a href='#more_previews_{$k}' class='spt'><img title='' src='" . wpdm_dynamic_thumb($vars['preview'], array(100, 45)) . "'/></a>\n";


        //WPMS fix
        global $blog_id;
        if (defined('MULTISITE')) {
            $vars['thumb'] = str_replace(home_url('/files'), ABSPATH . 'wp-content/blogs.dir/' . $blog_id . '/files', $vars['thumb']);
        }


        if (!isset($vars['download_link_called'])) {
            $tmpvar = DownloadLink($vars, 0, array('btnclass' => '[btnclass]'));
            $tmpvar1 = DownloadLink($vars, 1);
            $vars['download_link'] = $tmpvar;
            $vars['download_link_extended'] = $tmpvar1;
            $vars['download_link_called'] = 1;
        }

        $vars = apply_filters("wdm_before_fetch_template", $vars);
        if (!isset($vars['formatted'])) $vars['formatted'] = 0;
        ++$vars['formatted'];

        $this->PackageData =  $vars;
        return $this;
    }

    function get(){

    }

} 