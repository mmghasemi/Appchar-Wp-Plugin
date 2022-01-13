<?php


$countries_obj = new WC_Countries();
$countries = $countries_obj->__get('countries');
$default_country = $countries_obj->get_base_country();
$default_county_states = $countries_obj->get_states($default_country);
if(!get_option('appchar_states',false)){
    if (is_array($default_county_states) || is_object($default_county_states)) {
        foreach ($default_county_states as $key => $state) {
            $states[] = array(
                'id' => $key,
                'name' => $state
            );
            //echo $key.'+'.$state.'<br>';
        }
    }
}

if(isset($_POST['save_extension_setting'])){
    $states_fields = array();
    foreach ($default_county_states as $value=>$title){
        $city = array();
        if($_POST[$value]!=''){
            $city = explode('|',$_POST[$value]);
        }
        if(isset($_POST[$value.'_is_enable']) && $_POST[$value.'_is_enable']==1){
            $states_fields[] = array(
                'id'=> $value,
                'name'=> (isset($_POST['custom_'.$value.'_name']))?$_POST['custom_'.$value.'_name']:$title,
                'cities'=> $city,
            );
        }
    }
    update_option('appchar_states',$states_fields);
}
$appchar_states = get_option('appchar_states',false);
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('custom states fields', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">
                            <table class="form-table">
                                <tbody>
                                <?php
                                if(AppcharExtension::extensionIsActive('edit_checkout_fields')) {
                                ?>
                                    <tr>
                                        <th>
                                            <?php
                                            _e('your city type is', 'appchar');
                                            $options = get_option('appchar_general_setting');
                                            ?>
                                        </th>
                                        <td>
                                            <select disabled="disabled">
                                                <option>
                                                    <?php echo (isset($options['city_type']))? $options['city_type'] : __('text','appchar'); ?>
                                                </option>
                                            </select>
                                            <?php
                                            echo '<br>در صورتی که میخواهید نوع نمایش شهر را تغییر دهید به قسمت تنظیمات اپچار مراجعه نمایید'
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('customize states and cities', 'appchar'); ?>
                                            <?php
                                            if(isset($options['city_type']) && $options['city_type']=='select'){
                                                echo '<br><span style="color: red">';
                                                _e('با توجه به این که شما حالت انتخابی شهر را انتخاب نموده اید حتما به ازای هر کدام از استانهایی که انتخاب می کنید شهرهای آن را نیز تعیین نمایید','appchar');
                                                echo '</span>';
                                            }
                                            ?>
                                        </th>
                                        <td>
                                            <table class="form-table">
                                                <tbody>
                                                    <?php
                                                    if(is_array($appchar_states) || is_object($appchar_states)) {
                                                        foreach ($appchar_states as $appchar_state) {
                                                            echo '<tr>';
                                                            echo '<td style="width: 50px"><input type="checkbox" name="' . $appchar_state['id'] . '_is_enable" value="1" checked></td>';
                                                            echo '<td style="width: 150px">' . $default_county_states[$appchar_state['id']] . '</td>';
                                                            $custom_name = (isset($appchar_state['name']))?$appchar_state['name']:'';
                                                            echo '<td style="width: 150px"><input name="custom_'.$appchar_state['id'].'_name" value="'.$custom_name.'"></td>';
                                                            $display_city = ($appchar_state['cities']) ? implode('|', $appchar_state['cities']) : '';
                                                            $city_placeholder = __("Please enter your city consider, please separate cities by '|'", 'appchar');
                                                            echo '<td><textarea name=" ' . $appchar_state['id'] . '" rows="5" cols="55" placeholder="'.$city_placeholder.'">' . $display_city . '</textarea></td>';
                                                            echo '</tr>';
                                                            unset($default_county_states[$appchar_state['id']]);
                                                        }
                                                    }
                                                    if(is_array($default_county_states) || is_object($default_county_states)) {
                                                        foreach ($default_county_states as $value => $title){
                                                            echo '<tr>';
                                                            echo '<td style="width: 50px"><input type="checkbox" name="' . $value . '_is_enable" value="1"></td>';
                                                            echo '<td style="width: 150px">' . $title . '</td>';
                                                            echo '<td style="width: 150px"><input name="custom_'.$value.'_name" value=""></td>';
                                                            $display_city = __("Please enter your city consider, please separate cities by '|'", 'appchar');
                                                            echo '<td><textarea name=" ' . $value . '" rows="5" cols="55" placeholder="' . $display_city . '"></textarea></td>';
                                                            echo '</tr>';
                                                        }
                                                    }

                                                    ?>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                                </tbody>
                            </table>
                            <?php submit_button('','','save_extension_setting'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>