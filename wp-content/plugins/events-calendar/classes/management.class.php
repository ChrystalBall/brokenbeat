<?php
require_once(EVENTSCALENDARPATH . "classes/db.class.php");
require_once(EVENTSCALENDARPATH.'ics/ICS_Parse.php');
require_once(EVENTSCALENDARPATH.'ics/ICS_File.php');
require_once(EVENTSCALENDARPATH.'ics/ICS_Recur.php');

/**
*php4 fix by Brett Minnie (http://www.fractalmetal.co.za)
* fix fixed by Kerwin Kanago 30-Oct-2007
*/
if (phpversion () < '5' && !function_exists(str_split)){
function str_split($text, $split = 1){
if (!is_string($text)) return false;
if (!is_numeric($split) && $split < 1) return false;
$len = strlen($text);
$array = array();
$counter = 0;
$current = $split;
while ($counter < $len){
$current = ($current < $len)?$current:$len;
$array[] = substr($text, $counter, $current);
$counter = $current;
$current += $split;
}
return $array;
}
}
/**
*End Fix
*/

if( !class_exists("Management_071181") ) :


class Management_071181
{
  var $displayStartDate;
  var $displayEndDate;
  var $mmonth;
  var $yyear;
  var $externalArray;
  var $eventList;
  
  function Management_071181($dStartDate, $dEndDate, $m, $y)
  {
    $this->displayStartDate = $dStartDate;
    $this->displayEndDate = $dEndDate;
    $this->mmonth = $m;
    $this->yyear = $y;
    $this->externalArray = array();
    $this->eventList = array();
  }
  
  function getEventList()
  {
    $db = new DB_071181();
    $currentEvent = array();
    
    $numDays = date('t', mktime(0,0,0,$this->mmonth,1,$this->yyear));
    for($i=1;$i<=$numDays;$i++)
    {
      $this->eventList[$i] = array();
    }
    
    //Main Calendar
    //for($i=1; $i<=$numDays; $i++)
    //{
    //  $d = date('Y-m-d', mktime(0,0,0,$this->mmonth,$i,$this->yyear));
      $results = $db->getDateRange($this->displayStartDate,$this->displayEndDate);
      //$this->eventList[$i] = array();
      foreach($results as $r)
      {
        $currentEvent['eID'] = $r->id;
        $currentEvent['eTitle'] = addslashes($r->eventTitle);
        $currentEvent['eDescription'] = addslashes($r->eventDescription);
        $currentEvent['eLocation'] = addslashes($r->eventLocation);
        $currentEvent['eStartDate'] = addslashes($r->eventStartDate);
        $currentEvent['eStartTime'] = addslashes($r->eventStartTime);
        $currentEvent['eEndDate'] = addslashes($r->eventEndDate);
        $currentEvent['eEndTime'] = addslashes($r->eventEndTime);
        $currentEvent['accessLevel'] = $r->accessLevel;

        $d = split('-', $currentEvent['eStartDate']);
        $d = (int)$d[2];

        array_push($this->eventList[$d], $currentEvent);
      }
    //}      
    
    //External Calendars
    //for($i=1; $i<=$numDays; $i++)
    //{
      //$d = date('Y-m-d', mktime(0,0,0,$this->mmonth,$i,$this->yyear));
      $results = $db->getExternalCalendarList();
      foreach($results as $r)
      {
        if($r->externalType=='iCal')
        {
          /////if (!file_exists(ICS_CACHE)) {
          /////    mkdir(ICS_CACHE,0755) or die ("Cannot write Cache Directory for ".ICS_CACHE.".");
          /////}
          $startDateArray = split('-',$this->displayStartDate);
          $endDateArray = split('-',$this->displayEndDate);
          $now=strtotime($this->displayStartDate);
          $then=strtotime($this->displayEndDate);
          $now_arr=getdate($now);
          //$now=strtotime($now_arr['month']." 1, ".$$now_arr['year']);
          //$url=EVENTSCALENDARPATH.'basic.ics';
          $url=trim($r->externalAddress);
          $icsobject = new ICS_FiletoArray($url);
          ICS_ParseParse($icsobject,$now,$then,$eventlist,$sortlist);
          array_multisort($sortlist, SORT_ASC, $eventlist);
          $j=-1;
          foreach ($eventlist as $event){
              if (strtotime($event['DTSTART'])>=$now&&strtotime($event['DTSTART'])<=$then){
                  $currentEvent['eID'] = $j;
                $currentEvent['eTitle'] = addslashes(addslashes($event['SUMMARY']."<br />".$r->externalName));
                $currentEvent['eDescription'] = addslashes(addslashes($event['DESCRIPTION']));
                $currentEvent['eLocation'] = addslashes(addslashes($event['LOCATION']));
                $currentEvent['eStartDate'] = addslashes(addslashes(strftime("%Y-%m-%d",strtotime($event['DTSTART']))));
                $currentEvent['eStartTime'] = addslashes(addslashes(strftime("%H:%M:%S",strtotime($event['DTSTART']))));
                $currentEvent['eEndDate'] = addslashes(addslashes(strftime("%Y-%m-%d",strtotime($event['DTEND']))));
                $currentEvent['eEndTime'] = addslashes(addslashes(strftime("%H:%M:%S",strtotime($event['DTEND']))));
                $d = split('-', $currentEvent['eStartDate']);
              $d = (int)$d[2];
                array_push($this->eventList[$d], $currentEvent);
              $j--;
              }              
          }
        }
      //}
    }
  }
    
