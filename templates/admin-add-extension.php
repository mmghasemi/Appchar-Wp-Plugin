<?php
//if ( ! current_user_can('install_plugins') )
//    wp_die(__('You do not have sufficient permissions to install plugins on this site.','appchar'));

global $extension;

if(isset($_POST['enable'])){
    foreach ($_POST['activate-code'] as $value){
        echo $extension->activateLicense($_POST['plugincode'],$value,$_POST['device']);
    }
}
if(isset($_POST['disable'])){
    foreach ($_POST['activate-code'] as $value){

        echo $extension->deactiveLicense($_POST['plugincode'],$_POST['device']);
    }
}

?>

<div class="wrap">
    <br class="clear">
    <div class="wp-list-table widefat plugin-install">
        <h2 class="screen-reader-text"><?php _e('extensions','appchar') ?></h2>	<div id="the-list">

            <?php $extension->view(); ?>

        </div>
        <br class="clear">
    </div>