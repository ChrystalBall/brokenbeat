<?php
if(!class_exists('EC_JS')) :

require_once(ABSPATH . 'wp-includes/capabilities.php');
require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(EVENTSCALENDARCLASSPATH.'/ec_db.class.php');

class EC_JS {
  var $db;
  
  function EC_JS() {
    $this->db = new EC_DB();
  }
  
  function calendarData($m, $y) {
    global $current_user;
    $options = get_option('optionsEventsCalendar');
    $dayHasEventCSS= isset($options['dayHasEventCSS']) && !empty($options['dayHasEventCSS']) ? $options['dayHasEventCSS'] : 'color:red';
    $lastDay = date('t', mktime(0, 0, 0, $m, 1, $y));
    
    for($d = 1; $d <= $lastDay; $d++):
    $sqldate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
    $output = '';
    $output .= "<ul>";
    foreach($this->db->getDaysEvents($sqldate) as $e) :
      if(($e->accessLevel == 'public') || (current_user_can($e->accessLevel))) {
      $title = $e->eventTitle;
      $description = $e->eventDescription;
      $location = isset($e->eventLocation) ? $e->eventLocation : '';
      $startDate = $e->eventStartDate;
      $endDate = $e->eventEndDate;
      $startTime = isset($e->eventStartTime) ? $e->eventStartTime : '';
      $endTime = isset($e->eventEndTime) ? $e->eventEndTime : '';
      $output .= "<li><strong>$title</strong></li>";
    }
    endforeach;
    $output .= "</ul>";
    /*-- Added for localisation by Heirem ---------------- **/
    $clickdate = __('Click date for more details','events-calendar');
    if($output != '<ul></ul>'):
    $output .= "<span style=\"font-size:10px;font-weight:normal;\">$clickdate.</span>";
    /*---------------------------------------------------- **/
?>
    <script type="text/javascript">
      tb_pathToImage = "<?php echo get_option('siteurl');?>/wp-includes/js/thickbox/loadingAnimation.gif";
      tb_closeImage = "<?php echo get_option('siteurl');?>/wp-includes/js/thickbox/tb-close.png";
      jQuery(function($) {
        $(document).ready(function() {
          $('#events-calendar-<?php echo $d;?>').attr('title', '<?php echo $output;?>');
          $('#events-calendar-<?php echo $d;?>').attr('style', '<?php echo $dayHasEventCSS;?>');
          $('#events-calendar-<?php echo $d;?>').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#events-calendar-<?php echo $d;?>').click(function() {
            <!-- Added for localisation by Heirem ----- -->
            <!-- tb_show("<?php echo date('l, F j, Y', mktime(0,0,0,$m,$d,$y));?>", "<?php echo get_option('siteurl');?>?EC_view=day&EC_month=<?php echo $m;?>&EC_day=<?php echo $d;?>&EC_year=<?php echo $y;?>&TB_iframe=true&width=200&height=200", false); -->
            tb_show("<?php echo strftime('%A, %d %B, %Y', mktime(0,0,0,$m,$d,$y));?>", "<?php echo get_option('siteurl');?>?EC_view=day&EC_month=<?php echo $m;?>&EC_day=<?php echo $d;?>&EC_year=<?php echo $y;?>&TB_iframe=true&width=200&height=200", false);
            <!------------------------------------------ -->
          });
          $('#events-calendar-<?php echo $d;?>').Tooltip({
            delay:0,
            track:true
          });
        });
      });
    </script>
<?php
    endif;
    endfor;