  function displayManagementPage()
  {
    if( $_GET['action']=='editEvent' )
    {
      echo "<div class=\"wrap\">";      
      $this->displayEditEventForm();
      echo "</div>";
    }
    else if( $_GET['action']=='editExternal' )
    {
      echo "<div class=\"wrap\">";      
      $this->displayEditExternalForm();
      echo "</div>";
    }
    else
    {
      $this->getEventList();
      $this->displayCalendar();
      $this->displayAddEventForm();
      $this->displayExternalCalendars();
      $this->displayAddExternalForm();
      echo "</div>";
    }
  }
  
  function displayCalendar()
  {
    $explodedDate = explode("-", $this->displayStartDate);
    $mmonth = $explodedDate[1];
    $yyear = $explodedDate[0];

    $db = new DB_071181();
    
    $results = $db->getDateRange($this->displayStartDate, $this->displayEndDate);
    ?>
    <h2><?php printf(__('Events Calendar','events-calendar')) ?> (<?php
    if($mmonth==1){echo ''.__("January","events-calendar").' ';}
    if($mmonth==2){echo ''.__("February","events-calendar").' ';}
    if($mmonth==3){echo ''.__("March","events-calendar").' ';}
    if($mmonth==4){echo ''.__("April","events-calendar").' ';}
    if($mmonth==5){echo ''.__("May","events-calendar").' ';}
    if($mmonth==6){echo ''.__("June","events-calendar").' ';}
    if($mmonth==7){echo ''.__("July","events-calendar").' ';}
    if($mmonth==8){echo ''.__("August","events-calendar").' ';}
    if($mmonth==9){echo ''.__("September","events-calendar").' ';}
    if($mmonth==10){echo ''.__("October","events-calendar").' ';}
    if($mmonth==11){echo ''.__("November","events-calendar").' ';}
    if($mmonth==12){echo ''.__("December","events-calendar").' ';}
    echo $yyear;
    ?>)
    </h2>
    <form name="SelectMonth" method="POST" action="?page=events-calendar.php">
      <p style="text-align:right;">
      <?php if($mmonth!=date('n')){?>
      <a href="?page=events-calendar.php" />Today</a>
      <?php }?>
      <select id="mmonth" name="mmonth">
        <option value="1" <?php if ($mmonth==1){echo 'selected=selected';}?>><?php printf(__('January','events-calendar')) ?></option>
        <option value="2" <?php if ($mmonth==2){echo 'selected=selected';}?>><?php printf(__('February','events-calendar')) ?></option>
        <option value="3" <?php if ($mmonth==3){echo 'selected=selected';}?>><?php printf(__('March','events-calendar')) ?></option>
        <option value="4" <?php if ($mmonth==4){echo 'selected=selected';}?>><?php printf(__('April','events-calendar')) ?></option>
        <option value="5" <?php if ($mmonth==5){echo 'selected=selected';}?>><?php printf(__('May','events-calendar')) ?></option>
        <option value="6" <?php if ($mmonth==6){echo 'selected=selected';}?>><?php printf(__('June','events-calendar')) ?></option>
        <option value="7" <?php if ($mmonth==7){echo 'selected=selected';}?>><?php printf(__('July','events-calendar')) ?></option>
        <option value="8" <?php if ($mmonth==8){echo 'selected=selected';}?>><?php printf(__('August','events-calendar')) ?></option>
        <option value="9" <?php if ($mmonth==9){echo 'selected=selected';}?>><?php printf(__('September','events-calendar')) ?></option>
        <option value="10" <?php if ($mmonth==10){echo 'selected=selected';}?>><?php printf(__('October','events-calendar')) ?></option>
        <option value="11" <?php if ($mmonth==11){echo 'selected=selected';}?>><?php printf(__('November','events-calendar')) ?></option>
        <option value="12" <?php if ($mmonth==12){echo 'selected=selected';}?>><?php printf(__('December','events-calendar')) ?></option>
      </select>
      <input type="text" id="yyear" name="yyear" size="2" style="width:35px;" maxlength="4" value="<?php echo $yyear;?>" />
      <span class="submit"><input type="submit" value="Go &raquo;" /></span></p>
    </form>
    <?php
    
    $results = $db->getDateRange($this->displayStartDate, $this->displayEndDate);
    $rArray = array();
    for($i=1; $i<=31; $i++)
    {
      $rArray[$i] = false;
    }
    foreach($results as $r)
    {
      $eDate = explode("-", $r->eventStartDate);
      $dayofmonth = date('j', mktime(0,0,0,$eDate[1],$eDate[2],$eDate[0]));
      $rArray[$dayofmonth] = true;
    }
    $firstDay = get_option('start_of_week');
    echo $this->printCalendar($yyear, $mmonth, $rArray, 4, NULL, $firstDay);
    /*
    foreach($results as $r)
    {
      echo "$r->eventTitle <a href=\"?page=events-calendar.php&amp;action=edit&amp;eID=$r->id\">Edit</a> <a href=\"?page=events-calendar.php&amp;DeleteEventSubmitted=1&amp;eID=$r->id&amp;eDate=$r->eventDate\">Delete</a><br />";
      echo "$r->eventLocation<br />";
      echo "$r->eventDate<br />";
      echo "$r->eventStartTime<br />";
      echo "$r->eventEndTime<br /><br />";
    }
    */
  }
  
