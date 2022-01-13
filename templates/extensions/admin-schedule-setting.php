<?php
if(isset($_POST['save_extension_setting'])){
    if(isset($_POST['date'])) {
        foreach ($_POST['date'] as $date) {
            $weektime = explode(" ", $date);
            $week_num[$weektime[0]][] = $weektime[1];
        }
        update_option('appchar_schedule_time', $week_num);
        update_option('appchar_schedule_error_msg',$_POST['schedule-error-msg']);
    }
}
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
            <div>
                <h3 class="title"><?php _e('home page setting', 'appchar') ?></h3>
                <div class="inside">
                    <div id="settings">
                        <form id="taskSchedule" method="post">
                            <table class="form-table">
                                <tbody>
                                <?php
                                if(AppcharExtension::extensionIsActive('schedule')) {
                                    ?>

                                    <tr>

                                        <th>
                                            <?php _e('schedule your worktime', 'appchar'); ?>
                                        </th>
                                        <td>
                                            <div id="day-schedule"></div>
                                            <script>
                                                (function ($) {
                                                    $("#day-schedule").dayScheduleSelector({
                                                        /*
                                                         days: [1, 2, 3, 5, 6],
                                                         interval: 15,
                                                         startTime: '09:50',
                                                         endTime: '21:06'
                                                         */
                                                    });
                                                    $("#day-schedule").on('selected.artsy.dayScheduleSelector', function (e, selected) {
                                                        console.log(selected);
                                                    })
                                                    $("#day-schedule").data('artsy.dayScheduleSelector').deserialize({
                                                        <?php
                                                        $showtime = get_option('appchar_schedule_time','');
                                                        if($showtime != ''){
                                                            foreach ($showtime as $key=>$value){
                                                                $item = '';
                                                                foreach ($value as $items){
                                                                    $items = explode('-',$items);
                                                                    $item = $item . "['" . $items[0] . "', '" . $items[1] . "'],";
                                                                }
                                                                echo "'".$key."': [". $item .'],';
                                                            }
                                                        }else{
                                                        ?>
                                                        '0': [['06:00', '08:00'], ['10:00', '12:00'], ['14:00', '20:00']],
                                                        '1': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        '2': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        '3': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        '4': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        '5': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        '6': [['08:00', '12:00'], ['14:00', '20:00']],
                                                        <?php
                                                        }
                                                        ?>
                                                    });
                                                    $( "form#taskSchedule" ).submit(function( event ) {
                                                        var _form = $(this);
                                                        $( "#day-schedule td[data-selected^='selected']" ).each( function( index, element ){
                                                            var custom_date = $( this ).attr('data-day')+" "+$( this ).attr('data-time');
                                                            _form.append("<input type='hidden' name='date[]' value='"+custom_date+"' />");
                                                        });

                                                    });
                                                })(jquery1_11_3);
                                            </script>
                                            <script type="text/javascript">

                                                var _gaq = _gaq || [];
                                                _gaq.push(['_setAccount', 'UA-36251023-1']);
                                                _gaq.push(['_setDomainName', 'jqueryscript.net']);
                                                _gaq.push(['_trackPageview']);

                                                (function() {
                                                    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                                                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                                                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                                                })();

                                            </script>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?php _e('Enter a message for the closed shop','appchar') ?></th>
                                        <td><input type="text" name="schedule-error-msg" value="<?php echo get_option('appchar_schedule_error_msg',''); ?>"></td>
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

