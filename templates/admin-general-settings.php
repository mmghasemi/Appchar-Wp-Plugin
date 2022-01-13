<div>
    <?php if (isset($_GET['settings-updated'])) { ?>
        <div id="message" class="updated">
            <p><strong><?php _e('Settings saved', 'appchar') ?></strong></p>
        </div>
    <?php } ?>
    <div class="wrap">
        <form method="post" action="options.php">
            <?php
            settings_fields("appchar_general_section");
            settings_fields("appchar_visibility_section");
            settings_fields("appchar_pinned_section");
            settings_fields("appchar_extension_section");
            do_settings_sections("appchar-setting");
            submit_button();
            ?>
        </form>
    </div>
</div>