  function printCalendar($yyear, $mmonth, $days = array(), $day_name_length = 3, $mmonth_href = NULL, $first_day = 0, $pn = array())
  {
      $first_of_month = gmmktime(0,0,0,$mmonth,1,$yyear);
      #remember that mktime will automatically correct if invalid dates are entered
      # for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
      # this provides a built in "rounding" feature to generate_calendar()
  
      $day_names = array(); #generate all the day names according to the current locale
      for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
          $day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name
  
      list($mmonth, $yyear, $mmonth_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
      $weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
      $title   = htmlentities(ucfirst($mmonth_name)).'&nbsp;'.$yyear;  #note that some locales don't capitalize month and day names
  
      #Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
      @list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
      if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
      if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
      $calendar = '<table class="calendar" style="margin:auto" cellpadding="0" cellspacing="0">'."\n";//.
          //'<caption class="calendar-month">'.$p.($mmonth_href ? '<a href="'.htmlspecialchars($mmonth_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";
  
      if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
          #if day_name_length is >3, the full name of the day will be printed
          foreach($day_names as $d)
              $calendar .= '<th abbr="'.htmlentities($d).'" style="border:thin black solid;margin:0;">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
          $calendar .= "</tr>\n<tr style=\"height:100px;\">";
      }
  
      if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'" style="border:thin black solid;text-align:left;valign:top;width:125px;">&nbsp;</td>'; #initial 'empty' days
      for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
          if($weekday == 7){
              $weekday   = 0; #start a new week
              $calendar .= "</tr>\n<tr style=\"height:100px;\">";
          }
          if($this->eventList[$day]){
              $calendar .= '<td style="border:thin black solid;width:125px;vertical-align:top;"><div style="color:black;font-weight:bold;background-color:#C0C0C0;width:125px;border-bottom:thin black solid;">'.$day.'</div>';
              $d = date("Y-m-d", mktime(0,0,0,$mmonth, $day, $yyear));
              $results = $this->eventList[$day];
              foreach($results as $r)
              {
                $calendar .= "<nobr><span id=\"event-" . $r['eID'] . "\" style=\"font-weight:bold;font-size:12px;text-decoration:none;\"><a href=\"";
                if($r['eID']>=0)
                {
                  $calendar .= "?page=events-calendar.php&amp;action=editEvent&amp;eID=".$r['eID'] ."\"";
                }
                else
                {
                  $calendar .= "#\"";
                }
                $calendar .= "onMouseover=\"ddrivetip('";
                $calendar .= "<span style=\'font-size:10px;\'><span style=\'font-weight:bold\'>" . __('Title','events-calendar') . ":</span>".ereg_replace("[\r\n]", " ", stripslashes($r['eTitle']))."<br />";
                $calendar .= "<span style=\'font-weight:bold\'>" . __('Description','events-calendar') . ":</span>".ereg_replace("[\r\n]", " ", stripslashes($r['eDescription']))."<br />";
                $calendar .= "<span style=\'font-weight:bold\'>" . __('Location','events-calendar') . ":</span>".ereg_replace("[\r\n]", " ", stripslashes($r['eLocation']))."<br />";
                $calendar .= "<span style=\'font-weight:bold\'>" . __('Start Time','events-calendar') . ":</span>".$r['eStartDate'];
                if($r['eStartTime']!='')
                {
                  $calendar .= ", ".$r['eStartTime'];
                }
                $calendar .= "<br />";
                $calendar .= "<span style=\'font-weight:bold\'>" . __('End Time','events-calendar') . ":</span>".$r['eEndDate'];
                if($r['eEndTime']!='')
                {
                  $calendar .= ", ".$r['eEndTime'];
                }
                $calendar .= "<br />";
                if($r['accessLevel'] == 'public') $accessLevel = 'Public';
                if($r['accessLevel'] == 'level_10') $accessLevel = 'Administrator';
                if($r['accessLevel'] == 'level_7') $accessLevel = 'Editor';
                if($r['accessLevel'] == 'level_2') $accessLevel = 'Author';
                if($r['accessLevel'] == 'level_1') $accessLevel = 'Contributor';
                if($r['accessLevel'] == 'level_0') $accessLevel = 'Subscriber';
                $calendar .= "<span style=\'font-weight:bold\'>" . __('Visibility Level', 'events-calendar') . ":</span>".$accessLevel;
                $calendar .= "<br />";
                $displayTitle = str_split(stripslashes($r['eTitle']), 15);
                $calendar .= "</span>','white', 175);\" onMouseout=\"hideddrivetip();\">" . $displayTitle[0] ."</a>";
                if($r['eID']>=0)
            $calendar .= "<a href=\"?page=events-calendar.php&amp;DeleteEventSubmitted=1&amp;eID=".$r['eID']."&amp;eDate=".$r['eStartDate']."\"><img src=\"" . EVENTSCALENDARURL . "images/delete.gif\" style=\"width:10px;height:10px;border:none;\" /></a>";
          $calendar .= "<br /></span></nobr>";
              }
              $calendar .= "</td>\n";
          }
          else $calendar .= "<td style=\"border:thin black solid;width:125px;vertical-align:top;\"><div style=\"color:black;font-weight:bold;background-color:#C0C0C0;width:125px;border-bottom:thin black solid;\">$day</div></td>\n";
      }
      //if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days
      for($weekday; $weekday<7; $weekday++)
      {
        $calendar .= '<td style="border:thin black solid;width:125px;vertical-align:top;">&nbsp;</td>';
      }
  
