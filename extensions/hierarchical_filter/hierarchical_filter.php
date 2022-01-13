<?php
/**
 * Created by PhpStorm.
 * User: alishojaei
 * Date: 2019-05-29
 * Time: 11:43
 */


//hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_Appchar_filters_hierarchical_taxonomy', 0 );

//create a custom taxonomy name it Appchar filters for your posts

function create_Appchar_filters_hierarchical_taxonomy() {

// Add new taxonomy, make it hierarchical like categories
//first do the translations part for GUI

    $labels = array(
        'name' => _x( 'Appchar filters', 'taxonomy general name' ),
        'singular_name' => _x( 'Appchar filter', 'taxonomy singular name' ),
        'search_items' =>  __( 'Search Appchar filters' ),
        'all_items' => __( 'All Appchar filters' ),
        'parent_item' => __( 'Parent Appchar filter' ),
        'parent_item_colon' => __( 'Parent Appchar filter:' ),
        'edit_item' => __( 'Edit Appchar filter' ),
        'update_item' => __( 'Update Appchar filter' ),
        'add_new_item' => __( 'Add New Appchar filter' ),
        'new_item_name' => __( 'New Appchar filter Name' ),
        'menu_name' => __( 'Appchar filters' ),
    );

// Now register the taxonomy

    register_taxonomy('appchar_filter',array('product'), array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'Appchar_filter' ),
    ));

}

function thumbnail_appchar_filter_columns( $columns )
{
    $columns['thumbnail'] = __('thumbnail','appchar');

    return $columns;
}
add_filter('manage_edit-appchar_filter_columns' , 'thumbnail_appchar_filter_columns');
function appchar_thumbnail_taxonomy_columns_content( $content, $column_name, $term_id )
{
    $upload_dir = wp_upload_dir();
    if ( 'thumbnail' == $column_name ) {
        $attachment_id = get_term_meta($term_id,"thumbnail_id",true);
        if($attachment_id) {
            $content = wp_get_attachment_image($attachment_id,array(50,50));
        }else{
            $content = '<img width="50" src="'.wc_placeholder_img_src().'">';
        }
    }
    return $content;
}
add_filter( 'manage_appchar_filter_custom_column', 'appchar_thumbnail_taxonomy_columns_content', 10, 3 );

add_filter('appchar_get_product_terms', "add_image_to_object");
function add_image_to_object($terms){
    $upload_dir = wp_upload_dir();

    foreach ($terms as &$term){
        $attachment_id = get_term_meta($term->term_id,"thumbnail_id",true);
        if($attachment_id) {
            $term->image = wp_get_attachment_image_url($attachment_id);
        }else{

            $term->image = wc_placeholder_img_src();
        }
    }
    return $terms;
}

add_action( 'appchar_filter_add_form_fields','add_appchar_filter_fields');
add_action( 'created_term', 'save_appchar_filter_fields', 10, 3 );
add_action( 'appchar_filter_edit_form_fields','edit_appchar_filter_fields', 10 );
add_action( 'edit_term', 'save_appchar_filter_fields', 10, 3 );
/**
 * Category thumbnail fields.
 */
