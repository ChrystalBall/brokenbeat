<?php
if(!class_exists('EC_ManagementJS')) :

require_once(EVENTSCALENDARCLASSPATH.'/ec_db.class.php');

class EC_ManagementJS {
  var $db;
  
  function EC_ManagementJS() {
    $this->db = new EC_DB();
  }
  
  function calendarData($m, $y) {
    $options = get_option('optionsEventsCalendar');
    $lastDay = date('t', mktime(0, 0, 0, $m, 1, $y));
    for($d = 1; $d <= $lastDay; $d++):
    $sqldate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
    foreach($this->db->getDaysEvents($sqldate) as $e) :
    $output = '';
    $id = "$d-$e->id";
    $title = $e->eventTitle;
    $description = preg_replace('#\r?\n#', '<br />', $e->eventDescription);
    $location = isset($e->eventLocation) ? $e->eventLocation : '';
    $startDate = $e->eventStartDate;
    $endDate = $e->eventEndDate;
    $startTime = isset($e->eventStartTime) ? $e->eventStartTime : '';
    $endTime = isset($e->eventEndTime) ? $e->eventEndTime : '';
    $accessLevel = $e->accessLevel;
    $postID = $e->postID;
    /* $output .= "<strong>Title: </strong>$title<br />";
    $output .= "<strong>Location: </strong>$location<br />";
    $output .= "<strong>Description: </strong>$description<br />";
    $output .= "<strong>Start Date: </strong>$startDate<br />";
    $output .= "<strong>Start Time: </strong>$startTime<br />";
    $output .= "<strong>End Date: </strong>$endDate<br />";
    $output .= "<strong>End Time: </strong>$endTime<br />";
    $output .= "<strong>Visibility Level: </strong>$accessLevel<br /><br />"; */
    /*-- Added for localisation by Heirem ----------------*/
    $caption = __('Title','events-calendar');
    $output .= "<strong>$caption: </strong>$title<br />";
    $caption = __('Location','events-calendar');
    $output .= "<strong>$caption: </strong>$location<br />";
    $caption = __('Description','events-calendar');
    $output .= "<strong>$caption: </strong>$description<br />";
    $caption = __('Start Date','events-calendar');
    $output .= "<strong>$caption: </strong>$startDate<br />";
    $caption = __('Start Time','events-calendar');
    $output .= "<strong>$caption: </strong>$startTime<br />";
    $caption = __('End Date','events-calendar');
    $output .= "<strong>$caption: </strong>$endDate<br />";
    $caption = __('End Time','events-calendar');
    $output .= "<strong>$caption: </strong>$endTime<br />";
    $caption = __('Visibility','events-calendar');
    $output .= "<strong>$caption: </strong>$accessLevel<br />";
    /*-----------------------------------------------------*/
    if($output != ''):
?>
    <script type="text/javascript">
      jQuery(function($) {
        $(document).ready(function() {
          $('#events-calendar-<?php echo $d;?>').append("<div id=\"events-calendar-container-<?php echo $id;?>\"><span id=\"events-calendar-<?php echo $id;?>\"><?php echo $title;?></span><img id=\"events-calendar-delete-<?php echo $id;?>\" src=\"<?php echo EVENTSCALENDARIMAGESURL;?>/delete.gif\" style=\"width:12px;height:12px;\" /><\div>");
          $('#events-calendar-<?php echo $id;?>').attr('title', '<?php echo $output;?>');
          $('#events-calendar-<?php echo $id;?>').css('color', 'black');
          $('#events-calendar-<?php echo $id;?>').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#events-calendar-delete-<?php echo $id;?>').mouseover(function() {
            $(this).css('cursor', 'pointer');
          });
          $('#events-calendar-<?php echo $id;?>').click(function() {
            top.location = "?page=events-calendar&EC_action=edit&EC_id=<?php echo $e->id;?>";
          });
          $('#events-calendar-<?php echo $id;?>').Tooltip({
            delay:0,
            track:true
          });
          $('#events-calendar-delete-<?php echo $id;?>').click(function() {
          <!-- Added for localisation by Heirem -->
            doDelete = confirm("<?php _e('Are you sure you want to delete the following event:\n','events-calendar');echo $e->eventTitle;?>");
          <!-- -------------------------------- -->
            if(doDelete) {
              $.get("<?php echo get_option('siteurl');?>/wp-admin/admin.php?page=events-calendar",
              {EC_action: "ajaxDelete", EC_id: <?php echo $e->id;?>},
              function(data) {
                for(d = 1; d <= <?php echo $lastDay;?>; d++) {
                  $('#events-calendar-container-' + d + '-<?php echo $e->id;?>').css('background', 'red');
                  $('#events-calendar-container-' + d + '-<?php echo $e->id;?>').fadeOut(1000);
                }
              });
            }
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
          $('#EC_startDate').datepicker({dateFormat: 'yy-mm-dd'});
          $('#EC_endDate').datepicker({dateFormat: 'yy-mm-dd'});
<?php
          $timeStep = (isset($options['timeStep']) && !empty($options['timeStep'])) ? $options['timeStep'] : 30;
?>
          $("#EC_startTime").timePicker({step: <?php echo $timeStep;?>});
          $("#EC_endTime").timePicker({step: <?php echo $timeStep;?>});
        });
      });
    </script>
<?php
  }
}
endif;
?>
