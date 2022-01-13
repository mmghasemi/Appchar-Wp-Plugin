<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 5/4/17
 * Time: 9:50 AM
 */
$countries = new WC_Countries();
$billing = $countries->get_address_fields($countries->get_base_country(), 'billing_');
$billing['billing_city'] = array('label' => 'شهر', 'required' => 1);

if (isset($_POST['save_extension_setting'])) {
    $checkout_fields = get_option('appchar_checkout_fields_settings', array());
    $billing_fields = array();
    foreach ($billing as $key => $billing_field) {
        if ($_POST[$key . '_label'] != '') {
            $billing_fields[] = array(
                'name' => $key,
                'label' => $_POST[$key . '_label'],
                'visible' => (isset($_POST[$key . '_visible'])) ? true : false,
                'required' => (isset($_POST[$key . '_required'])) ? true : false,
            );
        } else {
            $billing_fields[] = array(
                'name' => $key,
                'label' => $billing_field['label'],
                'visible' => false,
                'required' => false,
            );
        }
    }
    $checkout_fields['billing'] = $billing_fields;
    update_option('appchar_checkout_fields_settings', $checkout_fields);

}
$appchar_checkout_fields = get_option('appchar_checkout_fields_settings', array());
$appchar_billing_fields = $appchar_checkout_fields['billing'];
$appchar_billing_fields2 = array();
foreach ($appchar_billing_fields as $appchar_billing_field) {
    $name = $appchar_billing_field['name'];
    unset($appchar_billing_field['name']);
    $appchar_billing_fields2[$name] = $appchar_billing_field;
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
                            <table class="form-table">
                                <tbody>
                                <?php
                                if (is_array($billing) || is_object($billing)) {
                                    foreach ($billing as $key => $billing_field) {
                                        if (isset($appchar_billing_fields2[$key])) {
                                            $label = $appchar_billing_fields2[$key]["label"];
                                            $required = ($appchar_billing_fields2[$key]['required'] == true) ? "checked" : "";
                                            $visible = ($appchar_billing_fields2[$key]['visible'] == true) ? "checked" : "";
                                        } else {
                                            $label = ($billing_field['label']) ? $billing_field['label'] : '';
                                            $required = ($billing_field['required'] == 1) ? "checked" : "";
                                            $visible = "";
                                        }
                                        $name = str_replace('billing_', '', $key);
                                        echo '<tr>';
                                        echo '<td style="width: 50px">' . $name . '</td>';
                                        echo '<td style="width: 150px"><input value="' . $label . '" name="' . $key . '_label"/></td>';
                                        echo '<td>' . __('visible', 'appchar') . ' <input type="checkbox" value="enable" name="' . $key . '_visible" ' . $visible . '></td>';
                                        echo '<td>' . __('require', 'appchar') . ' <input type="checkbox" value="enable" name="' . $key . '_required" ' . $required . '></td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                                </tbody>
                            </table>
                            <?php submit_button('', '', 'save_extension_setting'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
