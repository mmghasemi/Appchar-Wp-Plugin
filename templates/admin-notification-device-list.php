<?php

    $total_count = $data_single['total_count'];
    // if($data['total_count']>300){$data['total_count']=300;}
    $tc = $data_single['total_count'] / 20;
    $dt = ceil($tc);
    $data = array();
    if (!isset($_GET['paged']) || $_GET['paged'] > $dt) {
        $page = 1;
        $offset = 0;
        $total_players = $this->get_data_for_pagination(20, $offset);
        $data = json_decode($total_players, true);
    } else {
        $page = $_GET['paged'];
        $offset = ($page - 1)*20;
        $total_players = $this->get_data_for_pagination(20, $offset);
        $data = json_decode($total_players, true);
    }
?>
    <div class="tablenav top">
    <form method="get">
    <input type="hidden" name="page" value="appchar-notification">
    <input type="hidden" name="tab" value="display-devices">
    <div class="tablenav-pages">
    <span class="displaying-num"><?php echo $total_count.__(' item','appchar'); ?></span>
    <span class="pagination-links">
    <?php if ($page > 1) {
        $j=$page - 1;
        echo '<a class="next-page" href="admin.php?page=appchar-notification&tab=display-devices&paged='.$j.'">
        <span class="screen-reader-text" aria-hidden="true">'.__('previous page','appchar').'</span><span aria-hidden="true">‹</span></a>';
    }else{
        echo '<span class="tablenav-pages-navspan" aria-hidden="true">‹</span>';
    }?>
    <span class="paging-input">
        <label for="current-page-selector" class="screen-reader-text"><?php _e('current page','appchar'); ?></label>
        <input class="current-page" id="current-page-selector" type="text" name="paged" value="<?php echo $page; ?>"
               size="1"
               aria-describedby="table-paging"><?php _e(' of ','appchar'); ?>
        <span class="total-pages"><?php echo $dt; ?></span>
    </span>
    <?php if ($page < $dt) {
        $j=$page + 1;
        echo '<a class="next-page" href="admin.php?page=appchar-notification&tab=display-devices&paged='.$j.'">
        <span class="screen-reader-text" aria-hidden="true">'.__('next page','appchar').'</span><span aria-hidden="true">›</span></a>';
    }else{
        echo '<span class="tablenav-pages-navspan" aria-hidden="true">›</span>';
    }?>
        </span>

        </div>
        </form>
        <br class="clear">
        </div>
        <form method="get" action="admin.php">
            <input type="hidden" name="page" value="appchar-notification">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text"><?php _e('Bulk Action','appchar'); ?></label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="send_not"><?php _e('Send Notification','appchar'); ?></option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="<?php _e('apply','appchar') ?>">
                </div>
            </div>
            <table class="wp-list-table widefat fixed striped px_gcm_devices">
                <thead>
                <tr>
                    <th scope="col" id="gcm_regid" class="manage-column column-gcm_regid column-primary"><span></span></th>
                    <th scope="col" id="id" class="manage-column column-id"><span><?php _e('device id','appchar') ?></span></th>
                    <th scope="col" id="id" class="manage-column column-username"><span><?php _e('username','appchar') ?></span></th>
                    <th scope="col" id="os" class="manage-column column-os"><span><?php _e('device os','appchar') ?></span></th>
                    <th scope="col" id="model" class="manage-column column-model"><span><?php _e('device model','appchar') ?></span></th>
                    <th scope="col" id="game_version" class="manage-column column-game-version"><span><?php _e('app version','appchar') ?></span></th>
                    <th scope="col" id="last-active" class="manage-column column-last-active"><span><?php _e('last active','appchar') ?></span></th>
                    <th scope="col" id="play_time" class="manage-column column-playtime"><span><?php _e('play time','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at"><span><?php _e('create at','appchar') ?></span></th>

                </tr>
                </thead>

                <tbody id="the-list" data-wp-lists="list:px_gcm_device">

                <?php

                if (isset($data['error']) || $data['total_count'] == 0) {
                    echo '<td colspan="8">'.__('Unfortunately no devices found','appchar').'</td>';
                } else {
                    // $i=$offset;
                    // if($i>$data['total_count']){
                    //     $i=0;
                    // }
                    // $k=$i+20;
                    if($k>$data['total_count']){
                        $k=$data['total_count'];
                    }
                    for ($i = 0; $i < 20; $i++) {
                        $id = $data['players'][$i]['id'];
                        $args = array(
                            'meta_value'   => $id,
                        );
                        //var_dump(get_users( $args ));
                        // user_app_id field in user meta
                        global $wpdb;
                        $device_id = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}appchar_user_devices` WHERE `user_device_id`='$id'" );

                        foreach ($device_id as $key=>$values){
                            $user_id=$values->user_id;
                        }
                        $user = (isset($user_id))?get_user_by( 'id', $user_id ):false;
                        $user_name = ($user)?$user->user_login:'';
                        $os = $data['players'][$i]['device_os'];
                        $model = $data['players'][$i]['device_model'];
                        $appversion = $data['players'][$i]['game_version'];
                        $last_active = date_i18n( get_option( 'date_format' ), $data['players'][$i]['last_active']);
                        $play_time = $data['players'][$i]['playtime'];
                        $create = date_i18n( get_option( 'date_format' ), $data['players'][$i]['created_at'],true);
                        echo "<tr class=\"no-items\"><td> <input type='checkbox' name='id[]' value='$id'> </td>";
                        echo "<td><strong>$id </strong>" . '
                        <div class="locked-info">
                            <span class="locked-avatar"></span>
                            <span class="locked-text"></span>
                        </div>
                        <div class="row-actions">
                            <span class="edit"><a href="admin.php?page=appchar-notification&tab=manual&id=' . $id . '" title="">'. __('send notification','appchar') .'</a> </span>
                        </div>
                        <button type="button" class="toggle-row"><span class="screen-reader-text">'. __('show details','appchar') .'</span></button></td>';
                        echo "<td> $user_name</td>";
                        echo "<td> $os</td>";
                        echo "<td>$model</td>";
                        echo "<td>$appversion</td>";
                        echo "<td>$last_active</td>";
                        echo "<td>$play_time</td>";
                        echo "<td>$create</td></tr>";
                        $user_id = "";
                    }

                }
                ?>

                </tbody>

                <tfoot>
                <tr>
                    <th scope="col" id="gcm_regid" class="manage-column column-gcm_regid column-primary"><span></span></th>
                    <th scope="col" id="id" class="manage-column column-id"><span><?php _e('device id','appchar') ?></span></th>
                    <th scope="col" id="id" class="manage-column column-username"><span><?php _e('username','appchar') ?></span></th>
                    <th scope="col" id="os" class="manage-column column-os"><span><?php _e('device os','appchar') ?></span></th>
                    <th scope="col" id="model" class="manage-column column-model"><span><?php _e('device model','appchar') ?></span></th>
                    <th scope="col" id="game_version" class="manage-column column-game-version"><span><?php _e('app version','appchar') ?></span></th>
                    <th scope="col" id="last-active" class="manage-column column-last-active"><span><?php _e('last active','appchar') ?></span></th>
                    <th scope="col" id="play_time" class="manage-column column-playtime"><span><?php _e('play time','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at"><span><?php _e('create at','appchar') ?></span></th>

                </tr>
                </tfoot>

            </table>
        </form>
