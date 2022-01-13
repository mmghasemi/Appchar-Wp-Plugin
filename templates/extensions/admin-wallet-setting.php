<?php
if(isset($_POST['save_wallet_setting'])){
    $reward_per_order = array();
    if(isset($_POST['enable_rewards_per_order'])){
        $reward_per_order['status']='enable';
    }else{
        $reward_per_order['status']='disable';
    }
    $reward_per_order['amount_type']= $_POST['amount_type'];
    if($reward_per_order['amount_type']=='percent'){
        if($_POST['amount']>100){
            echo '<script>alert("'.__('Unfortunately, you can not enter upper than 100','appchar').'");
                                   window.location.reload();</script>';
            $_POST['amount'] = 0;
        }
    }
    if(!filter_var($_POST['amount'], FILTER_VALIDATE_INT) !== false){
        echo '<script>alert("'.__('Unfortunately, please enter valid amount','appchar').'");
              window.location.reload();</script>';
    }
    $reward_per_order['amount']= $_POST['amount'];
    if(is_numeric($_POST['minimum_amount_order'])){
        $reward_per_order['minimum_amount_order']= $_POST['minimum_amount_order'];
    }else{
        $reward_per_order['minimum_amount_order']= 0;
    }
    update_option('appchar_rewards_per_order',$reward_per_order);

    if(AppcharExtension::extensionIsActive('introduce_to_friends')){
        $itf_setting = array(
            'status'                    =>  (isset($_POST['enable_introduce_to_friends']))?'enable':'disable',
            'sidebar_title'             =>  (isset($_POST['sidebar_title']))?trim($_POST['sidebar_title']):'',
            'shared_text'               =>  (isset($_POST['shared_text']))?trim($_POST['shared_text']):'',
            'share_page_text'           =>  (isset($_POST['share_page_text']))?trim($_POST['share_page_text']):'',
            'minimum_order_amount'      =>  (isset($_POST['minimum_order_amount']))?trim($_POST['minimum_order_amount']):0,
            'referrer_point'            =>  (isset($_POST['referrer_point']))?trim($_POST['referrer_point']):'',
            'referrer_amount_type'      =>  (isset($_POST['referrer_amount_type']))?trim($_POST['referrer_amount_type']):'constant',
            'referrer_count'            =>  (isset($_POST['referrer_count']))?trim($_POST['referrer_count']):'once',
            'referred_user_point'       =>  (isset($_POST['referred_user_point']))?trim($_POST['referred_user_point']):'',
            'referred_user_amount_type' =>  (isset($_POST['referred_user_amount_type']))?trim($_POST['referred_user_amount_type']):'constant',
            'referred_user_count'       =>  (isset($_POST['referred_user_count']))?trim($_POST['referred_user_count']):'once',
        );
        update_option('introduce_to_friends_setting',$itf_setting);
    }
}

?>
<style>
    .custom_col{
        display: inline-block;
        vertical-align: middle;
        margin-left: 30px;
    }
