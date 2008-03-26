<?php
/*
This is the sidebar module for the Events Calendar when using K2 as your theme.
You will need to upload this file to the module directory for the K2 theme.  The
directory is as follows:  wp-content/themes/k2/app/modules

This will enable you to use the Events Calendar as a sidebar module.
---------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
---------------------------------------------------------------------
*/
define('EVENTSCALENDARPATH', ABSPATH."wp-content/plugins/events-calendar/");
if (function_exists('load_plugin_textdomain')) {
  load_plugin_textdomain(EVENTSCALENDARPATH, "languages/");
}

function events_calendar_sidebar_module($args)
{
  global $firstDay, $lastDay;
  $eDate = explode("-", $firstDay);
  $mmonth = $eDate[1];
  $yyear = $eDate[0];
  $display = new Display_071181($firstDay, $lastDay, $mmonth, $yyear);
  
  extract($args);  
  $options = get_option('widgetEventsCalendar');
  $title = $options['title'];
  if ( empty($title) )
    $title = '&nbsp;';
  echo $before_module . $before_title . $title . $after_title;
  if ( $options['type'] == 'calendar' ) {
    echo '<div id="calendar_wrap" style="text-align:right;width:100%;">';
    $display->getEventList();
    $display->getCalendar();
    echo '</div>';
  } elseif ( 'list' == $options['type'] ) {
    $display->printEventList('widget', $options['listcount']);
  }
  echo $after_module;
}