?>
    <script type="text/javascript">
      jQuery(function($) {
        $(document).ready(function() {
          /*-- Added for localisation by Heirem ---------------- */
          $('#EC_previousMonth').append("&laquo;<?php echo strftime('%b', mktime(0, 0, 0, $m-1, 1, $y));?>");
          // $('#EC_previousMonth').append("&laquo;<?php echo date('M', mktime(0, 0, 0, $m-1, 1, $y));?>");
          $('#EC_nextMonth').prepend("<?php echo strftime('%b', mktime(0, 0, 0, $m+1, 1, $y));?>&raquo;");
          // $('#EC_nextMonth').prepend("<?php echo date('M', mktime(0, 0, 0, $m+1, 1, $y));?>&raquo;");
          /*---------------------------------------------------- */
          $('#EC_previousMonth').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#EC_nextMonth').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#EC_previousMonth').click(function() {
            $('#EC_loadingPane').append("<img src=\"<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>\" style=\"width:50px;\" />");
            $.get("<?php echo get_option('siteurl');?>",
            {EC_action: "switchMonth", EC_month: <?php echo $m-1;?>, EC_year: <?php echo $y;?>},
            function(data) {
              $('#calendar_wrap').empty();
              //$('#calendar_wrap').append(data);
              $('#calendar_wrap').append($(data).html());
            });
          });
          $('#EC_nextMonth').click(function() {
            $('#EC_loadingPane').append("<img src=\"<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>\" style=\"width:50px;\" />");
            $.get("<?php echo get_option('siteurl');?>",
            {EC_action: "switchMonth", EC_month: <?php echo $m+1;?>, EC_year: <?php echo $y;?>},
            function(data) {
              $('#calendar_wrap').empty();
              //$('#calendar_wrap').append(data);
              $('#calendar_wrap').append($(data).html());
            });
          });
          $.preloadImages = function() {
            for(var i = 0; i<arguments.length; i++){
              jQuery("<img>").attr("src", arguments[i]);
            }
          }
          $.preloadImages("<?php echo EVENTSCALENDARIMAGESURL . '/loading.gif';?>");
        });
      });
    </script>