</style>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('Wallet Setting', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">
                            <table class="form-table">
                                <tbody>
                                    <?php
                                    $reward_per_order=get_option('appchar_rewards_per_order',array());
                                    ?>
                                    <tr>
                                        <th>
                                            <?php _e('Enable Rewards per order', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="enable_rewards_per_order" <?php echo (isset($reward_per_order['status']) && $reward_per_order["status"]=='enable')?'checked':''; ?>><?php __('Enable Rewards per order','appchar') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('Enable Rewards per order', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="enable_rewards_per_order" <?php echo (isset($reward_per_order['status']) && $reward_per_order["status"]=='enable')?'checked':''; ?>><?php __('Enable Rewards per order','appchar') ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('Amount Type', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <select name="amount_type">
                                                <option value="percent" <?php echo (isset($reward_per_order['amount_type']) && $reward_per_order['amount_type']=='percent')?'selected':''; ?>><?php _e('Percent','appchar') ?></option>
                                                <option value="constant" <?php echo (isset($reward_per_order['amount_type']) && $reward_per_order['amount_type']=='constant')?'selected':''; ?>><?php _e('Constant','appchar') ?></option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('amount', 'appchar'); ?>
                                        </th>
                                        <td>
                                           
                                            <input type="text" name="amount" value="<?php echo (isset($reward_per_order['amount']))?$reward_per_order['amount']:''; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('The minimum amount of order', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="number" name="minimum_amount_order" value="<?php echo ($reward_per_order['minimum_amount_order']!='')?$reward_per_order['minimum_amount_order']:0; ?>"><?php __('Enable Rewards per order','appchar') ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td colspan="2"><hr></td>
                                    </tr>
                                <?php
                                if(AppcharExtension::extensionIsActive('introduce_to_friends')):
                                $introduce_to_friends = get_option('introduce_to_friends_setting',array());
                                ?>
                                    <tr>
                                        <th>
                                            <?php _e('Enable introduce to friends extensions', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="checkbox" name="enable_introduce_to_friends" <?php echo (isset($introduce_to_friends['status']) && $introduce_to_friends["status"]=='enable')?'checked':''; ?>>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('title on sidebar menu', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="text" name="sidebar_title" value="<?php echo (isset($introduce_to_friends['sidebar_title']))?$introduce_to_friends['sidebar_title']:'معرفی به دوستان'; ?>">

                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('text to share with friends', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <?php $default_shared_text = "سلام من از این اپلیکیشن خرید میکنم\n خیلی اپلیکیشن خوبیه\n اگر دوست داری تو هم با لینک زیر دانلودش کن و زمان عضویت کد معرف [refere_code] رو وارد کن تا هم تو اعتبار هدیه بگیری هم من\n این لینکشه:\n آدرس اپلیکیشن رو وارد کنید" ?>
                                            <textarea cols="55" rows="5" name="shared_text"><?php echo (isset($introduce_to_friends['shared_text']))?$introduce_to_friends['shared_text']:$default_shared_text; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('text to show in share page', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <?php $default_share_page_text = "شما میتوانید با به اشتراک گذاری کد معرف خود و ثبت نام دوستان خود از طریق کد معرفی شما از ما اعتبار دریافت کنید\n در ضمن در این حالت [referred_user_point] اعتبار هم به دوست شما تعلق خواهد گرفت" ?>
                                            <textarea cols="55" rows="5" name="share_page_text"><?php echo (isset($introduce_to_friends['share_page_text']))?$introduce_to_friends['share_page_text']:$default_share_page_text; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('Minimum order amount', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <input type="text" name="minimum_order_amount" value="<?php echo (isset($introduce_to_friends['minimum_order_amount']))?$introduce_to_friends['minimum_order_amount']:'0'; ?>">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('Point of credit added to the referrer ', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <div class="row">
                                                <div class="custom_col">
                                                    <input type="text" name="referrer_point" value="<?php echo (isset($introduce_to_friends['referrer_point']))?$introduce_to_friends['referrer_point']:''; ?>">
                                                </div>
                                                <div class="custom_col">
                                                    <select name="referrer_amount_type">
                                                        <option value="constant" <?php echo (isset($introduce_to_friends['referrer_amount_type']) && $introduce_to_friends['referrer_amount_type']=='constant')?'selected':''; ?>><?php _e('Constant','appchar') ?></option>
                                                        <option value="percent" <?php echo (isset($introduce_to_friends['referrer_amount_type']) && $introduce_to_friends['referrer_amount_type']=='percent')?'selected':''; ?>><?php _e('Percent','appchar') ?></option>
                                                    </select>
                                                </div>
                                                <div class="custom_col">
                                                    <input type="radio" name="referrer_count" value="always" <?php echo (isset($introduce_to_friends['referrer_count']) && $introduce_to_friends["referrer_count"]=='always')?'checked':''; ?>><label><?php _e('Always','appchar') ?></label><br>
                                                    <input type="radio" name="referrer_count" value="once" <?php echo (isset($introduce_to_friends['referrer_count']) && $introduce_to_friends["referrer_count"]=='once')?'checked':''; ?>><label><?php _e('Only once','appchar') ?></label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('Point of credit added to the referred user', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <div class="custom_col">
                                                <input type="text" name="referred_user_point" value="<?php echo (isset($introduce_to_friends['referred_user_point']))?$introduce_to_friends['referred_user_point']:''; ?>">
                                            </div>
                                            <div class="custom_col">
                                                <select name="referred_user_amount_type">
                                                    <option value="constant" <?php echo (isset($introduce_to_friends['referred_user_amount_type']) && $introduce_to_friends['referred_user_amount_type']=='constant')?'selected':''; ?>><?php _e('Constant','appchar') ?></option>
                                                    <option value="percent" <?php echo (isset($introduce_to_friends['referred_user_amount_type']) && $introduce_to_friends['referred_user_amount_type']=='percent')?'selected':''; ?>><?php _e('Percent','appchar') ?></option>
                                                </select>
                                            </div>
                                            <div class="custom_col">
                                                <input type="radio" name="referred_user_count" value="always" <?php echo (isset($introduce_to_friends['referred_user_count']) && $introduce_to_friends["referred_user_count"]=='always')?'checked':''; ?>><label><?php _e('Always','appchar') ?></label><br>
                                                <input type="radio" name="referred_user_count" value="once" <?php echo (isset($introduce_to_friends['referred_user_count']) && $introduce_to_friends["referred_user_count"]=='once')?'checked':''; ?>><label><?php _e('Only once','appchar') ?></label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php
                                endif;
                                ?>
                                </tbody>
                            </table>
                            <?php submit_button('','','save_wallet_setting'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
