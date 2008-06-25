<?php
if(!class_exists("EC_Calendar")) :
require_once(EVENTSCALENDARCLASSPATH . '/ec_db.class.php');

class EC_Calendar {

  /**
   * Displays the Event List Widget
   */
  function displayEventList($num) {
    global $current_user;
    $db = new EC_DB();
    $js = new EC_JS();
    $options = get_option('optionsEventsCalendar');
    $format = $options['dateFormatLarge'];
    $events = $db->getUpcomingEvents($num);
    echo "<ul>";
    foreach($events as $event) {
      if($event->accessLevel == 'public' || $current_user->has_cap($event->accessLevel)) {
        $splitDate = explode("-", $event->eventStartDate);
        $month = $splitDate[1];
        $day = $splitDate[2];
        $year = $splitDate[0];
        $startDate = date("$format", mktime(0, 0, 0, $month, $day, $year));
        //$startDate = $startDate < date("$format") ? date("$format") : $startDate;
        echo "<li id=\"events-calendar-list-$event->id\">$startDate: " . stripslashes($event->eventTitle) . "</li>";
      }
    }
    echo "</ul>";
    $js->listData($events);
  }
    

  /**
   * Displays the Widget Calendar
   */
	 function displayWidget($year, $month, $days = array(), $day_name_length = 2) {
    $ecFile = get_bloginfo('stylesheet_directory') . "/style.css";
    // The following two lines are to get the length of day names - Ron
    $options = get_option('optionsEventsCalendar');
    $day_name_length = $options['daynamelength'];
    $ecData = file_get_contents($ecFile);
    if(strpos($ecData, "#today") === false) {
      $todaySet = false;
    } else {
      $todaySet = true;
    }
    $js = new EC_JS();
    $first_day = get_option('start_of_week');
    $first_of_month = gmmktime(0,0,0,$month,1,$year);

	  $day_names = array();
	  for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) //January 4, 1970 was a Sunday
		  $day_names[$n] = ucfirst(gmstrftime('%A',$t)); //%A means full textual day name

	  list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	  $weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
	  $title   = htmlentities(ucfirst($month_name))."&nbsp;".$year;

    $calendar = "<div id=\"calendar_wrap\"><table id=\"wp-calendar\">"."\n\t"."<caption id=\"calendar_month\" class=\"calendar-month\">".$title."</caption>\n\t<thead>\n\t<tr>\n";