<?php
  }
  
  function calendarDataLarge($m, $y) {
    global $current_user;
    $options = get_option('optionsEventsCalendar');
    $lastDay = date('t', mktime(0, 0, 0, $m, 1, $y));
    for($d = 1; $d <= $lastDay; $d++):
    $sqldate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
    foreach($this->db->getDaysEvents($sqldate) as $e) :
    // Change:  Output has to be after foreach and before the if statement.
    $output = '';
    if(($e->accessLevel == 'public') || (current_user_can($e->accessLevel))) {
      $output = '';
      $id = "$d-$e->id";
      $title = $e->eventTitle;
      $description = preg_replace('#\r?\n#', '<br />', $e->eventDescription);
      $location = isset($e->eventLocation) && !empty($e->eventLocation) ? $e->eventLocation : '';
      list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $e->eventStartDate);
           // Change: $event->eventStartTime below changed for times to show in large calendar
          if(!is_null($e->eventStartTime) && !empty($e->eventStartTime)) {
          list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $e->eventStartTime);
          $startTime = date($options['timeFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        }
        else
          $startTime = null;
        $startDate = date($options['dateFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        list($ec_endyear, $ec_endmonth, $ec_endday) = split("-", $e->eventEndDate);
        // Change: $event->eventEndTime below changed for times to show in large calendar
        if($e->eventEndTime != null && !empty($e->eventEndTime)) {
          list($ec_endhour, $ec_endminute, $ec_endsecond) = split(":", $e->eventEndTime);
          $endTime = date($options['timeFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
        }
        else
          $endTime = null;
        $endDate = date($options['dateFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
      $accessLevel = $e->accessLevel;
      if(!empty($title) && !is_null($title))
      $output .= "<strong>Title: </strong>$title<br />";
      if(!empty($location) && !is_null($location))
        $output .= "<strong>Location: </strong>$location<br />";
      if(!empty($description) && !is_null($description))
        $output .= "<strong>Description: </strong>$description<br />";
      if($startDate != $endDate)
        $output .= "<strong>Start Date: </strong>$startDate<br />";
      if(!empty($startTime) || !is_null($startTime))
        $output .= "<strong>Start Time: </strong>$startTime<br />";
      if($startDate != $endDate)
        $output .= "<strong>End Date: </strong>$endDate<br />";
      if(!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime))
        $output .= "<strong>End Time: </strong>$endTime<br />";
    }
        if($output != ''):
?>
    <script type="text/javascript">
      jQuery(function($) {
        $(document).ready(function() {
          $('#events-calendar-<?php echo $d;?>Large').append("<span id=\"events-calendar-<?php echo $id;?>Large\"><?php echo $title;?></span><br />");
          $('#events-calendar-<?php echo $id;?>Large').attr('title', '<?php echo $output;?>');
          $('#events-calendar-<?php echo $id;?>Large').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#events-calendar-<?php echo $id;?>Large').Tooltip({
            delay:0,
            track:true
          });
        });
      });
    </script>
<?php
    endif;
    endforeach;
    endfor;
?>
    <script type="text/javascript">
      jQuery(function($) {
        $(document).ready(function() {
          /*-- Added for localisation by Heirem ---------------- */
          $('#EC_previousMonthLarge').append("&laquo;<?php echo strftime('%B', mktime(0, 0, 0, $m-1, 1, $y));?>");
          // $('#EC_previousMonthLarge').append("&laquo;<?php echo date('M', mktime(0, 0, 0, $m-1, 1, $y));?>");
          $('#EC_nextMonthLarge').prepend("<?php echo strftime('%B', mktime(0, 0, 0, $m+1, 1, $y));?>&raquo;");
          //$('#EC_nextMonthLarge').prepend("<?php echo date('M', mktime(0, 0, 0, $m+1, 1, $y));?>&raquo;");
          /*---------------------------------------------------- */
          $('#EC_previousMonthLarge').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#EC_nextMonthLarge').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#EC_previousMonthLarge').click(function() {
            $.get("<?php echo get_option('siteurl');?>",
            {EC_action: "switchMonthLarge", EC_month: <?php echo $m-1;?>, EC_year: <?php echo $y;?>},
            function(data) {
              $('#calendar_wrapLarge').empty();
              //$('#calendar_wrapLarge').append(data);
              $('#calendar_wrapLarge').append($(data).html());
            });
          });
          $('#EC_nextMonthLarge').click(function() {
            $.get("<?php echo get_option('siteurl');?>",
            {EC_action: "switchMonthLarge", EC_month: <?php echo $m+1;?>, EC_year: <?php echo $y;?>},
            function(data) {
              $('#calendar_wrapLarge').empty();
              //$('#calendar_wrapLarge').append(data);
              $('#calendar_wrapLarge').append($(data).html());
            });
          });
        });
      });
    </script>
<?php
  }

  function listData($events) {
    global $current_user;
    $options = get_option('optionsEventsCalendar');
    $format = $options['dateFormatLarge'];
    foreach($events as $e):
    $output = '';
    if($e->accessLevel == 'public' || $current_user->has_cap($e->accessLevel)) {
      $id = "$e->id";
      $title = $e->eventTitle;
      $description = preg_replace('#\r?\n#', '<br />', $e->eventDescription);
      $location = isset($e->eventLocation) && !empty($e->eventLocation) ? $e->eventLocation : '';
      list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $e->eventStartDate);
        if(!is_null($e->eventStartTime) && !empty($e->eventStartTime)) {
          list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $e->eventStartTime);
          $startTime = date($options['timeFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        }
        else
          $startTime = null;
        $startDate = date($options['dateFormatLarge'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
        list($ec_endyear, $ec_endmonth, $ec_endday) = split("-", $e->eventEndDate);
        if($e->eventEndTime != null && !empty($e->eventEndTime)) {
          list($ec_endhour, $ec_endminute, $ec_endsecond) = split(":", $e->eventEndTime);
          $endTime = date($options['timeFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
        }
        else
          $endTime = null;
        $endDate = date($options['dateFormatLarge'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
      $accessLevel = $e->accessLevel;
      $output .= "<strong>Title: </strong>$title<br />";
      if(!empty($location) && !is_null($location))
        $output .= "<strong>Location: </strong>$location<br />";
      if(!empty($description) && !is_null($description))
        $output .= "<strong>Description: </strong>$description<br />";
      if($startDate != $endDate )
        $output .= "<strong>Start Date: </strong>$startDate<br />";
      if(!empty($startTime) || !is_null($startTime))
        $output .= "<strong>Start Time: </strong>$startTime<br />";
      if($startDate != $endDate)
        $output .= "<strong>End Date: </strong>$endDate<br />";
      if(!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime))
        $output .= "<strong>End Time: </strong>$endTime<br />";
    }
    if($output != ''):
?>
    <script type="text/javascript">
      jQuery(function($) {
        $(document).ready(function() {
          $('#events-calendar-list-<?php echo $id;?>').attr('title', '<?php echo $output;?>');
          $('#events-calendar-list-<?php echo $id;?>').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#events-calendar-list-<?php echo $e->id;?>').Tooltip({
            delay:0,
            track:true
          });          
        });
      });
    </script>
<?php
    endif;
    endforeach;
  }
}
endif;
?>
