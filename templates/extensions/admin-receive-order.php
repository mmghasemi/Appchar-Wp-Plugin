<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 9/9/17
 * Time: 12:40 PM
 */
function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}
$week_days = array(
    'Saturday'  => __('Saturday','appchar'),
    'Sunday'    => __('Sunday','appchar'),
    'Monday'    => __('Monday','appchar'),
    'Tuesday'   => __('Tuesday','appchar'),
    'Wednesday' => __('Wednesday','appchar'),
    'Thursday' => __('Thursday','appchar'),
    'Friday' => __('Friday','appchar'),
);

if (isset($_POST['save_extension_setting'])) {
    if(AppcharExtension::extensionIsActive('time_to_receive_order')) {
        $days_time = array();
        foreach ($week_days as $week_day=>$week_name){
            $day_time = array();
            if(isset($_POST['receive_'.$week_day.'_from'])) {
                foreach ($_POST['receive_' . $week_day . '_from'] as $key => $value) {
                    $to = $_POST['receive_' . $week_day . '_to'][$key];
                    $from = $value;
                    if ($to - $from > 0) {
                            $day_time[] = array(
                                'from' => intval($from),
                                'to' => intval($to),
                            );
//                        }
                    }
                }
            }
            $sorted = array_orderby($day_time, 'from', SORT_ASC, 'to', SORT_DESC);
            $days_time[$week_day] = $sorted;
        }
        $day_count = (isset($_POST['order_receive_day_count']) && $_POST['order_receive_day_count']<=7 && $_POST['order_receive_day_count']>1)?$_POST['order_receive_day_count']:1;
        $order_receive = array(
            'status' => (isset($_POST['order_receive_status'])) ? $_POST['order_receive_status'] : 'disable',
            'time_label' => $_POST['time_to_receive_order_label'],
            'day_label' => $_POST['day_to_receive_order_label'],
            'service_time' => $_POST['order_receive_service_time'],
            'day_time' => $days_time,
            'day_count' => $day_count,
            'hint' => null,
        );
        update_option('appchar_checkout_receive_setting', $order_receive);
    }
}

?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('custom checkout fields', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">

                            <div id="post-body-content">
                                <div>
                                    <h3 class="title">تنظیمات زمان دریافت سفارش از فروشگاه به مشتری</h3>
                                    <div class="inside">
                                        <div id="settings">
                                            <table class="form-table">
                                                <?php
                                                if (AppcharExtension::extensionIsActive('time_to_receive_order')):
                                                    $receive_setting = get_option('appchar_checkout_receive_setting');
                                                    ?>

                                                    <tr>
                                                        <th scope="row">فعالسازی دریافت سفارش توسط فروشگاه از مشتری</th>
                                                        <td><input type="checkbox" id="abd" name="order_receive_status"
                                                                   value="enable" <?php if (isset($receive_setting['status']) && $receive_setting['status'] == 'enable') {
                                                                echo 'checked';
                                                            } ?>></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">برچسب فیلد روز در اپ</th>
                                                        <td>عنوان: <input name="time_to_receive_order_label"
                                                                          placeholder="عنوان"
                                                                          value="<?php if (isset($receive_setting['time_label'])) {
                                                                              echo $receive_setting['time_label'];
                                                                          } ?>"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">برچسب فیلد زمان در اپ</th>
                                                        <td>عنوان: <input name="day_to_receive_order_label"
                                                                          placeholder="عنوان"
                                                                          value="<?php if (isset($receive_setting['day_label'])) {
                                                                              echo $receive_setting['day_label'];
                                                                          } ?>"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">زمان پردازش سفارش</th>
                                                        <td><input type="number" min="1" name="order_receive_service_time"
                                                                          placeholder="1"
                                                                          value="<?php if (isset($receive_setting['service_time'])) {
                                                                              echo $receive_setting['service_time'];
                                                                          } ?>"></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">تعداد روزهای قابل نمایش به کاربر</th>
                                                        <td>
                                                            <input type="number" min="1" name="order_receive_day_count"
                                                                   placeholder="1"  max="7"
                                                                   value="<?php if (isset($receive_setting['day_count'])) {
                                                                       echo $receive_setting['day_count'];
                                                                   } ?>">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row">تعیین روز و زمان ارجاع</th>
                                                        <td class="week-td">
                                                            <div>
                                                                <?php
                                                                foreach ($week_days as $day_key=>$week_day):
                                                                ?>
                                                                    <div class="day-time">
                                                                        <div>
                                                                            <div class="day"><?php echo $week_day; ?></div>
                                                                        </div>
                                                                        <div>
                                                                            <div class="time-block">
                                                                                <div class="time">
                                                                                    <?php
                                                                                    if(isset($receive_setting['day_time'][$day_key])) {

                                                                                        foreach ($receive_setting['day_time'][$day_key] as $appchar_dt) {
                                                                                            echo '<div><select name="receive_' . $day_key . '_from[]" class="select-time">';
                                                                                            for ($i = 1; $i <= 24; $i++) {
                                                                                                if ($appchar_dt['from'] == $i) {
                                                                                                    echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                                                                                } else {
                                                                                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                                                                                }
                                                                                            }
                                                                                            echo '</select> تا ';
                                                                                            echo '<select name="receive_' . $day_key . '_to[]" class="select-time">';
                                                                                            for ($i = 1; $i <= 24; $i++) {
                                                                                                if ($appchar_dt['to'] == $i) {
                                                                                                    echo '<option value="' . $i . '" selected>' . $i . '</option>';
                                                                                                } else {
                                                                                                    echo '<option value="' . $i . '">' . $i . '</option>';
                                                                                                }
                                                                                            }
                                                                                            echo '</select>' .
                                                                                                '<div class="edit-block"><a onclick="remove_select_time_block(this)"><span class="dashicons dashicons-trash"></span></a></div></div>';
                                                                                        }
                                                                                    }
                                                                                    
                                                                                    ?>
                                                                                    
                                                                                </div>
                                                                                <a onclick="add_select_time_block('<?php echo 'receive_'.$day_key; ?>',this)"><span class="dashicons dashicons-plus-alt"></span></a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                endforeach;
                                                                ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <?php submit_button('', '', 'save_extension_setting'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>