      return $calendar."</tr>\n</table>\n";
  }
  
  function displayAddEventForm()
  {
    echo "      <h2>" . __('Add Event','events-calendar') . "</h2>\n";
    echo "      <form name=\"AddEvent\" method=\"post\" action=\"?page=events-calendar.php\">\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Add Event &raquo;\" />\n";
    echo "        </p>\n";
    echo "        <table class=\"editform\" width=\"100%\" cellspacing=\"2\" cellpadding=\"5\">\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"title\">" . __('Title','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eTitle\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Description','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><textarea type=\"text\" name=\"eDescription\" /></textarea></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Location','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eLocation\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"allday\">" . __('All Day','events-calendar') . "?:</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"checkbox\" value=\"checked\" name=\"eAllDay\" onclick=\"showhideText(document.AddEvent.eAllDay, 'eStartTime');showhideText(document.AddEvent.eAllDay, 'eEndTime');\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"startdate\">" . __('Start Day','events-calendar') . " (YYYY-MM-DD):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eStartDate\" /><a href=\"javascript:displayDatePicker('eStartDate');\"><img style=\"border:none;text-decoration:none;\" src=\"".EVENTSCALENDARURL."images/calendar.gif\"></a></td>\n";
    echo "          </tr>\n";
    echo "          <tr id=\"eStartTime\">\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"starttime\">" . __('Start Time','events-calendar') . " (HH:MM:SS):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eStartTime\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"enddate\">" . __('End Date','events-calendar') . " (YYYY-MM-DD):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eEndDate\" /><a href=\"javascript:displayDatePicker('eEndDate');\"><img style=\"border:none;text-decoration:none;\" src=\"".EVENTSCALENDARURL."images/calendar.gif\"></a></td>\n";
    echo "          </tr>\n";
    echo "          <tr id=\"eEndTime\">\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"endtime\">" . __('End Time','events-calendar') . " (HH:MM:SS):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eEndTime\" /></td>\n";
    echo "          </tr>\n";
    echo "          </tr id=\"accessLevel\">\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"accessLevel\">" . __('Visibility Level', 'events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\">\n";
    echo "              <select name=\"accessLevel\">\n";?>
                              <option value="public"><?php printf(__("Public","events-calendar")) ?></option>
                              <option value="level_10"><?php printf(__("Administrator","events-calendar")) ?></option>
                              <option value="level_7"><?php printf(__("Editor","events-calendar")) ?></option>
                              <option value="level_2"><?php printf(__("Author","events-calendar")) ?></option>
                              <option value="level_1"><?php printf(__("Contributor","events-calendar")) ?></option>
                              <option value="level_0"><?php printf(__("Subscriber","events-calendar")) ?></option>
<?php
    echo "              </select>\n";
    echo "            </td></tr>\n";
    echo "        </table>\n";
    echo "        <input type=\"hidden\" name=\"AddEventSubmitted\" value=\"1\" />\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Add Event &raquo;\" />\n";
    echo "        </p>\n";
    echo "      </form>\n";
  }
  
  function displayEditEventForm()
  {
    $db = new DB_071181();
    $r = $db->getEntry($_GET['eID']);
    $r = $r[0];
    $eStartTime = stripslashes($r->eventStartTime);
    $eEndTime = stripslashes($r->eventEndTime);
    $checked = "";
    $visibility = "";
    if(stripslashes($r->eventStartTime)=="00:00:00" && stripslashes($r->eventEndTime)=="00:00:00")
    {
      $eStartTime = "";
      $eEndTime = "";
      $checked = "checked=\"checked\"";
      $visibility = "style=\"visibility:hidden;\"";
    }
    echo "      <h2>" . __('Edit Event','events-calendar') . "</h2>\n";
    echo "      <form name=\"EditEvent\" method=\"post\" action=\"?page=events-calendar.php\">\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Update Event &raquo;\" />\n";
    echo "        </p>\n";
    echo "        <table class=\"editform\" width=\"100%\" cellspacing=\"2\" cellpadding=\"5\">\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"title\">" . __('Title','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eTitle\" value=\"".stripslashes($r->eventTitle)."\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Description','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><textarea type=\"text\" name=\"eDescription\" />".stripslashes($r->eventDescription)."</textarea></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Location','events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eLocation\" value=\"".stripslashes($r->eventLocation)."\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"allday\">" . __('All Day','events-calendar') . "?:</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"checkbox\" value=\"checked\" name=\"eAllDay\" onclick=\"showhideText(document.EditEvent.eAllDay, 'eStartTime');showhideText(document.EditEvent.eAllDay, 'eEndTime');\" $checked/></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"startdate\">" . __('Start Date','events-calendar') . " (YYYY-MM-DD):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eStartDate\" value=\"".stripslashes($r->eventStartDate)."\"/><a href=\"javascript:displayDatePicker('eStartDate');\"><img style=\"border:none;text-decoration:none;\" src=\"".EVENTSCALENDARURL."images/calendar.gif\"></a></td>\n";
    echo "          </tr>\n";
    echo "          <tr id=\"eStartTime\" $visibility>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"starttime\">" . __('Start Time','events-calendar') . " (HH:MM:SS):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eStartTime\" value=\"".$eStartTime."\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"enddate\">" . __('End Date','events-calendar') . " (YYYY-MM-DD):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eEndDate\" value=\"".stripslashes($r->eventEndDate)."\"/><a href=\"javascript:displayDatePicker('eEndDate');\"><img style=\"border:none;text-decoration:none;\" src=\"".EVENTSCALENDARURL."images/calendar.gif\"></a></td>\n";
    echo "          </tr>\n";
    echo "          <tr id=\"eEndTime\" $visibility>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"endtime\">" . __('End Time','events-calendar') . " (HH:MM:SS):</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eEndTime\" value=\"".$eEndTime."\" /></td>\n";
    echo "          </tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"accessLevel\">" . __('Visibility Level', 'events-calendar') . ":</label></th>\n";
    echo "            <td width=\"67%\">\n";
    echo "              <select name=\"accessLevel\">\n";?>
                              <option value="public"><?php printf(__("Public","events-calendar")) ?></option>
                              <option value="level_10" <?php if (isset($r->accessLevel) && 'level_10' ==$r->accessLevel ) echo 'selected="selected"';?>><?php printf(__("Administrator","events-calendar")) ?></option>
                              <option value="level_7" <?php if ( isset($r->accessLevel) && 'level_7' == $r->accessLevel ) echo 'selected="selected"'; ?>><?php printf(__("Editor","events-calendar")) ?></option>
                              <option value="level_2" <?php if ( isset($r->accessLevel) && 'level_2' == $r->accessLevel ) echo 'selected="selected"'; ?>><?php printf(__("Author","events-calendar")) ?></option>
                              <option value="level_1" <?php if ( isset($r->accessLevel) && 'level_1' == $r->accessLevel ) echo 'selected="selected"'; ?>><?php printf(__("Contributor","events-calendar")) ?></option>
                              <option value="level_0" <?php if ( isset($r->accessLevel) && 'level_0' == $r->accessLevel ) echo 'selected="selected"'; ?>><?php printf(__("Subscriber","events-calendar")) ?></option>
<?php
    echo "              </select>\n";
    echo "            </td></tr>\n";
    echo "        </table>\n";
    echo "        <input type=\"hidden\" name=\"EditEventSubmitted\" value=\"1\" />\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Update Event &raquo;\" />\n";
    echo "        </p>\n";
    echo "<input type=\"hidden\" name=\"eID\" value=\"$r->id\" />";
    echo "      </form>\n";
  }
  
  function displayExternalCalendars()
  {
    $db = new DB_071181();
    $results = $db->getExternalCalendarList();
    echo "<h2>" . __('External Calendars','events-calendar') . "</h2>\n";
    echo "<table class=\"widefat\">\n";
    echo "  <thead>\n";
    echo "    <tr>\n";
    echo "      <th scope=\"col\" style=\"text-align:center;\">ID</th>\n";
    echo "      <th scope=\"col\">" . __('Type','events-calendar') . "</th>\n";
    echo "      <th scope=\"col\">" . __('Name','events-calendar') . "</th>\n";
    echo "      <th scope=\"col\">" . __('Address','events-calendar') . "</th>\n";
    echo "      <th scope=\"col\" colspan=\"2\" style=\"text-align:center;\">" . __('Action','events-calendar') . "</th>\n";
    echo "    </tr>\n";
    echo "  </thead>\n";
    echo "  <tbody id=\"the-list\">\n";
    $i=0;
    foreach($results as $r)
    {
      if($i % 2 == 0)
        $class = "alternate";
      else
        $class = "";
      echo "    <tr id=\"external-" . $r->id . "\" class=\"" . $class . "\">\n";
      echo "      <th scope=\"row\" style=\"text-align:center;\">" . $r->id . "</th>\n";
      echo "      <td>" . $r->externalType . "</td>\n";
      echo "      <td>" . $r->externalName . "</td>\n";
      echo "      <td>" . $r->externalAddress . "</td>\n";
      echo "      <td style=\"text-align:center;\"><a class=\"edit\" href=\"?page=events-calendar.php&amp;action=editExternal&amp;id=$r->id\">" . __('Edit','events-calendar') . "</a></td><td style=\"text-align:center;\"><a class=\"delete\" href=\"?page=events-calendar.php&amp;DeleteExternalSubmitted=1&amp;id=" . $r->id . "\">" . __('Delete','events-calendar') . "</a></td>\n";
      echo "    </tr>\n";
      $i++;
    }
    echo "  </tbody>\n";
    echo "</table>\n\n<br />";
  }

  function displayAddExternalForm()
  {
    echo "      <form name=\"AddExternal\" method=\"POST\" action=\"?page=events-calendar.php\">\n";
    echo "      Currently only iCal is supported but I hope to add more soon.<br />\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Add External Calendar &raquo;\" />\n";
    echo "        </p>\n";
    echo "        <table class=\"editform\" width=\"100%\" cellspacing=\"2\" cellpadding=\"5\">\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"title\">" . __('Type','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><select name=\"eType\" /><option value=\"iCal\" />iCal</option></select></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Name','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eName\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"date\">" . __('Address','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eAddress\" /></td>\n";
    echo "          </tr>\n";
    echo "        </table>\n";
    echo "            <input type=\"hidden\" name=\"month\" value=\"$this->mmonth\" />\n";
    echo "            <input type=\"hidden\" name=\"year\" value=\"$this->yyear\" />\n";
    echo "        <input type=\"hidden\" name=\"AddExternalSubmitted\" value=\"1\" />\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Add External Calendar &raquo;\" />\n";
    echo "        </p>\n";
    echo "      </form>\n";
  }
  
  function displayEditExternalForm()
  {
    global $mmonth, $yyear;
    $db = new DB_071181();
    $r = $db->getExternalCalendar($_GET['id']);
    $r = $r[0];
    
    echo "      <form name=\"AddExternal\" method=\"POST\" action=\"?page=events-calendar.php\">\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Update External Calendar &raquo;\" />\n";
    echo "        </p>\n";
    echo "        <table class=\"editform\" width=\"100%\" cellspacing=\"2\" cellpadding=\"5\">\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"title\">" . __('Type','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><select name=\"eType\" /><option value=\"iCal\" />iCal</option></select></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"location\">" . __('Name','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eName\" value=\"$r->externalName\" /></td>\n";
    echo "          </tr>\n";
    echo "          <tr>\n";
    echo "            <th width=\"33%\" scope=\"row\" valign=\"top\"><label for=\"date\">" . __('Address','events-calendar') . "</label></th>\n";
    echo "            <td width=\"67%\"><input type=\"text\" name=\"eAddress\" value=\"$r->externalAddress\" /></td>\n";
    echo "          </tr>\n";
    echo "        </table>\n";
    echo "            <input type=\"hidden\" name=\"eID\" value=\"$r->id\" />\n";
    echo "            <input type=\"hidden\" name=\"month\" value=\"$this->mmonth\" />\n";
    echo "            <input type=\"hidden\" name=\"year\" value=\"$this->yyear\" />\n";
    echo "        <input type=\"hidden\" name=\"EditExternalSubmitted\" value=\"1\" />\n";
    echo "        <p class=\"submit\">\n";
    echo "          <input type=\"submit\" name=\"submit\" value=\"Update External Calendar &raquo;\" />\n";
    echo "        </p>\n";
    echo "      </form>\n";
  }
  
  function displayWidgetControl()
  {
    $options = get_option('widgetEventsCalendar');
    if ( !is_array($options) ){
      $options = array();
      $options['title'] = 'Events Calendar';
      $options['dayOfWeekLength'] = '3';
      $options['accessLevel'] = 'level_10';
      $options['fontSize'] = 10;
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
    $fontSize = htmlspecialchars($options['fontSize'], ENT_QUOTES);
    
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
      <p style="text-align:center;">
        <label for="eventscalendar-fontSize">
          <?php printf(__("Font size", "events-calendar")) ?>:
          <input type="text" name="eventscalendar[fontSize]" id="eventscalendar-fontSize" style="width:25px;" value="<?php echo $fontSize;?>"/>px
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
}

endif;
?>
