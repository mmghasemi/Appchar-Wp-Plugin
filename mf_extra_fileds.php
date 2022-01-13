<?php
if( !class_exists("MF_Wizard_ExtraFields")) {
    class MF_Wizard_ExtraFields
    {
        var $extra_fields;

        function __construct()
        {
            $this->extra_fields = array('phone_number' => 'شماره همراه');

            add_action('personal_options_update', array($this, 'save_extra_profile_fileds'));
            add_action('edit_user_profile_update', array($this, 'save_extra_profile_fileds'));

            add_action('show_user_profile', array($this, 'add_extra_social_links'));
            add_action('edit_user_profile', array($this, 'add_extra_social_links'));

        }

        function save_extra_profile_fileds($user_id)
        {
            foreach ($this->extra_fields as $key => $value) {
                if (isset($_POST[$key]))
                    update_user_meta($user_id, $key, sanitize_text_field($_POST[$key]));
            }

        }

        function get($user_id, $filed_name)
        {
            if (isset($this->extra_fields[$filed_name]))
                return get_the_author_meta($filed_name, $user_id);
            return '';
        }


        function set($user_id, $filed_name, $filed_value)
        {
            if (isset($this->extra_fields[$filed_name]))
                update_user_meta($user_id, $filed_name, sanitize_text_field($filed_value));
        }


        function getExtraFields()
        {
            return $this->extra_fields;
        }

        function add_extra_social_links($user)
        {
            if( isset($_GET['isSetExtraFields0098'] ) )
                return ;
            $_GET['isSetExtraFields0098'] = 'true';
            ?>
            <h3>New User Profile Fields</h3>

            <table class="form-table">
                <?php
                foreach ($this->extra_fields as $key => $value) {
                    ?>
                    <tr>
                        <th><label for="<?php echo $key; ?>"><?php echo $value; ?></label></th>
                        <td><input type="text" name="<?php echo $key; ?>"
                                   value="<?php echo esc_attr(get_the_author_meta($key, $user->ID)); ?>"
                                   class="regular-text"/></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <?php
        }

    }
}
?>