function events_calendar_sidebar_module_control()
{
  global $firstDay, $lastDay;
  $eDate = explode("-", $firstDay);
  $mmonth = $eDate[1];
  $yyear = $eDate[0];
  
  $management = new Management_071181($firstDay, $lastDay, $mmonth, $yyear);
  
  $options = get_option('widgetEventsCalendar');
  if ( !is_array($options) ){
    $options = array();
    $options['title'] = 'Events Calendar';
    $options['dayOfWeekLength'] = '3';
    $options['accessLevel'] = 'level_10';
  }
  if ( $_POST['eventscalendar']['submit'] ) {
    unset($_POST['eventscalendar']['submit']);
    foreach ( $_POST['eventscalendar'] as $key => $option ) {
      $options[$key] = strip_tags(stripslashes($option));
    }
    update_option('widgetEventsCalendar', $options);
  }

  $title = htmlspecialchars($options['title'], ENT_QUOTES);
  $dayOfWeekLength = htmlspecialchars($options['dayOfWeekLength'], ENT_QUOTES);

  $one = 'no';
  $two = 'no';
  $three = 'no';
  $four = 'no';

  if($options['dayOfWeekLength']=='1')
    $one = "checked=\"yes\"";
  if($options['dayOfWeekLength']=='2')
    $two = "checked=\"yes\"";
  if($options['dayOfWeekLength']=='3')
    $three = "checked=\"yes\"";
  if($options['dayOfWeekLength']=='4')
    $four = "checked=\"yes\"";
  echo '<p style="text-align:center;"><label for="eventscalendar-title">' . __("Title","events-calendar") . ': <input style="width: 200px;" id="eventscalendar-title" name="eventscalendar[title]" type="text" value="'.$title.'" /></label></p>';
  echo '<p style="text-align:center;"><label for="eventscalendar-dayOfWeekLength">' . __("Day of week style","events-calendar") . ': <input id="eventscalendar-dayOfWeekLength1" name="eventscalendar[dayOfWeekLength]" type="radio" value="1" '.$one.' />' . __("S","events-calendar") . ' <input id="eventscalendar-dayOfWeekLength2" name="eventscalendar[dayOfWeekLength]" type="radio" value="2" '.$two.' />' . __("Su","events-calendar") . ' <input id="eventscalendar-dayOfWeekLength3" name="eventscalendar[dayOfWeekLength]" type="radio" value="3" '.$three.' />' . __("Sun","events-calendar") . ' <input id="eventscalendar-dayOfWeekLength4" name="eventscalendar[dayOfWeekLength]" type="radio" value="4" '.$four.' />' . __("Sunday","events-calendar") . ' </label></p>';
  ?>
    <p style="text-align:center;">
      <label for="eventscalendar-type">
        <?php printf(__("Calendar Type","events-calendar")) ?>: 
        <select name="eventscalendar[type]" id="eventscalendar-type">
          <option value="calendar"><?php printf(__("Calendar","events-calendar")) ?></option>
          <option value="list" <?php if ( isset($options['type']) && 'list' == $options['type'] ) echo 'selected="selected"'; ?>><?php printf(__("Event List","events-calendar")) ?></option>
        </select>
      </label>
    </p>
    <div id="eventscalendar-EventListOptions" style="<?php if ( !isset($options['type']) || 'list' != $options['type'] ) echo 'display: none;'; ?>">
      <p>
        <span style="font-weight: bold"><?php printf(__("Event List options","events-calendar")) ?></span>
      </p>
      <p>
        <label for="eventscalendar-listCount">
          <?php printf(__("Number of events","events-calendar")) ?>: 
          <input style="width: 30px;" type="text" id="eventscalendar-listCount" name="eventscalendar[listCount]" value="<?php echo ( isset($options['listCount']) && !empty($options['listCount']) ) ? $options['listCount'] : '5'; ?>" />
        </label>
      </p>
    </div>
    <p style="text-align:center;">
      <label for="eventscalendar-accessLevel">
        <?php printf(__("Minimum User Level","events-calendar")) ?>: 
        <select name="eventscalendar[accessLevel]" id="eventscalendar-accessLevel">
          <option value="level_10"><?php printf(__("Administrator","events-calendar")) ?></option>
          <option value="level_7" <?php if ( isset($options['accessLevel']) && 'level_7' == $options['accessLevel'] ) echo 'selected="selected"'; ?>><?php printf(__("Editor","events-calendar")) ?></option>
          <option value="level_2" <?php if ( isset($options['accessLevel']) && 'level_2' == $options['accessLevel'] ) echo 'selected="selected"'; ?>><?php printf(__("Author","events-calendar")) ?></option>
          <option value="level_1" <?php if ( isset($options['accessLevel']) && 'level_1' == $options['accessLevel'] ) echo 'selected="selected"'; ?>><?php printf(__("Contributor","events-calendar")) ?></option>
          <option value="level_0" <?php if ( isset($options['accessLevel']) && 'level_0' == $options['accessLevel'] ) echo 'selected="selected"'; ?>><?php printf(__("Subscriber","events-calendar")) ?></option>
        </select>
      </label>
    </p>
  <?php
  echo '<p><span style="font-weight: bold">' . __("Formatting Options","events-calendar") . '</span><br /> (same as PHP <a href="http://us.php.net/manual/en/function.date.php" target="_blank">date function</a>)</p>';
  echo '<p style="text-align:center;"><label for="eventscalendar-dateFormat">' . __("Date Format","events-calendar") . ': <input id="eventscalendar-dateFormat" name="eventscalendar[dateFormat]" type="text" value="' . (isset($options['dateFormat']) && !empty($options['dateFormat']) ? $options['dateFormat'] : 'Y-m-d' ) . '" /> </label></p>';
  echo '<p style="text-align:center;"><label for="eventscalendar-timeFormat">' . __("Time Format","events-calendar") . ': <input id="eventscalendar-timeFormat" name="eventscalendar[timeFormat]" type="text" value="' . (isset($options['timeFormat']) && !empty($options['timeFormat']) ? $options['timeFormat'] : 'h:i:s a' ) . '" /> </label></p>';
  echo '<input type="hidden" id="eventscalendar-submit" name="eventscalendar[submit]" value="1" />';
  ?>
    <script type="text/javascript">
      jQuery("select#eventscalendar-type").change(function(){
        if ( "list" == this.value ) {
          jQuery("#eventscalendar-EventListOptions").show();
        } else {
          jQuery("#eventscalendar-EventListOptions").hide();
        }
      });
    </script>
  <?php
}

register_sidebar_module(__('Events Calendar','events-calendar'), 'events_calendar_sidebar_module', 'sb-pagemenu', array('custom_title' => __('%s Subpages', 'k2_domain') ));
register_sidebar_module_control(__('Events Calendar','events-calendar'), 'events_calendar_sidebar_module_control');
?>