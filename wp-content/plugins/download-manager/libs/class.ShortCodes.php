<?php

class ShortCodes
{

    function __construct()
    {
        // Combined front-end UI
        add_shortcode('wpdm_frontend', array($this, 'FrontendUI'));
        add_shortcode('wpdm-frontend', array($this, 'FrontendUI'));

        // Package Form
        add_shortcode('wpdm-package-form', array($this, 'PackageFormFrontend'));

        // List User Packages
        add_shortcode('wpdm-list-packages', array($this, 'PackageListFrontend'));

        // Register form
        add_shortcode('wpdm-reg-form', array($this, 'RegisterForm'));

        // Edit Profile
        add_shortcode('wpdm-edit-profile', array($this, 'EditProfile'));
    }

    /**
     * @usage Short-code function for front-end UI
     * @return string
     */
    function FrontendUI()
    {

        global $wpdb, $current_user, $wp_query;
        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));


        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return "<div class='w3eden'><div class='alert alert-danger'>" . wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))) . "</div></div>";

        $cond_uid = wpdm_multi_user("and uid='{$current_user->ID}'");
        $id = wpdm_query_var('ID');
        $task = wpdm_query_var('task');

        $tabs = array( //'sales' => array('label'=>'Sales','callback'=>'wpdm_sales_report')
        );
        $tabs = apply_filters('wpdm_frontend', $tabs);
        $burl = get_permalink();
        $sap = strpos($burl, '?') ? '&' : '?';
        ob_start();
        ?>
        <div class="w3eden">
        <ul id="tabs" class="nav nav-tabs" style="margin: 0px !important;padding: 0px;">
            <?php if (is_user_logged_in()) { ?>
                <li <?php if ($task == '' || $task == 'edit-package') { ?>class="active"<?php } ?> >
                    <a href="<?php echo $burl; ?>">All Packages</a></li>
                <li <?php if ($task == 'addnew') { ?>class="active"<?php } ?> ><a
                        href="<?php echo $burl . $sap; ?>task=addnew">Create New Package</a></li>
                <?php foreach ($tabs as $tid => $tab): ?>
                    <li <?php if ($task == $tid) { ?>class="active"<?php } ?> ><a
                            href="<?php echo $burl . $sap; ?>task=<?php echo $tid; ?>"><?php echo $tab['label']; ?></a>
                    </li>
                <?php endforeach; ?>
                <li <?php if ($task == 'editprofile') { ?>class="active"<?php } ?> ><a
                        href="<?php echo $burl . $sap; ?>task=editprofile">Edit Profile</a></li>
                <li><a href="<?php echo $burl . $sap; ?>task=logout">Logout</a></li>
            <?php } else { ?>
                <li class="active"><a href="<?php echo $burl; ?>">Signup or Signin</a></li>
            <?php } ?>
        </ul>
        <div class="tab-content" style="border: 1px solid #dddddd;border-top: 0;padding: 20px">

        <?php

        if (is_user_logged_in()) {

            if ($task == 'addnew' || $task == 'edit-package')
                include(wpdm_tpl_path('wpdm-add-new-file-front.php'));
            else if ($task == 'editprofile')
                include(wpdm_tpl_path('wpdm-edit-user-profile.php'));
            //else if ($task != '' && $tabs[$task]['callback'] != '')
            //    call_user_func($tabs[$task]['callback']);
            else if ($task != '' && isset($tabs[$task]['shortcode']) && $tabs[$task]['shortcode'] != '')
                echo do_shortcode($tabs[$task]['shortcode']);
            else
                include(wpdm_tpl_path('wpdm-list-files-front.php'));
        } else {

            include(wpdm_tpl_path('wpdm-be-member.php'));
        }
        echo '</div></div><script>jQuery(function($){ $("#tabs > li > a").click(function(){ location.href=this.href; });  });</script>';
        $data = ob_get_clean();

        return $data;
    }

    /**
     * @usage Show Edit Package Form
     * @return string
     */
    public function PackageFormFrontend($params = array())
    {
        global $wpdb, $current_user, $wp_query;
        if (is_array($params)) extract($params);

        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return WPDM_Messages::Error(wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))), -1);

        $cond_uid = wpdm_multi_user("and uid='{$current_user->ID}'");
        $id = wpdm_query_var('ID');

        ob_start();

        if (is_user_logged_in()) {
            include(wpdm_tpl_path('wpdm-add-new-file-front.php'));
        } else {
            include(wpdm_tpl_path('wpdm-be-member.php'));
        }

        $data = ob_get_clean();
        return $data;
    }

    /**
     * @usage List user packages at frontend
     * @return string
     */
    public function PackageListFrontend($sparams = array())
    {
        global $wpdb, $current_user, $wp_query;
        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return WPDM_Messages::Error(wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))), -1);

        $cond_uid = wpdm_multi_user("and uid='{$current_user->ID}'");
        $id = wpdm_query_var('ID', 'int');

        ob_start();

        if (is_user_logged_in()) {
            include(wpdm_tpl_path('wpdm-list-files-front.php'));
        } else {
            include(wpdm_tpl_path('wpdm-be-member.php'));
        }

        $data = ob_get_clean();
        return $data;
    }

    public function EditProfile()
    {
        global $wpdb, $current_user, $wp_query;
        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return WPDM_Messages::Error(wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))), -1);

        $cond_uid = wpdm_multi_user("and uid='{$current_user->ID}'");
        $id = wpdm_query_var('ID');

        ob_start();

        if (is_user_logged_in()) {
            include(wpdm_tpl_path('wpdm-edit-user-profile.php'));
        } else {
            include(wpdm_tpl_path('wpdm-be-member.php'));
        }

        $data = ob_get_clean();
        return $data;
    }

    function RegisterForm($params = array()){
        if(!get_option('users_can_register')) return __('User registration is disabled', 'wpdmpro');
        if(isset($params['role'])) update_post_meta(get_the_ID(),'__wpdm_role', $params['role']);
        ob_start();
        require_once(wpdm_tpl_path('wpdm-reg-form.php'));
        $data = ob_get_clean();
        return $data;
    }

}

new ShortCodes();