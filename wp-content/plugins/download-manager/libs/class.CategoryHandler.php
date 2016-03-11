<?php


class WPDM_CategoryHandler {

    function __construct(){
        add_action( 'wpdmcategory_add_form_fields', array($this,'MetaFields'), 10, 2 );
        add_action( 'wpdmcategory_edit_form_fields', array($this,'MetaFieldsEdit'), 10, 2 );

        add_action( 'edited_wpdmcategory', array($this,'SaveMetaData'), 10, 2 );
        add_action( 'create_wpdmcategory', array($this,'SaveMetaData'), 10, 2 );

        add_action( 'admin_init', array($this,'AdminInit') );



    }

    function AdminInit(){
        add_filter("manage_edit-wpdmcategory_columns", array($this,'CategoryIDColumnHead'));
        add_filter("manage_wpdmcategory_custom_column", array($this,'CategoryIDColumnData'), 10, 3);
    }

    function CategoryIDColumnHead($columns) {
        $columns['tag_ID'] = 'ID<style>#tag_ID, .tag_ID{ width: 70px !important; }</style>';
        return $columns;
    }

    function CategoryIDColumnData($c, $column_name, $term_id) {

        if ($column_name == 'tag_ID') {
            echo $term_id;
        }
    }

    function MetaFields() {
        ?>
        <div class="form-field">
            <label><?php _e( 'Access:', 'wpdmcategory' ); ?></label>
            <p class="description"><?php _e( 'Select the roles who should have access to the packages under this category','wpdmpro' ); ?></p>


                <label><input name="__wpdmcategory[access][]" type="checkbox" value="guest" checked="checked"><?php echo __("All Visitors","wpdmpro"); ?></label>
                <?php
                global $wp_roles;
                $roles = array_reverse($wp_roles->role_names);
                foreach( $roles as $role => $name ) {





                    ?>
                <label><input name="__wpdmcategory[access][]" type="checkbox" value="<?php echo $role; ?>"  > <?php echo $name; ?></label>
                <?php } ?>


        </div>
    <?php
    }
    function MetaFieldsEdit() {
        $MetaData = get_option( "__wpdmcategory" );
        $MetaData = maybe_unserialize($MetaData);
        ?>
        <tr class="form-field">
            <th><label><?php _e( 'Access:', 'wpdmcategory' ); ?></label>
            </th>
            <td>
                <p class="description"><?php _e( 'Select the roles who should have access to the packages under this category','wpdmpro' ); ?></p>
                <ul>
                 <?php

                $currentAccess = isset($MetaData[$_GET['tag_ID']])?$MetaData[$_GET['tag_ID']]['access']:array();

                $selz = '';
                if(  $currentAccess ) $selz = (in_array('guest',$currentAccess))?'checked=checked':'';
                ?>

                <li><label><input name="__wpdmcategory[access][]" type="checkbox" value="guest" <?php echo $selz  ?>><?php echo __("All Visitors","wpdmpro"); ?></label></li>
                <?php
                global $wp_roles;
                $roles = array_reverse($wp_roles->role_names);
                foreach( $roles as $role => $name ) {



                    if(  $currentAccess ) $sel = (in_array($role,$currentAccess))?'checked=checked':'';
                    else $sel = '';



                    ?>
                    <li><label><input name="__wpdmcategory[access][]" type="checkbox" value="<?php echo $role; ?>" <?php echo $sel  ?>> <?php echo $name; ?></label></li>
                <?php } ?>
                </ul>
            </td>
        </tr>
    <?php
    }

    function SaveMetaData( $term_id ) {
        if ( isset( $_POST['__wpdmcategory'] ) ) {
            $MetaData = get_option( "__wpdmcategory" );
            $MetaData = maybe_unserialize($MetaData);
            $MetaData[$term_id] = $_POST['__wpdmcategory'];
            update_option( "__wpdmcategory", $MetaData );
        }
    }

    public static function  GetAllowedRoles( $term_id ){
        $MetaData = get_option( "__wpdmcategory" );
        $MetaData = maybe_unserialize($MetaData);
        return isset($MetaData[$term_id])?$MetaData[$term_id]['access']:array();
    }

}

new WPDM_CategoryHandler();