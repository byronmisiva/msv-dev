<?php


class ApplySettings {

    function __construct(){

        add_filter('wpdm_custom_data', array( $this, 'SR_CheckPackageAccess' ), 10, 2);

    }

    function SR_CheckPackageAccess($data, $id){
        global $current_user;
        $skiplocks = maybe_unserialize(get_option('__wpdm_skip_locks', array()));
        if( is_user_logged_in() ){
            foreach($skiplocks as $lock){
                unset($data[$lock."_lock"]); // = 0;
            }
        }

        return $data;
    }


}

new ApplySettings();