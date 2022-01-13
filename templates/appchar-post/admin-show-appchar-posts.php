<?php
if (isset($_GET['delete'])) {
    $post = wp_delete_post($_GET['delete']);
}

$hasposts = get_posts(array('post_type' => 'appchar_post'));

?>

<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div class="postbox">
                <h3 class="title"><?php _e('all appchar pages', 'appchar') ?></h3>
                <p><?php _e("This page will remove in future version of plugin","appchar"); ?></p>
                <div class="inside">
                    <div id="settings">
                            <table class="wp-list-table widefat fixed striped posts">
                                <tbody>
                                <?php
                                if(!$hasposts){
                                    echo '<tr><td>'.__('No pages were found','appchar').'</td></tr>';
                                }else {
                                    foreach ($hasposts as $post): ?>
                                        <tr>
                                            <td width="5%">
                                                <?php echo $post->ID; ?>
                                            </td>
                                            <td width="75%">
                                                <?php echo $post->post_title; ?>
                                            </td>
                                            <td width="10%">
                                                <a href="admin.php?page=appchar-pages&post_id=<?php echo $post->ID; ?>"><?php _e('edit','appchar') ?></a>
                                            </td>
                                            <td width="10%">
                                                <a href="admin.php?page=appchar-pages&tab=show&delete=<?php echo $post->ID; ?>" onclick="return confirm('<?php _e('You Are About To delete this post.\nThis Action Is Not Reversible.\n\n Choose [Cancel] to stop, [Ok] to delete.', 'appchar'); ?>')"><?php _e('delete','appchar') ?></a>
                                            </td>
                                        </tr>
                                <?php
                                    endforeach;
                                }
                                ?>
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
