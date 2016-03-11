<?php
global $btnclass;
$p = serialize($params);
$tid = md5($p);
if(!isset($params['items_per_page'])) $params['items_per_page'] = 20;
if(isset($params['jstable']) && $params['jstable']==1): ?>
    <script src="<?php echo WPDM_BASE_URL.'js/jquery.dataTables.min.js' ?>"></script>
    <link href="<?php echo WPDM_BASE_URL.'css/jquery.dataTables.min.css' ?>" rel="stylesheet" />
    <style>
        #wpdmmydls-<?php echo $tid; ?>{
            border: 1px solid #dddddd !important;
            border-radius: 4px !important;
            font-size: 10pt;
            overflow: hidden;
        }
        #wpdmmydls-<?php echo $tid; ?> th{
            background-color: #eeeeee;
            border-bottom: 1px solid #bbb;
        }
    #wpdmmydls-<?php echo $tid; ?>_filter input[type=search],
    #wpdmmydls-<?php echo $tid; ?>_length select{
        padding: 5px !important;
        border-radius: 3px !important;
        border: 1px solid #dddddd !important;
    }
        .dataTables_wrapper .dataTables_paginate .paginate_button{
            padding: 0.2em 0.8em !important;
            border-radius: 3px !important;
        }

        #wpdmmydls-<?php echo $tid; ?> .package-title{
            color:#36597C;
            font-size: 11pt;
            font-weight: 400;
        }
        #wpdmmydls-<?php echo $tid; ?> small{
            font-size: 9pt;
        }

        #wpdmmydls-<?php echo $tid; ?> .fa{
            color: #888;
        }
    </style>
    <script>
        jQuery(function($){
            $('#wpdmmydls-<?php echo $tid; ?>').dataTable({
                "oLanguage": {
                    "sEmptyTable": "<?php echo isset($params['no_data_msg']) && $params['no_data_msg']!=''?$params['no_data_msg']:__('No Packages Found','wpdmpro'); ?>"
                },
                "iDisplayLength": <?php echo $params['items_per_page'] ?>,
                "aLengthMenu": [[<?php echo $params['items_per_page']; ?>, 10, 25, 50, -1], [<?php echo $params['items_per_page']; ?>, 10, 25, 50, "All"]]
            });
        });
    </script>
<?php endif; ?>

<div class="w3eden">
    <div class="container-fluid" id="wpdm-all-packages">
        <table id="wpdmmydls-<?php echo $tid; ?>" class="table table-striped wpdm-all-packages-table">
            <thead>
            <tr>
                <th class=""><?php _e('Title','wpdmpro'); ?></th>
                <th class="hidden-sm hidden-xs"><?php _e('Categories','wpdmpro'); ?></th>
                <th class="hidden-xs"><?php _e('Create Date','wpdmpro'); ?></th>
                <th style="width: 100px;"><?php _e('Download','wpdmpro'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php


            $cfurl = get_permalink();

            if(strpos($cfurl, "?")) $cfurl.="&wpdmc="; else $cfurl.="?wpdmc=";
            $params = array("post_type"=>"wpdmpro","posts_per_page"=>$items,"offset"=>$offset);
            if(isset($tax_query)) $params['tax_query'] = $tax_query;
            $q = new WP_Query($params);
            $total_files = $q->found_posts;
            while ($q->have_posts()): $q->the_post();

                $ext = "_blank";
                $data = wpdm_custom_data(get_the_ID());
                if(isset($data['files'])&&count($data['files'])){
                $tmpvar = explode(".",$data['files'][0]);
                $ext = count($tmpvar) > 1 ? end($tmpvar) : $ext;
                } else $data['files'] = array();

                $ext = isset($data['icon'])?$data['icon']:$ext.".png";

                $cats = wp_get_post_terms(get_the_ID(), 'wpdmcategory');
                $fcats = array();

                foreach($cats as $cat){
                    $fcats[] = "<a class='sbyc' href='{$cfurl}{$cat->slug}'>{$cat->name}</a>";
                }
                $cats = @implode(", ", $fcats);
                $data['ID'] = $data['id'] = get_the_ID();
                $data['title'] = get_the_title();
                if($ext=='') $ext = '_blank.png';
                if($ext==basename($ext)) $ext = plugins_url("download-manager/file-type-icons/".$ext);
                ?>

                <tr>
                    <td style="background-image: url('<?php echo $ext ; ?>');background-size: 32px;background-position: 5px 8px;background-repeat:  no-repeat;padding-left: 43px;line-height: normal;">
                        <a class="package-title" href='<?php echo the_permalink(); ?>'><?php the_title(); ?></a><br/>
                        <small><i class="fa fa-folder"></i> &nbsp; <?php echo count($data['files']); ?> files &nbsp;&nbsp;
                            <i class="fa fa-download"></i> &nbsp; <?php echo isset($data['download_count'])?$data['download_count']:0; ?>
                            download<?php echo isset($data['download_count']) && $data['download_count'] > 1 ? 's' : ''; ?><br/>
                        <span class="hidden-md hidden-lg"><?php echo $cats; ?></br></span>
                        <span class="hidden-md hidden-lg hidden-sm"><?php echo get_the_date(); ?></span>
                        </small>
                    </td>
                    <td class="hidden-sm hidden-xs"><?php echo $cats; ?></td>
                    <td class="hidden-xs"><?php echo get_the_date(); ?></td>
                    <td><?php echo DownloadLink($data, $style = 'simple-dl-link'); ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if((!isset($params['jstable']) || $params['jstable']==0) && $total_files==0): ?>
                <tr>
                    <td colspan="4" class="text-center">

                        <?php echo isset($params['no_data_msg']) && $params['no_data_msg']!=''?$params['no_data_msg']:__('No Packages Found','wpdmpro'); ?>

                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <?php
        global $wp_rewrite,$wp_query;

        $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

        $pagination = array(
            'base' => @add_query_arg('paged','%#%'),
            'format' => '',
            'total' => ceil($total_files/$items),
            'current' => $cp,
            'show_all' => false,
            'type' => 'list',
            'prev_next'    => True,
            'prev_text' => '<i class="icon icon-angle-left"></i> Previous',
            'next_text' => 'Next <i class="icon icon-angle-right"></i>',
        );

        if( $wp_rewrite->using_permalinks() )
            $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg('s',get_pagenum_link(1) ) ) . 'page/%#%/', 'paged');

        if( !empty($wp_query->query_vars['s']) )
            $pagination['add_args'] = array('s'=>get_query_var('s'));

        echo  "<div class='text-center'>".str_replace("<ul class='page-numbers'>","<ul class='page-numbers pagination pagination-centered'>",paginate_links($pagination))."</div>";

        wp_reset_query();
        ?>

    </div>
</div>
