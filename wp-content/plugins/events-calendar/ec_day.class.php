<?php
if(!class_exists('EC_Day')):
require_once('ec_db.class.php');

class EC_Day {
  var $db;
  
  function EC_Day() {
    $this->db = new EC_DB();
  }
  
  function display($d) {
    $options = get_option('optionsEventsCalendar');
    $events = $this->db->getDaysEvents($d);
    list($ec_year, $ec_month, $ec_day) = split('-', $d);
?>
<style type="text/css">
/*
 * Days events calendar
 */
#EC_daysEvents {
  font-size: 14px;
}
.EC_title {
  background: #A4CAE6;
}
.EC_location {
  background: #FFF8DC;
}
.EC_time {
  background: #CCCCCC;
}
.EC_date {
  background: #E0EEEE;
  text-align: center;
}
</style>
      <div id="EC_daysEvents">
<?php
    foreach($events as $event) {
    if(($event->accessLevel == 'public') || (current_user_can($event->accessLevel))) :
      $title = stripslashes($event->eventTitle);
      $description = preg_replace('#\r?\n#', '<br />', $event->eventDescription);
      $description = stripslashes($description);
      $location = stripslashes($event->eventLocation);
      list($ec_startyear, $ec_startmonth, $ec_startday) = explode("-", $event->eventStartDate);
      if(!is_null($event->eventStartTime) && !empty($event->eventStartTime)) {
        list($ec_starthour, $ec_startminute, $ec_startsecond) = explode(":", $event->eventStartTime);
        $startTime = date($options['timeFormatWidget'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
      }
      else 
        $startTime = null;
      $startDate = date($options['dateFormatWidget'], mktime($ec_starthour, $ec_startminute, $ec_startsecond, $ec_startmonth, $ec_startday, $ec_startyear));
      list($ec_endyear, $ec_endmonth, $ec_endday) = split("-", $event->eventEndDate);
      if($event->eventEndTime != null && !empty($event->eventEndTime)) {
        list($ec_endhour, $ec_endminute, $ec_endsecond) = split(":", $event->eventEndTime);
        $endTime = date($options['timeFormatWidget'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
      }
      else
        $endTime = null;
      $endDate = date($options['dateFormatWidget'], mktime($ec_endhour, $ec_endminute, $ec_endsecond, $ec_endmonth, $ec_endday, $ec_endyear));
?>
      <p>
      <div for="EC_title" class="EC_title"><strong><?php echo $title;?></strong></div>
<?php
      if(!empty($location) || !is_null($location)):
?>
      <!-- Added for localisation by Heirem ---------------- -->
      <div for="EC_location" class="EC_location"><strong><?php _e('Location','events-calendar'); ?>:</strong> <?php echo $location;?></div>
      <!-- ------------------------------------------------- -->
<?php
      endif;

      if(!empty($startTime) || !is_null($startTime)):
?>
      <div for="EC_time" class="EC_time"><strong><?php echo $startTime;?></strong>
<?php
      endif;

      if(!empty($endTime) && !empty($startTime) || !is_null($endTime) && !is_null($startTime)):
      /* Added for localisation by Heirem --------------------*/
?>
       <?php _e('to','events-calendar');?> <strong><?php echo $endTime;?></strong>
<?php
      /* -----------------------------------------------------*/
      endif;

      if(!empty($startTime) || !is_null($startTime)):
?>
      </div>
<?php
      endif;
?>
      <div for="EC_description" class="EC_description"><?php echo $description;?></div>
<?php
      if($event->eventStartDate != $event->eventEndDate ):
?>
      <div for="EC_date" class="EC_date"><?php echo $startDate;?> :: <?php echo $endDate;?></div>
<?php
      endif;
?>
      </p><p />
<?php
    endif;
    }
?>
      </div>
<?php
  }
}
endif;
?>