function add_appchar_filter_fields() {
    ?>
    <div class="form-field term-thumbnail-wrap">
        <label><?php esc_html_e( 'Thumbnail', 'woocommerce' ); ?></label>
        <div id="appchar_filter_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( wc_placeholder_img_src() ); ?>" width="60px" height="60px" /></div>
        <div style="line-height: 60px;">
            <input type="hidden" id="appchar_filter_thumbnail_id" name="appchar_filter_thumbnail_id" />
            <button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'woocommerce' ); ?></button>
            <button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'woocommerce' ); ?></button>
        </div>
        <script type="text/javascript">

            // Only show the "remove image" button when needed
            if ( ! jQuery( '#appchar_filter_thumbnail_id' ).val() ) {
                jQuery( '.remove_image_button' ).hide();
            }

            // Uploading files
            var file_frame;

            jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

                event.preventDefault();

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.downloadable_file = wp.media({
                    title: '<?php esc_html_e( 'Choose an image', 'woocommerce' ); ?>',
                    button: {
                        text: '<?php esc_html_e( 'Use image', 'woocommerce' ); ?>'
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                    var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                    jQuery( '#appchar_filter_thumbnail_id' ).val( attachment.id );
                    jQuery( '#appchar_filter_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                    jQuery( '.remove_image_button' ).show();
                });

                // Finally, open the modal.
                file_frame.open();
            });

            jQuery( document ).on( 'click', '.remove_image_button', function() {
                jQuery( '#appchar_filter_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                jQuery( '#appchar_filter_thumbnail_id' ).val( '' );
                jQuery( '.remove_image_button' ).hide();
                return false;
            });

            jQuery( document ).ajaxComplete( function( event, request, options ) {
                if ( request && 4 === request.readyState && 200 === request.status
                    && options.data && 0 <= options.data.indexOf( 'action=add-tag' ) ) {

                    var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
                    if ( ! res || res.errors ) {
                        return;
                    }
                    // Clear Thumbnail fields on submit
                    jQuery( '#appchar_filter_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                    jQuery( '#appchar_filter_thumbnail_id' ).val( '' );
                    jQuery( '.remove_image_button' ).hide();
                    // Clear Display type field on submit
                    jQuery( '#display_type' ).val( '' );
                    return;
                }
            } );

        </script>
        <div class="clear"></div>
    </div>
    <?php
}

/**
 * Save category fields
 *
 * @param mixed  $term_id Term ID being saved.
 * @param mixed  $tt_id Term taxonomy ID.
 * @param string $taxonomy Taxonomy slug.
 */
function save_appchar_filter_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
    if ( isset( $_POST['appchar_filter_thumbnail_id'] ) && 'appchar_filter' === $taxonomy ) { // WPCS: CSRF ok, input var ok.
        update_term_meta( $term_id, 'thumbnail_id', absint( $_POST['appchar_filter_thumbnail_id'] ) ); // WPCS: CSRF ok, input var ok.
    }
}

/**
 * Edit category thumbnail field.
 *
 * @param mixed $term Term (category) being edited.
 */
function edit_appchar_filter_fields( $term ) {

    $thumbnail_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );

    if ( $thumbnail_id ) {
        $image = wp_get_attachment_thumb_url( $thumbnail_id );
    } else {
        $image = wc_placeholder_img_src();
    }
    ?>
    <tr class="form-field term-thumbnail-wrap">
        <th scope="row" valign="top"><label><?php esc_html_e( 'Thumbnail', 'woocommerce' ); ?></label></th>
        <td>
            <div id="appchar_filter_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
            <div style="line-height: 60px;">
                <input type="hidden" id="appchar_filter_thumbnail_id" name="appchar_filter_thumbnail_id" value="<?php echo esc_attr( $thumbnail_id ); ?>" />
                <button type="button" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'woocommerce' ); ?></button>
                <button type="button" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'woocommerce' ); ?></button>
            </div>
            <script type="text/javascript">

                // Only show the "remove image" button when needed
                if ( '0' === jQuery( '#appchar_filter_thumbnail_id' ).val() ) {
                    jQuery( '.remove_image_button' ).hide();
                }

                // Uploading files
                var file_frame;

                jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

                    event.preventDefault();

                    // If the media frame already exists, reopen it.
                    if ( file_frame ) {
                        file_frame.open();
                        return;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.downloadable_file = wp.media({
                        title: '<?php esc_html_e( 'Choose an image', 'woocommerce' ); ?>',
                        button: {
                            text: '<?php esc_html_e( 'Use image', 'woocommerce' ); ?>'
                        },
                        multiple: false
                    });

                    // When an image is selected, run a callback.
                    file_frame.on( 'select', function() {
                        var attachment           = file_frame.state().get( 'selection' ).first().toJSON();
                        var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                        jQuery( '#appchar_filter_thumbnail_id' ).val( attachment.id );
                        jQuery( '#appchar_filter_thumbnail' ).find( 'img' ).attr( 'src', attachment_thumbnail.url );
                        jQuery( '.remove_image_button' ).show();
                    });

                    // Finally, open the modal.
                    file_frame.open();
                });

                jQuery( document ).on( 'click', '.remove_image_button', function() {
                    jQuery( '#appchar_filter_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( wc_placeholder_img_src() ); ?>' );
                    jQuery( '#appchar_filter_thumbnail_id' ).val( '' );
                    jQuery( '.remove_image_button' ).hide();
                    return false;
                });

            </script>
            <div class="clear"></div>
        </td>
    </tr>
    <?php
}

