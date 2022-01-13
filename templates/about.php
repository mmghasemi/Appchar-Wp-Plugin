<?php
update_option( 'redirect_to_appchar_about_page_check', 'no' );
$plugins = get_plugins();
$appchar_data = $plugins['appchar-woocommerce/appchar-woocommerce.php'];
?>
<div class="wrap about-wrap">

    <h1>افزونه اپلیکیشن ساز اپچار - نسخه ووکامرس - نگارش <?php echo $appchar_data['Version']; ?></h1>

    <div class="about-text">از شما بابت استفاده از این نگارش افزونه اپچار سپاسگزاریم . در زیر امکانات نگارش جدید را مشاهده نمایید .</div>

    <div class="wp-badge" style="background-color:#fff !important;background-image:url('<?php echo APPCHAR_IMG_URL.'logo.png'; ?>') !important;"></div>

    <h2 class="nav-tab-wrapper">
        <a href="index.php?page=about-appchar" class="nav-tab nav-tab-active">صفحه ساز پیشرفته</a>
        <a target="_blank" href="http://appchar.com" class="nav-tab">سایت اپچار</a>
    </h2>

    <div class="changelog">
        <h3>توسعه دهندگان نسخه جدید</h3>

        <p>
            این افزونه به صورت رایگان از سوی
            <a target="_blank" href="http://appchar.com/">اپلیکیشن ساز اپچار</a>
            ارائه شده است . هر گونه کپی برداری و کسب درآمد از آن توسط سایرین غیر مجاز می باشد .
        </p>

        <p>برنامه نویس:  <strong><a href="http://alishojaei.ir/">علی شجاعی</a></strong></p>

    </div>
    <hr/>


    <div class="feature-section two-col">
        <div class="col">
            <h3>صفحه ساز پیشرفته</h3>
            <p>از طریق این صفحه ساز میتوانید صفحه اول اپلکیشن خود را همانگونه که دوست دارید طراحی و به کاربرانتان ارائه نمایید</p>
            <p>همچنین میتوانید این صفحه را تغییر داده و کاربران خود را از تغییرات به وجود آمده و پیشنهادها شگفت زده کنید</p>

        </div>
        <div class="col">
            <img src="<?php echo APPCHAR_IMG_URL.'homepage.jpg'; ?>" />
        </div>
    </div>





    <div class="changelog under-the-hood feature-list">
        <div class="last-feature">
            <h3> و امکانات بی نظیر دیگر ....</h3>

            <div class="return-to-dashboard">
                <a href="<?php echo admin_url( 'admin.php?page=appchar' ); ?>">رفتن به تنظیمات پلاگین اپچار</a>
            </div>
        </div>
    </div>
    <div class="clear"></div>
</div>