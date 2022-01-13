<?php
$string_xml = new Appchar_String_XML();
$items = $string_xml->get_data_into_array();
if($_POST['save_xml_file']){
    foreach ($items as $key=>$item){
        $items[$key] = $_POST[$key];
    }
    $string_xml->update_xml($items);
}
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('home page setting', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form method="post">
                            <table class="form-table">
                                <tbody>
                                <?php
                                $string_xml->create_setting_page();
                                ?>
                                </tbody>
                            </table>
                            <?php submit_button('','','save_xml_file'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