	  if($day_name_length){ //if the day names should be shown ($day_name_length > 0)
		  //if day_name_length is >3, the full name of the day will be printed
		  foreach($day_names as $d)
			  $calendar .= "\t\t<th abbr=\"".htmlentities($d)."\" scope=\"col\" title=\"".htmlentities($d)."\">".htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d)."</th>\n";
		  $calendar .= "\t</tr>\n\t</thead>\n\t<tfoot>\n\t<tr>\n\t\t<td class=\"pad\" style=\"text-align:left\" colspan=\"2\">&nbsp;<span id=\"EC_previousMonth\"></span></td>\n\t\t<td class=\"pad\" colspan=\"3\" id=\"EC_loadingPane\" style=\"text-align:center;\"></td>\n\t\t<td class=\"pad\" style=\"text-align:right;\" colspan=\"2\"><span id=\"EC_nextMonth\"></span>&nbsp;</td>\n\t</tr>\n\t</tfoot>\n\t<tbody>\n\t<tr>\n";
	  }

	  if($weekday > 0) $calendar .= "\t\t<td colspan=\"".$weekday."\" class=\"pad\">&nbsp;</td>"; //initial \"empty\" days
	  for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		  if($weekday == 7){
			  $weekday   = 0; //start a new week
			  $calendar .= "\n\t</tr>\n\t<tr>\n\t\t";
		  }
		  if(!$todaySet) {
        $dayID = ("$month/$day/$year" == date('m/j/Y')) ? " id=\"today\" style=\"border:thin solid red;\"" : "";
		  } else {
        $dayID = ("$month/$day/$year" == date('m/j/Y')) ? " id=\"today\"" : "";
      }
		  $calendar .= "<td".$dayID."><span id=\"events-calendar-$day\">$day</span></td>";
	  }
	  if($weekday != 7) $calendar .= "\n\t\t<td colspan=\"".(7-$weekday)."\" class=\"pad\">&nbsp;</td>"; //remaining "empty" days

	  echo $calendar."\t</tr>\n\t</tbody>\n\t</table>";
    $js->calendarData($month, $year);
	  echo "</div>";
  }

  /**
   * Displays the Large Calendar
   */
  function displayLarge($year, $month, $days = array(), $day_name_length = 7) {
    $js = new EC_JS();
    $first_day = get_option('start_of_week');
    $first_of_month = gmmktime(0,0,0,$month,1,$year);

	  $day_names = array();
	  for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) //January 4, 1970 was a Sunday
		  $day_names[$n] = ucfirst(gmstrftime('%A',$t)); //%A means full textual day name

	  list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	  $weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
	  $title   = htmlentities(ucfirst($month_name))."&nbsp;".$year;
	  $previousMonth = date('F', mktime(0, 0, 0, $month-1, 1, $year));
	  $nextMonth = date('F', mktime(0, 0, 0, $month+1, 1, $year));
	  $calendar = "<div id=\"calendar_wrapLarge\"><h2 style=\"text-align:center;\"><span id=\"EC_previousMonthLarge\"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$title&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"EC_nextMonthLarge\"></span></h2><br />";

    $calendar .= "<table id=\"wp-calendarLarge\">\n\t<thead>\n\t<tr>\n";
    // Following two lines will get the length of day names - Ron
    $options = get_option('optionsEventsCalendar');
    $day_name_length = $options['daynamelengthLarge'];

	  if($day_name_length){ //if the day names should be shown ($day_name_length > 0)
		  //if day_name_length is >3, the full name of the day will be printed
		  foreach($day_names as $d)
			  $calendar .= "\t\t<th abbr=\"".htmlentities($d)."\" scope=\"col\" title=\"".htmlentities($d)."\">".htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d)."</th>\n";
		  $calendar .= "\t</tr>\n\t</thead>\n\t";
	  }

	  if($weekday > 0) $calendar .= "\t\t<td colspan=\"".$weekday."\" class=\"pad\">&nbsp;</td>"; //initial \"empty\" days
	  for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		  if($weekday == 7){
			  $weekday   = 0; //start a new week
			  $calendar .= "\n\t</tr>\n\t<tr>\n\t\t";
		  }
      $dayID = ("$month/$day/$year" == date('m/j/Y')) ? " id=\"todayLarge\"" : "";
		  $calendar .= "<td".$dayID."><div class=\"dayHead\">$day</div><div id=\"events-calendar-".$day."Large\"></div></td>";
	  }
	  if($weekday != 7) $calendar .= "\n\t\t<td colspan=\"".(7-$weekday)."\" class=\"pad\">&nbsp;</td>"; //remaining "empty" days

	  echo $calendar."\t</tr>\n\t</tbody>\n\t</table>";
	  $js->calendarDataLarge($month, $year);
	  echo "</div>";
	}

	  /**
	   * Displays the Admin Calendar
	   */
	  function displayAdmin($year, $month, $days = array(), $day_name_length = 7) {
    $first_day = get_option('start_of_week');
    $first_of_month = gmmktime(0,0,0,$month,1,$year);

	  $day_names = array();
	  for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) //January 4, 1970 was a Sunday
		  $day_names[$n] = ucfirst(gmstrftime('%A',$t)); //%A means full textual day name

	  list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	  $weekday = ($weekday + 7 - $first_day) % 7; //adjust for $first_day
	  $title   = htmlentities(ucfirst($month_name))."&nbsp;".$year;
	  $previousMonth = date('F', mktime(0, 0, 0, $month-1, 1, $year));
	  $nextMonth = date('F', mktime(0, 0, 0, $month+1, 1, $year));
	  $calendar = "<h2 style=\"text-align:center;\"><a href=\"?page=events-calendar&EC_action=switchMonthAdmin&EC_month=" . ($month-1) . "&EC_year=" . ($year) . "\">&laquo;" . $previousMonth . "</a>  Events Calendar ($title)  <a href=\"?page=events-calendar&EC_action=switchMonthAdmin&EC_month=" . ($month+1) . "&EC_year=" . ($year) . "\">" . $nextMonth . "&raquo;</a></h2><hr />";

    $calendar .= "<table id=\"wp-calendar\">\n\t<thead>\n\t<tr>\n";

	  if($day_name_length){ //if the day names should be shown ($day_name_length > 0)
		  //if day_name_length is >3, the full name of the day will be printed
		  foreach($day_names as $d)
			  $calendar .= "\t\t<th abbr=\"".htmlentities($d)."\" scope=\"col\" title=\"".htmlentities($d)."\">".htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d)."</th>\n";
		  $calendar .= "\t</tr>\n\t</thead>\n\t\n";
	  }

	  if($weekday > 0) $calendar .= "\t\t<td colspan=\"".$weekday."\" class=\"pad\">&nbsp;</td>"; //initial \"empty\" days
	  for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		  if($weekday == 7){
			  $weekday   = 0; //start a new week
			  $calendar .= "\n\t</tr>\n\t<tr>\n\t\t";
		  }
      $dayID = ($day == date('j')) ? " id=\"today\"" : "";
		  $calendar .= "<td".$dayID."><div class=\"dayHead\">$day</div><div id=\"events-calendar-$day\"></div></td>";
	  }
	  if($weekday != 7) $calendar .= "\n\t\t<td colspan=\"".(7-$weekday)."\" class=\"pad\">&nbsp;</td>"; //remaining "empty" days

	  echo $calendar."\t</tr>\n\t</tbody>\n\t</table>";
	}
}
endif;
?>