<div>
    <?php if (isset($_GET['settings-updated'])) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved', 'appchar') ?></strong></p>
        </div>
    <?php } ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php settings_fields('notification-settings-group'); ?>
            <?php do_settings_sections('appchar-notification-setting-page'); ?>
            <?php submit_button(); ?>
        </form>
        <span class="description">
            <strong>جزییات سفارش : </strong><br>
            <code>{phone}</code> = شماره موبایل خریدار   ،
            <code>{email}</code> = ایمیل خریدار   ،
            <code>{order_id}</code> = شماره سفارش  ،
            <code>{status}</code> = وضعیت سفارش<br>
            <code>{price}</code> = مبلغ سفارش   ،
            <code>{all_items}</code> = آیتم های سفارش  ،
            <code>{all_items_qty}</code> = آیتم های سفارش همراه تعداد ،
            <code>{count_items}</code> = تعداد آیتم های سفارش  <br>
            <code>{payment_method}</code> = روش پرداخت  ،
            <code>{shipping_method}</code> = روش ارسال  ،
            <code>{description}</code> = توضیحات خریدار  ،
            <code>{transaction_id}</code> = شماره تراکنش<br><br>

            <strong>جزییات صورت حساب : </strong><br>
            <code>{b_first_name}</code> = نام خریدار   ،
            <code>{b_last_name}</code> = نام خانوادگی خریدار   ،
            <code>{b_company}</code> = نام شرکت   <br>
            <code>{b_country}</code> = کشور   ،
            <code>{b_state}</code> = ایالت/استان   ،
            <code>{b_city}</code> = شهر   ،
            <code>{b_address_1}</code> = آدرس 1   ،
            <code>{b_address_2}</code> = آدرس 2   ،
            <code>{b_postcode}</code> = کد پستی<br><br>

            <strong>جزییات حمل و نقل : </strong><br>
            <code>{sh_first_name}</code> = نام خریدار   ،
            <code>{sh_last_name}</code> = نام خانوادگی خریدار   ،
            <code>{sh_company}</code> = نام شرکت   <br>
            <code>{sh_country}</code> = کشور   ،
            <code>{sh_state}</code> = ایالت/استان   ،
            <code>{sh_city}</code> = شهر   ،
            <code>{sh_address_1}</code> = آدرس 1   ،
            <code>{sh_address_2}</code> = آدرس 2   ،
            <code>{sh_postcode}</code> = کد پستی<br><br>
        </span>
    </div>
</div>
<br class="clear">

<script>
  var ttl = document.getElementById("ttl").value;
  var span = document.getElementById("ttl_day");
  var hour = (ttl / 3600);
  var day = (hour / 24);
  span.innerHTML = "روز" + day ;
  function showDay() {
    var ttl = document.getElementById("ttl").value;
    var span = document.getElementById("ttl_day");
    var hour = (ttl / 3600);
    var day = (hour / 24);
    span.innerHTML = "روز" + day ;
  }
</script>
