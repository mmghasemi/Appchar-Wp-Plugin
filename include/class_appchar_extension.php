<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
class AppcharExtension{
    private $site_url;
    function __construct(){
        $url=get_site_url();
        $find = array( 'http://', 'https://' );
        $replace = '';
        $this->site_url = str_replace( $find, $replace, $url );
    }

    /*
     * list of extension
     */
    public static function extensionName(){
        return array(
            array(
                'name'=>'user-waller-credit-system',
                'title'         =>__('user wallet credit system','appchar'),
                'desc'          =>'<p style="text-align: justify">شما می توانید به وسیله افزونه کیف پول یک کیف پول حرفه ای به فروشگاه و اپ خود اضافه کنید</p><p style="text-align: justify">این افزونه قابلیت امتیازدهی به ازای خرید محصول را نیز دارا می باشد</p>',
                'plugincode'    => 'wallet',
                'is_plugin'     => true,
                'has_setting'  => true,
            ),
            array(
                'name' => 'barcode-scanner',
                'title'=> __('barcode scanner','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از قابلیت بارکدخوان مشتریان شما میتوانند با اسکن کردن بارکد محصولات مستقیما به صفحه محصول مورد نظر شما وارد شود</p><p style="text-align: justify">برای استفاده از این قابلیت باید در قسمت SKU شناسه محصول مورد نظر را وارد نمایید</p>',
                'plugincode' => 'barcodescanner',
                'is_plugin'     => false,
                'has_setting'  => false,
            ),
            array(
                'name' => 'schedule',
                'title'=> __('schedule','appchar'),
                'desc' => '<p style="text-align: justify">از طریق افزونه زمانبندی میتوانید برای فروشگاه خود زمانبندی باز و یا بسته بودن فروشگاه تعیین نمایید. در این حالت در زمانهایی که فروشگاه بسته است امکان ثبت سفارش وجود ندارد</p>',
                'plugincode' => 'schedule',
                'is_plugin'     => false,
                'has_setting'   => true,
            ),
            array(
                'name' => 'flexible-checkout-fields',
                'title'=> __('Flexible Checkout Fields','appchar'),
                'desc' => '<p style="text-align: justify">این افزونه جهت ویرایش فیلدهای پیشفرض ووکامرس  استفاده می شود. شما میتوانید هر کدام از فیلدها را به سلیقه خود حذف و یا عنوان ونوع فیلدها را عوض نمایید</p>',
                'plugincode' => 'edit_checkout_fields',
                'is_plugin'     => false,
                'has_setting'   => true,
            ),
            array(
                'name' => 'custom-register-fields',
                'title'=> __('Custom Register Fields','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این افزونه می توانید فیلدهای ثبتنام را به میل خود ویرایش کنید (در این ویژگی شما میتوانید عنوان فیلدها و نوع فیلدها را تغییر دهید)</p>',
                'plugincode' => 'custom_register_fields',
                'is_plugin'     => false,
                'has_setting'   => true,
            ),
            array(
                'name' => 'user-approve',
                'title'=> __('User Approve','appchar'),
                'desc' => '<p style="text-align: justify">افزونه ای که با ان میتوانید برای اضافه شدن کاربران به لیستتاییدیه قرار دهید.</p>',
                'plugincode' => 'user_approve',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'custom-tab',
                'title'=> __('Custom Tab','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این ویژگی می توانید تبهای سفارشی برای هر محصول اضافه کنید و توضیحات بیشتری در مورد محصولات فروشگاه خود به کاربر ارایه نمایید.</p>',
                'plugincode' => 'custom_tab',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'address-seller',
                'title'=> __('Address Seller','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این ویژگی در صورتی که محصولات فروشگاه شما توسط فروشندگان دیگر به فروش می رسد ادرس و محل آنها را در اپ نمایش دهید</p>',
                'plugincode' => 'address_seller',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'notification-log',
                'title'=> __('display notification','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این ویژگی شما میتوانید لیست نوتیفیکیشنهایی که به هر کاربر ارسال می کنید را به او نمایش دهید تا کاربر هیچ یک از پیامهای شما را از دست ندهد</p>',
                'plugincode' => 'notification_log',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'multi-language',
                'title'=> __('multi language','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این ویژگی شما می توانید اپلیکیشن خود را بین المللی کرده و برای استفاده کاربران با زبان های مختلف شخصی سازی کنید</p>',
                'plugincode' => 'multi_language',
                'is_plugin'     => false,
                'has_setting'   => false,

            ),
            array(
                'name' => 'special-offer',
                'title'=> __('special offer','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این قابلیت می توانید قسمت جدید به صفحه خانه خود اضافه نمایید و مشتریانتان را از پیشنهادهای ویژه خود باخبر سازید.</p>',
                'plugincode' => 'special_offer',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'product-expiration-date',
                'title'=> __('product expiration date','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این افزونه فقط محصولاتی که دارای تاریخ و قیمت ویژه هستند از طریق کاربر ثبت می شوند و بقیه محصولات برچسب منقضی شده می خورد.</p>',
                'plugincode' => 'product_expiration_date',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'free-download',
                'title'=> __('free download','appchar'),
                'desc' => '<p style="text-align: justify">فعال کردن لینک دانلود به جای خرید در محصولات دانلودی رایگان</p>',
                'plugincode' => 'free_download',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'get-shipping-on-map',
                'title'=> __('show map on order page','appchar'),
                'desc' => '<p style="text-align: justify">فعال کردن نقشه گوگل برای انتخاب مکان ارسال محصول توسط کاربر</p>',
                'plugincode' => 'get_shipping_on_map',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'google-analytics',
                'title'=> __('google analytics','appchar'),
                'desc' => '<p style="text-align: justify">از طریق این افزونه میتوانید از طریق گوگل انالیتیکس انالیز دقیقی از کاربران اپلیکشن خود داشته باشید.</p>',
                'plugincode' => 'google_analytics',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'easy-shopping-cart',
                'title'=> __('easy shopping cart','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این قابلیت ، مشتریان شما ساده و سریعتر به محصولات شما دسترسی پیدا کرده و اضافه کردن محصولات به سبد برای انها ساده تر خواهد شد. </p>',
                'plugincode' => 'easy_shopping_cart',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'catalog-mode',
                'title'=> __('catalog mode','appchar'),
                'desc' => '<p style="text-align: justify">این افزونه به شما این قابلیت را می دهد که قابلیت سفارش محصول و نمایش قیمت را در اپلیکیشن خود غیرفعال کنید</p>',
                'plugincode' => 'catalog_mode',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'force-login',
                'title'=> __('force login','appchar'),
                'desc' => '<p style="text-align: justify">در صورت فعال بودن این افزونه کاربران برای استفاده از اپ ملزم به ورود میشوند</p>',
                'plugincode' => 'force_login',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'video-in-product',
                'title'=> __('video in product','appchar'),
                'desc' => '<p style="text-align: justify">اضافه کردن ویدیو به محصول</p>',
                'plugincode' => 'video_in_product',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'video-in-post',
                'title'=> __('video in post','appchar'),
                'desc' => '<p style="text-align: justify">اضافه کردن ویدیو به پستها (برای استفاده از این قابلیت باید افزونه بلاگ فعال باشد)</p>',
                'plugincode' => 'video_in_post',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'advance-notification',
                'title'=> __('advance notification','appchar'),
                'desc' => '<p style="text-align: justify"> امکان نوتیفیکشن پیشرفته شامل باز شدن نوتیفیکشن در محصول مرتبط و یا اضافه کردن تصویر به نوتیفیکیشن </p>',
                'plugincode' => 'advance_notification',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'blog',
                'title'=> __('show blog','appchar'),
                'desc' => '<p style="text-align: justify"> نمایش جدیدترین نوشته ها از دسته بندی خاص در اپ</p>',
                'plugincode' => 'blog',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'lottery',
                'title'=> __('lottery','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این افزونه می توانید بین کاربران خود قرعه کشی برگزار کنید و نتیجه قرعه کشی را در اپلیکیشن خود نمایش دهید</p>',
                'plugincode' => 'lottery',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'time-to-receive-order',
                'title'=> __('time to receive order','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این پلاگین ، می توان در هنگام ثبت سفارش از مشتری روز و ساعتی که مایل است کالا را تحویل دهد ، دریافت کرد .</p>',
                'plugincode' => 'time_to_receive_order',
                'is_plugin'     => false,
                'has_setting'   => true,
            ),
            array(
                'name' => 'time-to-send-order',
                'title'=> __('time to send order','appchar'),
                'desc' => '<p style="text-align: justify">با استفاده از این پلاگین ، می توان در هنگام ثبت سفارش از مشتری روز و ساعتی که مایل به دریافت سفارش هست را دریافت کرد</p>',
                'plugincode' => 'time_to_send_order',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'post-builder',
                'title'=> __('post builder','appchar'),
                'desc' => '<p style="text-align: justify">در صورت استفاده از افزونه ی وبلاگ، با این افزونه می توانید طراحی پست خود را مناسب تر یا زیباتر نمایید</p>',
                'plugincode' => 'post_builder',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(

                'name' => 'introduce-to-friends',
                'title'=> __('introduce to friends','appchar'),
                'desc' => '<p style="text-align: justify">به کمک این پلاگین امکان اعلام کد معرفی اپلیکیشن به دوستان از طریق شبکه های اجتماعی و دریافت تخفیف در صورت ثبت نام دوستان فعال می گردد .</p>',
                'plugincode' => 'introduce_to_friends',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'login-with-sms',
                'title'=> __('Login with SMS','appchar'),
                'desc' => '<p style="text-align: justify">از طریق این قابلیت میتوانید سرویس ثبت نام و لاگین خود را به صورت یکپارچه از طریق سرویس پیامک مدیریت کنید</p>',
                'plugincode' => 'login_with_sms',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'hierarchical-filter',
                'title'=> __('Hierarchical Filter','appchar'),
                'desc' => '<p style="text-align: justify">ازطریق این پلاگین میتوانید قابلیت فیلتر کردن سلسله مراتبی را به سیستم خود اضافه نمایید.</p>',
                'plugincode' => 'hierarchical_filter',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'ios-for-publish',
                'title'=> __('IOS for publish','appchar'),
                'desc' => '<p style="text-align: justify"> پلاگینی مخصوص انتشاراپ با لایسنس اینترپرایز </p>',
                'plugincode' => 'ios_for_publish',
                'is_plugin'     => false,
                'has_setting'   => false,
            ),
            array(
                'name' => 'appstore-distribution',
                'title'=> __('appstore distribution','appchar'),
                'desc' => '<p style="text-align: justify"> پلاگینی مخصوص انتشاراپ در اپستور </p>',
                'plugincode' => 'appstore_distribution',
                'is_plugin'     => false,
                'has_setting'   => false,
            )
        );
    }
    public function get_android_extension_code($plugincode){
        $extensions= get_option('appchar_extensions');
        $android_activation_code = (isset($extensions[$plugincode]['android']['activation_code']))?$extensions[$plugincode]['android']['activation_code']:get_option($plugincode.'-activation-code','');
        return $android_activation_code;
    }
    public function get_ios_extension_code($plugincode){
        $extensions= get_option('appchar_extensions');
        $ios_activation_code = (isset($extensions[$plugincode]['ios']['activation_code']))?$extensions[$plugincode]['ios']['activation_code']:'';
        return $ios_activation_code;
    }
    public function set_extension_code($plugincode,$devices,$license){
        $extensions= get_option('appchar_extensions');
        $appchar_extensions[$plugincode][$devices]['activation-code'] = substr($license,-7);
        $appchar_extensions[$plugincode][$devices]['activation-hash-code'] = md5($license);
    }
    function view(){
        foreach (self::extensionName() as $extension) {
            ?>
            <div class="plugin-card plugin-card-<?php echo $extension['name'] ?>">
                <div class="plugin-card-top">
                    <div class="name column-name">
                        <h3>
                            <?php if($extension['is_plugin']){ ?>

                                <a href="<?php echo site_url(); ?>/wp-admin/plugin-install.php?tab=plugin-information&amp;plugin=<?php echo $extension['name'] ?>&amp;TB_iframe=true&amp;width=772&amp;height=352"
                                   class="thickbox open-plugin-details-modal">
                                    <?php echo $extension['title'] ?><img src="<?php echo APPCHAR_IMG_URL.$extension['name'].'.jpg'; ?>"
                                                                          class="plugin-icon" alt="">
                                </a>
                            <?php }else{ ?>
                                <?php echo $extension['title'] ?><img src="<?php echo APPCHAR_IMG_URL.$extension['name'].'.jpg'; ?>" class="plugin-icon">
                            <?php } ?>
                        </h3>
                    </div>
                    <?php if($extension['is_plugin']): ?>
                        <div class="action-links">
                            <ul class="plugin-action-buttons">
                                <!--I remove install button from the extention card due to the serious problems.--->
                                <?php add_thickbox(); ?>

                            </ul>
                        </div>
                        <?php
                    endif;
                    ?>
                    <div class="desc column-description">
                        <?php echo $extension['desc'] ?>
                    </div>
                </div>
                <div class="plugin-card-bottom">
                    <?php
                    $devices = array('android','ios');
                    foreach ($devices as $device){
                        $this->generateInputFields($extension,$device);
                    }
                    ?>
                </div>
            </div>
            <?php
        }
    }

    /*
     * create a view of extension for
     */
    function generateInputFields($extension,$device){
        $extensions= get_option('appchar_extensions');
        $old_activation_code = ($device == 'android')?get_option($extension['plugincode'].'-activation-code',''):'';
        $activation_code = (isset($extensions[$extension['plugincode']][$device]['activation-code']))?$extensions[$extension['plugincode']][$device]['activation-code']:$old_activation_code;
        ?>
        <form method="post">
            <input type="hidden" name="plugincode" value="<?php echo $extension['plugincode']; ?>">
            <input type="hidden" name="device" value="<?php echo $device; ?>">
            <?php if($activation_code ==''): ?>
                <div class="" style="float:right; width: 260px ">
                    <input type="text" name="activate-code[<?php echo $extension['name'] ?>]" style="width: 100%" placeholder="<?php echo $device; ?>" >
                </div>

                <div class="" style="float:left; width: 180px ">
                    <input type="submit" class="install-now button" name="enable" value="<?php _e('enable','appchar') ?>" style="width: 100% ;">
                </div>
                <!--            <div class="column-downloaded" style="width: 100%;max-width: 180px;"><br>-->
                <!--                --><?php
//                _e('After installing the plugin, enter your license','appchar');
//                ?>
                <!--            </div>-->

                <?php
            else:
                ?>
                <div class="" style="float:right; width: 260px ">
                    <input type="text" name="activate-code[<?php echo $extension['name'] ?>]" style="width: 100%" <?php  echo 'value="*************************'. $activation_code .'" readonly'; ?> >
                </div>
                <div class="" style="float:left; width: 180px ">
                    <span class="button button-disabled" style="float: right; text-align:center; background-color: #87D37C !important;"><?php _e('enabled','appchar') ?></span>
                    <input type="submit" class="install-now button" name="disable" value="<?php _e('disable','appchar') ?>" style="float: left;">
                </div>
                <!--            <div class="column-downloaded" style="max-width: 100%"><br>-->
                <!--                --><?php
//                _e('deactivate your license so you can activate it on another WordPress site','appchar');
//                ?>
                <!--            </div>-->
                <?php
            endif;
            ?>
        </form>
        <?php
    }

    /*
     * function to sendActivation code to sever
     *
     */
    function activateLicense($plugincode,$license,$device){
        $url = "http://appchar.com/api.asmx/register?plugincode=$plugincode&license=$license&domain=$this->site_url&type=$device";
        $output = wp_remote_get($url);
        if(is_wp_error($output)){
            return '<div class="notice notice-error is-dismissible"><p>متاسفانه در ارتباط با سرور خطایی رخ داده است لطفا مجددا تلاش نمایید</p></div>';
        }
        $request=json_decode($output['body'],true);
        if($request['Status']){
            $appchar_extensions = get_option('appchar_extensions',array());
            $appchar_extensions[$plugincode][$device]['activation-code'] = substr($license,-7);
            $appchar_extensions[$plugincode][$device]['activation-hash-code'] = md5($license);
            update_option('appchar_extensions', $appchar_extensions);
            $msg = '<div class="notice notice-success is-dismissible"><p>'.$request['Message'].'</p></div>';
        }else{
            $msg = '<div class="notice notice-error is-dismissible"><p>'.$request['Message'].'</p></div>';
        }
        return $msg;
    }

    function deactiveLicense($plugincode,$device){
        $appchar_extensions = get_option('appchar_extensions',array());
        $license = (isset($appchar_extensions[$plugincode][$device]['activation-hash-code']))?$appchar_extensions[$plugincode][$device]['activation-hash-code']:get_option($plugincode.'-activation-hash-code','');
        $url = "http://appchar.com/api.asmx/unregister?plugincode=$plugincode&license=$license&domain=$this->site_url&type=$device";
        $output = wp_remote_get($url);
        if(is_wp_error($output)){
            return '<div class="notice notice-error is-dismissible"><p>متاسفانه در ارتباط با سرور خطایی رخ داده است لطفا مجددا تلاش نمایید</p></div>';
        }
        $request=json_decode($output['body'],true);
        if($request['Status']) {
            if(get_option($plugincode.'-activation-hash-code',false)){
                delete_option($plugincode.'-activation-hash-code');
                delete_option($plugincode.'-activation-code');
            }else{
                unset($appchar_extensions[$plugincode][$device]);
                update_option('appchar_extensions',$appchar_extensions);
            }
            $msg = '<div id="message" class="notice notice-success is-dismissible"><p> '. __('This feature was disabled', 'appchar') . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>';
        }else{
            $msg = '<div id="message" class="notice notice-error is-dismissible"><p>' . __('This feature is not disabled', 'appchar') . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>';
        }
        return $msg;
    }
//    public static function sendRequestActivation(){
//        $url=get_site_url();
//        $find = array( 'http://', 'https://' );
//        $replace = '';
//        $site_url = str_replace( $find, $replace, $url );
//        $url = "http://appchar.com/api.asmx/pluginsWithKeys?domain=".$site_url;
//        $output = wp_remote_get($url);
//        if(is_wp_error($output)){
//            return '<div class="notice notice-error is-dismissible"><p>متاسفانه در ارتباط با سرور خطایی رخ داده است لطفا مجددا تلاش نمایید</p></div>';
//        }
//        $request=json_decode($output,true);
//        $request2 = array();
//        foreach ($request as $rqst){
//            $request2[$rqst['Code']] = $rqst['Licence'];
//        }
//        return $request2;
//    }
    public static function extensionIsActive($plugincode){
        $extensions= get_option('appchar_extensions');
        $android_activation_code = (isset($extensions[$plugincode]['android']['activation-code']))?$extensions[$plugincode]['android']['activation-code']:get_option($plugincode.'-activation-code','');
        $ios_activation_code = (isset($extensions[$plugincode]['ios']['activation-code']))?$extensions[$plugincode]['ios']['activation-code']:'';
        //$request = self::sendRequestActivation();
        //if(isset($request[$plugincode])) {
        if($android_activation_code!='' || $ios_activation_code!=''){
            return true;
        }
        return false;
    }
    public static function has_settings(){
        $has_setting = false;
        $extentions = self::extensionName();
        foreach ($extentions as $extention){
            if($extention['has_setting']== true){
                if(self::extensionIsActive($extention['plugincode'])){
                    $has_setting = true;
                }
            }
        }
        return $has_setting;
    }

    /*
     * get active extensions that has setting
     */
    public static function getActiveExtensions(){
        $activeExtensions = array();
        $extentions = self::extensionName();
        foreach ($extentions as $extention){
            if($extention['has_setting']== true){
                if(self::extensionIsActive($extention['plugincode'])){
                    $activeExtensions[]= $extention;
                }
            }
        }
        return $activeExtensions;
    }
}
