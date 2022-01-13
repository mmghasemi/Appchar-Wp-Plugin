<?php
if(!get_option('appchar_custom_register_fields',false)) {
    $default_fields = array(
        array(
            'name' => 'first_name',
            'label' => __('first name', 'appchar'),
            'title' => __('first name', 'appchar'),
            'type' => 'text',
            'visible' => true,
            'require' => false,
        ),
        array(
            'name' => 'last_name',
            'label' => __('last name', 'appchar'),
            'title' => __('last name', 'appchar'),
            'type' => 'text',
            'visible' => true,
            'require' => false,
        ),
        array(
            'name' => 'username',
            'label' => __('username', 'appchar'),
            'title' => __('username', 'appchar'),
            'type' => 'text',
            'visible' => true,
            'require' => true,
        ),
        array(
            'name' => 'email',
            'label' => __('email', 'appchar'),
            'title' => __('email', 'appchar'),
            'type' => 'email',
            'visible' => true,
            'require' => true,
        ),
        array(
            'name' => 'password',
            'label' => __('password', 'appchar'),
            'title' => __('password', 'appchar'),
            'type' => 'password',
            'visible' => true,
            'require' => true,
        )
    );
    update_option('appchar_custom_register_fields', $default_fields);
}
if(!get_option('appchar_custom_login_fields',false)) {
    $default_login_fields=array(
        array(
            'name'      => 'username_login',
            'label'      => __('username or email','appchar'),
            'title'      => __('username or email','appchar'),
            'type'      => 'email',
        ),
        array(
            'name'      => 'password_login',
            'label'      => __('password','appchar'),
            'title'      => __('password','appchar'),
            'type'      => 'password',
        )
    );
    update_option('appchar_custom_login_fields',$default_login_fields);
}

if(isset($_POST['save_extension_setting'])){
    $fields = get_option('appchar_custom_register_fields',false);
    $new_fields = array();
    foreach ($fields as $field){
        $field['title'] = $_POST[$field['name']]['title'];
        $field['type'] = $_POST[$field['name']]['type'];
        $field['visible']= (isset($_POST[$field['name']]['visible']))?true:false;
        $field['require']= (isset($_POST[$field['name']]['require']))?true:false;
        $new_fields[]=$field;
    }
    update_option('appchar_custom_register_fields',$new_fields);

    $fields = get_option('appchar_custom_login_fields',false);
    $new_fields = array();
    foreach ($fields as $field){
        $field['title'] = $_POST[$field['name']]['title'];
        $field['type'] = $_POST[$field['name']]['type'];
        $new_fields[]=$field;
    }
    update_option('appchar_custom_login_fields',$new_fields);
}
$fields = get_option('appchar_custom_register_fields',false);
$login_fields= get_option('appchar_custom_login_fields',false);
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('Custom Register Fields', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">
                            <table class="form-table">
                                <tbody>
                                <?php
                                if(AppcharExtension::extensionIsActive('custom_register_fields')) {
                                    ?>
                                    <tr>
                                        <th>
                                            <?php _e('custom register fields', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <?php
                                            $field_types = array('text'=> __('text','appchar'), 'number'=> __('number','appchar'), 'phone'=> __('phone','appchar'), 'email'=> __('email','appchar'), 'password'=> __('password','appchar'), 'mobile'=> __('mobile','appchar'));
                                            echo '<table><tbody><tr><th>'.__('label','appchar').'</th><th>'.__('title','appchar').'</th><th>'.__('fields type','appchar').'</th><th>'.__('visible','appchar').'</th><th>'.__('require filed','appchar').'</th></tr>';
                                            foreach ($fields as $field){
                                                echo '<tr><td>'.$field['label'].'</td>';
                                                echo '<td><input type="text" name="'.$field['name'].'[title]" value="'.$field['title'].'"></td>';
                                                $f_type = '';
                                                foreach ($field_types as $value=>$field_type){
                                                    if($field['type']==$value){
                                                        $f_type .= '<option value="' . $value . '" selected>' . $field_type . '</option>';
                                                    }else {
                                                        $f_type .= '<option value="' . $value . '">' . $field_type . '</option>';
                                                    }
                                                }
                                                $readonly = ($field['name'] == 'password' || $field['name'] == 'email')?'style="display:none"':"";
                                                echo '<td><select name="'.$field['name'].'[type]" '.$readonly.'>'.$f_type.'</select></td>';
                                                $checked = ($field['visible'])?"checked":"";
                                                echo '<td><input type="checkbox" '.$readonly.' name="'.$field['name'].'[visible]"'.$checked.'></td>';
                                                $checked = ($field['require'])?"checked":"";
                                                echo '<td><input type="checkbox" '.$readonly.' name="'.$field['name'].'[require]"'.$checked.'></td>';
                                            }
                                            echo '</tbody></table>';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            <?php _e('custom login fields', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <?php
                                            echo '<table><tbody><tr><th>'.__('label','appchar').'</th><th>'.__('title','appchar').'</th><th>'.__('type','appchar').'</th></tr>';
                                            foreach ($login_fields as $field){
                                                echo '<tr><td>'.$field['label'].'</td>';
                                                echo '<td><input type="text" name="'.$field['name'].'[title]" value="'.$field['title'].'"></td>';
                                                $f_type = '';
                                                foreach ($field_types as $value=>$field_type){
                                                    if($field['type']==$value){
                                                        $f_type .= '<option value="' . $value . '" selected>' . $field_type . '</option>';
                                                    }else {
                                                        $f_type .= '<option value="' . $value . '">' . $field_type . '</option>';
                                                    }
                                                }
                                                $readonly = ($field['name'] == 'password_login')?'style="display:none"':"";
                                                echo '<td><select name="'.$field['name'].'[type]" '.$readonly.'>'.$f_type.'</select></td>';
                                            }
                                            echo '</tbody></table>';
                                            ?>
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

