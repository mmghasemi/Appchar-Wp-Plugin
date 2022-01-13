<?php
if (isset($_POST['save_setting'])) {
    if(isset($_POST['selected-category'])){
        $selected_categories = array();
        $selected_categories['error_message'] = htmlspecialchars($_POST['error_message']);
        foreach ($_POST['selected-category'] as $key=>$value) {
            if ($value != -1 && $_POST['from_time'][$key] < $_POST['to_time'][$key]){
                $selected_categories[$value] = array(
                    'from' => $_POST['from_time'][$key],
                    'to' => $_POST['to_time'][$key],
                );
            }
        }
        update_option('appchar_categories_status',$selected_categories);
    }
}



$args = array(
    'taxonomy'     => 'product_cat',
    'orderby'      => 'name',
    'show_count'   => 0,
    'pad_counts'   => 0,
    'hierarchical' => 1,
    'title_li'     => '',
    'hide_empty'   => 0
);
$all_categories = get_categories( $args );
?>


<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">
                            <table class="form-table">
                                <tbody>
                                <tr>
                                    <th>
                                        با اضافه کردن بلاک دسته بندی ها برای هر کدام برنامه ریزی کنید
                                    </th>
                                    <td>
                                        <table>
                                            <tbody id="categories-tbody">
                                            <tr>
                                                <th>انتخاب دسته:</th>
                                                <th>از ساعت:</th>
                                                <th>تا ساعت:</th>
                                                <th>#</th>
                                            </tr>
                                            <?php
                                            $categories_status=get_option('appchar_categories_status',false);
                                            if($categories_status):
                                                foreach ($categories_status as $key=>$category_status):
                                            ?>
                                                    <tr>
                                                        <td>
                                                            <select name="selected-category[]">
                                                                <option value="-1">--select--</option>
                                                                <?php
                                                                foreach ($all_categories as $category){
                                                                    if($category->term_id==$key){
                                                                        echo "<option value='$category->term_id' selected>$category->name</option>";
                                                                    }else{
                                                                        echo "<option value='$category->term_id'>$category->name</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="from_time[]">
                                                                <?php
                                                                for($i=0;$i<=23;$i++){
                                                                    if($category_status['from']==$i){
                                                                        echo "<option value='$i' selected>$i</option>";
                                                                    }else {
                                                                        echo "<option value='$i'>$i</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="to_time[]">
                                                                <?php
                                                                for($i=1;$i<=24;$i++){
                                                                    if($category_status['to']==$i){
                                                                        echo "<option value='$i' selected>$i</option>";
                                                                    }else {
                                                                        echo "<option value='$i'>$i</option>";
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <a onclick="delete_row(this)"><span class="dashicons dashicons-dismiss"></span></a>
                                                        </td>
                                                    </tr>
                                            <?php
                                                endforeach;
                                            endif;
                                            ?>
                                                <tr class="category-row">
                                                    <td>
                                                        <select name="selected-category[]">
                                                            <option value="-1">--select--</option>
                                                            <?php
                                                            foreach ($all_categories as $category){
                                                                echo "<option value='$category->term_id'>$category->name</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="from_time[]">
                                                            <?php
                                                            for($i=0;$i<=23;$i++){
                                                                echo "<option value='$i'>$i</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="to_time[]">
                                                            <?php
                                                            for($i=1;$i<=24;$i++){
                                                                echo "<option value='$i'>$i</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div style="text-align:center;background-color:white;">
                                            <a onclick="row_append()">
                                                <span class="dashicons dashicons-plus-alt"></span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>پیغام خطا زمان جلوگیری از اضافه شدن محصول به سبد</th>
                                    <?php
                                    if(isset($categories_status['error_message'])) {
                                        $error_message = $categories_status['error_message'];
                                    }else {

                                        $error_message = __('متاسفانه محصول {product_name} به سبد اضافه نشد.','appchar');
                                    }
                                    ?>
                                    <td><textarea name="error_message" rows="5" cols="55"><?php echo $error_message ?></textarea></td>
                                </tr>
                                </tbody>
                            </table>
                            <?php submit_button('', '', 'save_setting'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
