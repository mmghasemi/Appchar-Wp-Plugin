<?php
function px_register_settings() {
	add_settings_section('appchar_notification_setting_section', '', 'notification_section_callback', 'appchar-notification-setting-page');
	add_settings_field('api-key', 'Api Key', 'api_key_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
	add_settings_field('app-id', 'App Id', 'app_id_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
	add_settings_field('show_result', __('Show notification result','appchar'), 'show_notification_result_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
	add_settings_field('pdin', __('Send notifications when adding new product','appchar'), 'pdin_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
    if(AppcharExtension::extensionIsActive("advance_notification")) {
				add_settings_field('delayed_option', __('Schedule your Notification', 'appchar'), 'delayed_option_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
				add_settings_field('ttl', __('Time To Live', 'appchar'), 'ttl_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
				add_settings_field('delivery_time_of_day', __('Delivery Time Of Day'), 'delivery_time_of_day_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
        add_settings_field('poin', __('Send notifications when adding new post', 'appchar'), 'poin_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
        add_settings_field('newpost', __('New post message', 'appchar'), 'newpost_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
        add_settings_field('notificationImageType', __('Notification image type', 'appchar'), 'notificationImageType_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
    }
    add_settings_field('pdpc', __('Send notification when registration Specials','appchar'), 'pdpc_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section');
    add_settings_field('orno', __('Send notification when order status changed','appchar'), 'orno_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section' );

    add_settings_section('appchar_notification_setting_section2', '<hr>'.__('Notification Message','appchar'), 'notification_section_callback2', 'appchar-notification-setting-page');
    add_settings_field('newproduct', __('New product message','appchar'), 'newproduct_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('spproduct', __('Special product message','appchar'), 'spproduct_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	//[appchar_body_failed][appchar_body_refunded][appchar_body_cancelled]
	//[appchar_body_completed][appchar_body_onhold][appchar_body_processing][appchar_body_pending]
	add_settings_field('pending', __('Pending Payment status','appchar'), 'pending_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('processing', __('Processing status','appchar'), 'processing_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('on-hold', __('On Hold status','appchar'), 'onhold_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('completed', __('Completed status','appchar'), 'completed_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('cancelled', __('Cancelled status','appchar'), 'cancelled_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('refunded', __('Refunded status','appchar'), 'refunded_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	add_settings_field('failed', __('Failed status','appchar'), 'failed_callback', 'appchar-notification-setting-page', 'appchar_notification_setting_section2' );
	register_setting( 'notification-settings-group', 'appchar_notification_setting', 'appchar_notification_settings_validate' );
}

function notification_section_callback() {
    echo __('Settings required to send notifications','appchar');
}

function notification_section_callback2() {
    echo __('customize your notification message','appchar');
}

function api_key_callback() {
    $options = get_option('appchar_notification_setting');
    ?>
<input type="text" name="appchar_notification_setting[api-key]" size="41" value="<?php echo $options['api-key']; ?>" />
<?php
}

function app_id_callback() {
    $options = get_option('appchar_notification_setting');
    ?>
<input type="text" name="appchar_notification_setting[app-id]" size="41" value="<?php echo $options['app-id']; ?>" />
<?php
}

function show_notification_result_callback(){
    $options = get_option('appchar_notification_setting');
    $show_result = (isset($options['show_result']))?$options['show_result']:false;
	$html = '<input type="checkbox" id="show_result" name="appchar_notification_setting[show_result]" value="1"' . checked(1, $show_result, false) . '/>';
	echo $html;
}

function pdin_callback(){
    $options = get_option('appchar_notification_setting');
	$html = '<input type="checkbox" id="pdin" name="appchar_notification_setting[pdin]" value="1"' . checked(1, $options['pdin'], false) . '/>';
	echo $html;
}

function poin_callback(){
    $options = get_option('appchar_notification_setting');

	$html = '<input type="checkbox" id="poin" name="appchar_notification_setting[poin]" value="1"' . checked(1, $options['poin'], false) . '/>';
	echo $html;
}

function pdpc_callback(){
    $options = get_option('appchar_notification_setting');
	$html = '<input type="checkbox" id="pdpc" name="appchar_notification_setting[pdpc]" value="1"' . checked(1, $options['pdpc'], false) . '/>';
	echo $html;
}

function orno_callback() {
    $options = get_option('appchar_notification_setting');
    $html = '<input type="checkbox" id="orno" name="appchar_notification_setting[orno]" value="1"' . checked(1, $options['orno'], false) . '/>';
	echo $html;
}

function newproduct_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[newproduct]" >';
	if($options['newproduct']!=''){
		$html .=$options['newproduct'];
	}else {
		$html .= __('product {product name} in {blog title}', 'appchar');
	}
	$html.= '</textarea>';
	echo $html;
}

function newpost_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[newpost]" >';
	if($options['newpost']!=''){
		$html .=$options['newpost'];
	}else {
		$html .= __('post {product name} in {blog title}', 'appchar');
	}
	$html.= '</textarea>';
	echo $html;
}

function notificationImageType_callback() {
    $selectOption = array(
        'none'      => __('none','appchar'),
        'background' => __('background','appchar'),
        'large'     => __('large','appchar')
    );
	$options = get_option('appchar_notification_setting');
	$html = '<select class="regular-text" name="appchar_notification_setting[notificationImageType]" >';
    foreach ($selectOption as $key=>$option) {
        if($options['notificationImageType']==$key){
            $html .= '<option value="'.$key.'" selected>'.$option.'</option>';
        }else{
            $html .= '<option value="'.$key.'">'.$option.'</option>';
        }
	}
	$html.= '</select>';
	echo $html;
}

function delayed_option_callback() {
		$selectOption = array(
			'none' => __('none', 'appchar'),
			'timezone' => __('Timezone', 'appchar'),
			'last-active' => __('Last Active', 'appchar')
		);
		$options = get_option('appchar_notification_setting');
		$html = '<select class="regular-text" name="appchar_notification_setting[delayed_option]" >';
			foreach ($selectOption as $key => $option) {
				if($options['delayed_option'] == $key) {
					$html .= '<option value"' . $key . '" selected>' . $option . '</option>';
				}else {
					$html .= '<option value="'.$key.'">'.$option.'</option>';
				}
			}
			$html .= '</select>';
			echo $html;
}

function ttl_callback() {
		$options = get_option('appchar_notification_setting');
		$html = '<input type="number" max="2419200" id="ttl" name="appchar_notification_setting[ttl]" value="';
		if($options['ttl'] != '') {
			$html .= $options['ttl'];
		}else {
			$html .= "259200";
		}
		$html .= '" onchange="showDay();"/>';
		$html .= '<span id="ttl_day"></span>';
		echo $html;
}

function delivery_time_of_day_callback() {
	$selectOptions = array(
		'none' => __('none', 'appchar'),
		'1' => '1',
		'2' => '2',
		'3' => '3',
		'4' => '4',
		'5' => '5',
		'6' => '6',
		'7' => '7',
		'8' => '8',
		'9' => '9',
		'10' => '10',
		'11' => '11',
		'12' => '12',
		'13' => '13',
		'14' => '14',
		'15' => '15',
		'16' => '16',
		'17' => '17',
		'18' => '18',
		'19' => '19',
		'20' => '20',
		'21' => '21',
		'22' => '22',
		'23' => '23',
		'24' => '24'
	);
	$options = get_option('appchar_notification_setting');
	$html = '<select name="appchar_notification_setting[delivery_time_of_day]">';
	foreach ($selectOptions as $key => $option) {
		if($options['delivery_time_of_day'] == $key) {
			$html .= '<option value="' . $key . '" selected>' . $option . '</option>';
		}else {
			$html .= '<option value="' . $key . '">' . $option . '</option>';
		}
	}
	$html .= '</select>';
	echo $html;
}

function spproduct_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[spproduct]" >';
	if($options['spproduct']!=''){
		$html .=$options['spproduct'];
	}else {
		$html .= __('Save money on {product name} with {percent price} percent discount on {blog title}', 'appchar');
	}
	$html.='</textarea>';
	echo $html;
}

function pending_callback() {
    $options = get_option('appchar_notification_setting');
    $html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[pending]" >';
	if($options['pending']!=''){
	$html .=$options['pending'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of pending
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function processing_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[processing]">';
	if($options['processing']!=''){
		$html .=$options['processing'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of processing
Custom items: {all_items}
Order value: {price} kfjkdlsjklsdf
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function onhold_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[on-hold]">';
	if($options['on-hold']!=''){
		$html .=$options['on-hold'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of on hold
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function completed_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[completed]">';
	if($options['completed']!=''){
		$html .=$options['completed'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of comleted
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function cancelled_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[cancelled]">';
	if($options['cancelled']!=''){
		$html .=$options['cancelled'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of cancelled
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function refunded_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[refunded]">';
	if($options['refunded']!=''){
		$html .=$options['refunded'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of refunded
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function failed_callback() {
	$options = get_option('appchar_notification_setting');
	$html = '<textarea rows="5" cols="55" class="regular-text" name="appchar_notification_setting[failed]">';
	if($options['failed']!=''){
		$html .=$options['failed'];
	}else{
		$html .=__('hello {b_first_name} {b_last_name}
Order {order_id} has been received and is now in a state of failed
Custom items: {all_items}
Order value: {price}
Transaction Number: {transaction_id}','appchar');
	}
	$html	.='</textarea>';
	echo $html;
}

function appchar_notification_settings_validate($arr_input) {
	$options = get_option('appchar_notification_setting');

	if(isset($arr_input['api-key'])) {
		$options['api-key'] = trim($arr_input['api-key']);
	}

	if(isset($arr_input['app-id'])) {
		$options['app-id'] = trim($arr_input['app-id']);
	}

	if(isset($arr_input['show_result'])) {
		$options['show_result'] = trim($arr_input['show_result']);
	}else $options['show_result'] = 0;

	if(isset($arr_input['pdin'])) {
		$options['pdin'] = trim($arr_input['pdin']);
	}else $options['pdin'] = 0;

	if(isset($arr_input['poin'])) {
		$options['poin'] = trim($arr_input['poin']);
	}else $options['poin'] = 0;

	if(isset($arr_input['pdpc'])) {
		$options['pdpc'] = trim($arr_input['pdpc']);
	}else $options['pdpc'] = 0;

	if(isset($arr_input['orno'])) {
		$options['orno'] = trim($arr_input['orno']);
	} else $options['orno'] = 0;

	if(isset($arr_input['newproduct'])) {
		$options['newproduct'] = trim($arr_input['newproduct']);
	}

	if(isset($arr_input['newpost'])) {
		$options['newpost'] = trim($arr_input['newpost']);
	}

    if(isset($arr_input['notificationImageType'])) {
        $options['notificationImageType'] = trim($arr_input['notificationImageType']);
    }

		if(isset($arr_input['delayed_option'])) {
			$options['delayed_option'] = trim($arr_input['delayed_option']);
		}
		if(isset($arr_input['ttl'])) {
			$options['ttl'] = trim($arr_input['ttl']);
		}

		if(isset($arr_input['delivery_time_of_day'])) {
			$options['delivery_time_of_day'] = trim($arr_input['delivery_time_of_day']);
		}

	// spproduct = special product
	if(isset($arr_input['spproduct'])) {
		$options['spproduct'] = trim($arr_input['spproduct']);
	}

	//[appchar_body_failed][appchar_body_refunded][appchar_body_cancelled]
	//[appchar_body_completed][appchar_body_onhold][appchar_body_processing][appchar_body_pending]
	if(isset($arr_input['pending'])) {
		$options['pending'] = trim($arr_input['pending']);
	}

	if(isset($arr_input['processing'])) {
		$options['processing'] = trim($arr_input['processing']);
	}

	if(isset($arr_input['on-hold'])) {
		$options['on-hold'] = trim($arr_input['on-hold']);
	}

	if(isset($arr_input['completed'])) {
		$options['completed'] = trim($arr_input['completed']);
	}

	if(isset($arr_input['cancelled'])) {
		$options['cancelled'] = trim($arr_input['cancelled']);
	}

	if(isset($arr_input['refunded'])) {
		$options['refunded'] = trim($arr_input['refunded']);
	}

	if(isset($arr_input['failed'])) {
		$options['failed'] = trim($arr_input['failed']);
	}
    return $options;
}

add_action('admin_init', 'px_register_settings');
?>
