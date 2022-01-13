<?php
 //   var_dump($data);

    if($data['total_count']>50){$data['total_count']=50;}
    $tc = $data['total_count'] / 20;
    $dt = ceil($tc);
    if (!isset($_GET['paged']) || $_GET['paged'] > $dt) {
        $page = 1;
    } else {
        $page = $_GET['paged'];
    }
    ?>


        <div class="tablenav top">
            <form method="get">
                <input type="hidden" name="page" value="appchar-notification">
                <input type="hidden" name="tab" value="display-notifications">

                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo $data['total_count'].__(' item','appchar'); ?></span>
    <span class="pagination-links">
    <?php if ($page > 1) {
        $j=$page - 1;
        echo '<a class="next-page" href="admin.php?page=appchar-notification&tab=display-notifications&paged='.$j.'">
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
            echo '<a class="next-page" href="admin.php?page=appchar-notification&tab=display-notifications&paged='.$j.'">
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
            <input type="hidden" name="page" value="px-gcm">
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
                    <th scope="col" id="gcm_regid" class="manage-column column-gcm_regid column-primary" style="width: 3%;"><span></span></th>
                    <th scope="col" id="id" class="manage-column column-id"><span><?php _e('notification id','appchar') ?></span></th>
                    <th scope="col" id="os" class="manage-column column-os"><span><?php _e('content','appchar') ?></span></th>
                    <th scope="col" id="model" class="manage-column column-model" style="width: 8%;"><span><?php _e('successful','appchar') ?></span></th>
                    <th scope="col" id="game_version" class="manage-column column-game-version" style="width: 8%;"><span><?php _e('failed','appchar') ?></span></th>
                    <th scope="col" id="last-active" class="manage-column column-last-active" style="width: 8%;"><span><?php _e('converted','appchar') ?></span></th>
                    <th scope="col" id="play_time" class="manage-column column-playtime" style="width: 8%;"><span><?php _e('remaining','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at" style="width: 8%;"><span><?php _e('queued at','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at" style="width: 8%;"><span><?php _e('send after','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at" style="width: 8%;"><span><?php _e('canceled','appchar') ?></span></th>

                </tr>
                </thead>

                <tbody id="the-list" data-wp-lists="list:px_gcm_device">

                <?php

                if (isset($data['error']) || $data['total_count'] == 0) {
                    echo '<td colspan="8">'.__('Unfortunately no devices found','appchar').'</td>';
                } else {
                    $i=($page-1)*20;
                    if($i>$data['total_count']){
                        $i=0;
                    }
                    $k=$i+20;
                    if($k>$data['total_count']){
                        $k=$data['total_count'];
                    }
                    for ($i; $i < $k; $i++) {
                        $id = $data['notifications'][$i]['id'];
                        $content = $data['notifications'][$i]['contents']['en'];
                        $successful_count = $data['notifications'][$i]['successful'];
                        $failed_count = $data['notifications'][$i]['failed'];
                        $converted_count = $data['notifications'][$i]['converted'];
                        $remaining_count = $data['notifications'][$i]['remaining'];
                        $queued_at = date_i18n( get_option( 'date_format' ), $data['notifications'][$i]['queued_at']);
                        $send_after = date_i18n( get_option( 'date_format' ), $data['notifications'][$i]['send_after']);
                        $canceled = $data['notifications'][$i]['canceled'];
                        echo "<tr class=\"no-items\"><td> <input type='checkbox' name='id[]' value='$id'> </td>";
                        echo "<td><strong>$id </strong></td>";
                        echo "<td> $content</td>";
                        echo "<td>$successful_count</td>";
                        echo "<td>$failed_count</td>";
                        echo "<td>$converted_count</td>";
                        echo "<td>$remaining_count</td>";
                        echo "<td>$queued_at</td>";
                        echo "<td>$send_after</td>";
                        echo "<td>$canceled</td></tr>";

                    }

                }
                ?>

                </tbody>

                <tfoot>
                <tr>
                    <th scope="col" id="gcm_regid" class="manage-column column-gcm_regid column-primary"><span></span></th>
                    <th scope="col" id="id" class="manage-column column-id"><span><?php _e('notification id','appchar') ?></span></th>
                    <th scope="col" id="os" class="manage-column column-os"><span><?php _e('content','appchar') ?></span></th>
                    <th scope="col" id="model" class="manage-column column-model"><span><?php _e('successful','appchar') ?></span></th>
                    <th scope="col" id="game_version" class="manage-column column-game-version"><span><?php _e('failed','appchar') ?></span></th>
                    <th scope="col" id="last-active" class="manage-column column-last-active"><span><?php _e('converted','appchar') ?></span></th>
                    <th scope="col" id="play_time" class="manage-column column-playtime"><span><?php _e('remaining','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at"><span><?php _e('queued at','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at"><span><?php _e('send after','appchar') ?></span></th>
                    <th scope="col" id="created_at" class="manage-column column-create-at"><span><?php _e('canceled','appchar') ?></span></th>

                </tr>
                </tfoot>

            </table>
    </form>