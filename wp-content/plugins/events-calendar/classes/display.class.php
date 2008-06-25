<?php

require_once(EVENTSCALENDARPATH . "classes/db.class.php");

if( !class_exists("Display_071181") ) :

class Display_071181 {
  var $displayStartDate;
  var $displayEndDate;
  var $mmonth;
  var $yyear;
  
  function Display_071181($dStartDate, $dEndDate, $m, $y) {
    $this->displayStartDate = $dStartDate;
    $this->displayEndDate = $dEndDate;
    $this->mmonth = $m;
    $this->yyear = $y;
  }
  
  function displayWidget($args) {
    extract($args);
    $options = get_option('widgetEventsCalendar');
    $title = "<a href='".get_option('siteurl')."/wp-admin/edit.php?page=events-calendar'>".$options['title']."</a>";
    if ( empty($title) ) {
      $title = '&nbsp;';
    }
    echo $before_widget . $before_title . $title . $after_title;
    if ( $options['type'] == 'calendar' ) {
      echo '<div id="calendar_wrap" style="text-align:right;width:100%;">';
      $this->getEventList();
      $this->getCalendar();
      echo '</div>';
    } elseif ( 'list' == $options['type'] ) {
      $this->printEventList('widget', $options['listcount']);
    }
    echo $after_widget;
  }
  
  function getEventList() {
    global $current_user;
    $db = new DB_071181();
    $currentEvent = array();
    
    $numDays = date('t', mktime(0,0,0,$this->mmonth,1,$this->yyear));
    for($i=1;$i<=$numDays;$i++) {
      $this->eventList[$i] = array();
    }
    
    // Calendar
    $results = $db->getDateRange($this->displayStartDate,$this->displayEndDate);

    foreach($results as $r) {
      $cap = $r->accessLevel;
      if($current_user->has_cap($cap) || $cap == 'public') {
        $currentEvent['eID'] = $r->id;
        $currentEvent['eTitle'] = addslashes($r->eventTitle);
        $currentEvent['eDescription'] = addslashes($r->eventDescription);
        $currentEvent['eLocation'] = addslashes($r->eventLocation);
        $currentEvent['eStartDate'] = addslashes($r->eventStartDate);
        $currentEvent['eStartTime'] = addslashes($r->eventStartTime);
        $currentEvent['eEndDate'] = addslashes($r->eventEndDate);
        $currentEvent['eEndTime'] = addslashes($r->eventEndTime);

        $d = split('-', $currentEvent['eStartDate']);
        $d = (int)$d[2];

        array_push($this->eventList[$d], $currentEvent);
      }
    }

    //External Calendars
    $results = $db->getExternalCalendarList();
    foreach($results as $r) {
      if($r->externalType=='iCal') {
        /////if (!file_exists(ICS_CACHE)) {
        /////    mkdir(ICS_CACHE,0755) or die ("Cannot write Cache Directory for ".ICS_CACHE.".");
        /////}
        $startDateArray = split('-',$this->displayStartDate);
        $endDateArray = split('-',$this->displayEndDate);
        $now=strtotime($this->displayStartDate);
        $then=strtotime($this->displayEndDate);
        $now_arr=getdate($now);
        $url=trim($r->externalAddress);
        $icsobject = new ICS_FiletoArray($url);
        ICS_ParseParse($icsobject,$now,$then,$eventlist,$sortlist);
        array_multisort($sortlist, SORT_ASC, $eventlist);
        $j=-1;
        foreach ($eventlist as $event) {
          if (strtotime($event['DTSTART'])>=$now&&strtotime($event['DTSTART'])<=$then) {
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
    }
  }
  
  function getCalendar() {
    $options = get_option('widgetEventsCalendar');
    $fontSize = isset($options['fontSize']) ? $options['fontSize'] : 10;
    $explodedDate = explode("-", $this->displayStartDate);
    $mmonth = $explodedDate[1];
    $yyear = $explodedDate[0];

    $db = new DB_071181();
    
    ?>
    <form name="SelectMonth" method="POST" action="" style="text-align:left;">
      <select id="mmonth" name="mmonth" style="font-family:arial;font-size:<?php echo $fontSize;?>px;width:<?php echo $fontSize*8;?>px;">
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
      <input type="text" id="yyear" name="yyear" style="font-size:<?php echo $fontSize;?>px;width:<?php echo $fontSize*3;?>px;" maxlength="4" style="font-family:arial;" value="<?php echo $yyear;?>" />
      <input type="submit" value="GO" style="font-family:arial;font-size:<?php echo $fontSize;?>px;"/>
    </form>
    <?php
    $results = $db->getDateRange($this->displayStartDate, $this->displayEndDate);
    $rArray = array();
    for($i=1; $i<=31; $i++) {
      $rArray[$i] = false;
    }
    foreach($results as $r) {
      $eDate = explode("-", $r->eventStartDate);
      $dayofmonth = date('j', mktime(0,0,0,$eDate[1],$eDate[2],$eDate[0]));
      $rArray[$dayofmonth] = true;
    }
    $firstDay = get_option('start_of_week');
    $dayLength = $options['dayOfWeekLength'];
    echo $this->printCalendar($yyear, $mmonth, $rArray, $dayLength, NULL, $firstDay, array(), $options['dateFormat'], $options['timeFormat']);
  }
  
  function printCalendar($yyear, $mmonth, $days = array(), $day_name_length = 3, $mmonth_href = NULL, $first_day = 0, $pn = array(), $dateFormat = 'Y-m-d', $timeFormat = 'h:i:s a')
  {
    $options = get_option('widgetEventsCalendar');
    $fontSize = $options['fontSize'];
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
    $calendar = '<table class="calendar" style="font-size:'.$fontSize.'px;">'."\n";//.
  
    if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
      #opening <tr> added by Everson Santos Araujo
      $calendar .= "<thead><tr>";
      #if day_name_length is >3, the full name of the day will be printed
      foreach($day_names as $d)
        $calendar .= '<th abbr="'.htmlentities($d).'" scope="col">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
      $calendar .= "</tr></thead>\n<tbody><tr>";
    }
  
    if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'" class="pad">&nbsp;</td>'; #initial 'empty' days
    for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
      if($weekday == 7){
        $weekday   = 0; #start a new week
        $calendar .= "</tr>\n<tr>";
      }
      if($this->eventList[$day]){
        //$calendar .= '<td onMouseover="ddrivetip(\'';
        $calendar .= '<td';
        $d = date("Y-m-d", mktime(0,0,0,$mmonth, $day, $yyear));
        $results = $this->eventList[$day];
        $inards = '';
        $inardsNoStyle = '';
        foreach($results as $r)
        {
          //$inards .= "<span style=\'font-size:10px;color:black;\'><span style=\'font-weight:bold\'>".__('Title','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eTitle']))."<br />";
          $inardsNoStyle .= "<span class=\'eventPopupText\'><span class=\'eventPopupTitle\'>".__('Title','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eTitle']))."<br />";
          //if ( !empty($r['eDescription']) ) $inards .= "<span style=\'font-weight:bold\'>".__('Description','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eDescription']))."<br />";
          if ( !empty($r['eDescription']) ) $inardsNoStyle .= "<span class=\'eventPopupTitle\'>".__('Description','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eDescription']))."<br />";
          //if ( !empty($r['eLocation']) ) $inards .= "<span style=\'font-weight:bold\'>".__('Location','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eLocation']))."<br />";
          if ( !empty($r['eLocation']) ) $inards .= "<span class=\'eventPopupTitle\'>".__('Location','events-calendar').": </span>".ereg_replace("[\r\n]", " ", stripslashes($r['eLocation']))."<br />";
          //if (!empty($r['eStartDate']) && $r['eStartDate'] != '0000-00-00' && $r['eStartTime']!='' && $r['eStartTime']!='00:00:00') {
          //  $inards .= "<span style=\'font-weight:bold\'>".__('Start Time','events-calendar').": </span>";
          //  if (!empty($r['eStartDate']) && $r['eStartDate'] != '0000-00-00') $inards .= date($dateFormat, strtotime($r['eStartDate']));
          //  if($r['eStartTime']!='' && $r['eStartTime']!='00:00:00') {
          //    $inards .= ", ".date($timeFormat, strtotime($r['eStartTime']));
          //  }
          //  $inards .= "<br />";
          //}
          if (!empty($r['eStartDate']) && $r['eStartDate'] != '0000-00-00' && $r['eStartTime']!='' && $r['eStartTime']!='00:00:00') {
            $inardsNoStyle .= "<span class=\'eventPopupTitle\'>".__('Start Time','events-calendar').": </span>";
            if (!empty($r['eStartDate']) && $r['eStartDate'] != '0000-00-00') $inardsNoStyle .= date($dateFormat, strtotime($r['eStartDate']));
            if($r['eStartTime']!='' && $r['eStartTime']!='00:00:00') {
              $inardsNoStyle .= ", ".date($timeFormat, strtotime($r['eStartTime']));
            }
            $inardsNoStyle .= "<br />";
          }
          //if (!empty($r['eEndDate']) && $r['eEndDate'] != '0000-00-00' && $r['eEndTime']!='' && $r['eEndTime']!='00:00:00') {
          //  $inards .= "<span style=\'font-weight:bold\'>".__('End Time','events-calendar').": </span>";
          //  if (!empty($r['eEndDate']) && $r['eEndDate'] != '0000-00-00') $inards .= date($dateFormat, strtotime($r['eEndDate']));
          //  if($r['eEndTime']!='' && $r['eEndTime']!='00:00:00') {
          //    $inards .= ", ".date($timeFormat, strtotime($r['eEndTime']));
          //  }
          //  $inards .= "<br />";
          //}
          if (!empty($r['eEndDate']) && $r['eEndDate'] != '0000-00-00' && $r['eEndTime']!='' && $r['eEndTime']!='00:00:00') {
            $inardsNoStyle .= "<span class=\'eventPopupTitle\'>".__('End Time','events-calendar').": </span>";
            if (!empty($r['eEndDate']) && $r['eEndDate'] != '0000-00-00') $inardsNoStyle .= date($dateFormat, strtotime($r['eEndDate']));
            if($r['eEndTime']!='' && $r['eEndTime']!='00:00:00') {
              $inardsNoStyle .= ", ".date($timeFormat, strtotime($r['eEndTime']));
            }
            $inardsNoStyle .= "<br />";
          }
        }
        //$calendar .= $inards . '\',\'white\', 175);" onMouseout="hideddrivetip();" onClick="window.open(\'' . get_option('siteurl') . '/wp-eventPopup.php?date=' . $d . '&content=' . $inards . '\', \'Event Detail\', \'height=200,width=400,status=yes,toolbar=no,menubar=no,location=no\')" style="" class="dateColor">'.$day.'</td>';
        $calendar .= ' onClick="window.open(\'' . get_option('siteurl') . '/wp-eventPopup.php?date=' . $d . '&content=' . $inardsNoStyle . '\', \'Event Detail\', \'height=200,width=400,status=yes,toolbar=no,menubar=no,location=no\')" style="" class="dateColor">'.$day.'</td>';
      } else $calendar .= "<td style=\"color:black;cursor: pointer;text-align:center;\">$day</td>";
    }
    if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'" class="pad">&nbsp;</td>'; #remaining "empty" days
  
    return $calendar."</tr>\n</tbody></table>\n";
  }
  
  function printEventList($type, $count) {
    $options = get_option('widgetEventsCalendar');
    $fontSize = $options['fontSize'];
    $db = new DB_071181();
    if ( 'widget' == $type ) {
      $options = get_option('widgetEventsCalendar');
      $list = $db->getUpcoming((isset($options['listCount']) && !empty($options['listCount'])) ? $options['listCount'] : 5);
      ?>
        <ul id="list_wrap" style="font-size:<?php echo $fontSize;?>px">
          <?php foreach ( $list as $item ) : ?>
            <li><?php echo date($options['dateFormat'], strtotime($item->eventStartDate)) ?>: <span style="cursor: pointer;" class="eventsCalendar-item" id="event-<?php echo $item->id ?>"><?php echo $item->eventTitle ?></span></li>
          <?php endforeach; ?>
        </ul>
        <script type="text/javascript">
          var eventsCalendarData = {
            <?php foreach ( $list as $key => $item ) : ?>
              "<?php echo $item->id ?>" : {
                "title":"<?php echo $item->eventTitle ?>", 
                "description":"<?php echo $item->eventDescription ?>", 
                "location":"<?php echo $item->eventLocation ?>", 
                "startDate":"<?php echo date($options['dateFormat'], strtotime($item->eventStartDate)) ?>", 
                "startTime":"<?php if($item->eventStartTime!='' && $item->eventStartTime!='00:00:00') echo date($options['timeFormat'], strtotime($item->eventStartTime)) ?>", 
                "endDate":"<?php if (!empty($item->eventEndDate) && $item->eventEndDate != '0000-00-00') echo date($options['dateFormat'], strtotime($item->eventEndDate)) ?>", 
                "endTime":"<?php if($item->eventEndTime!='' && $item->eventEndTime!='00:00:00') echo date($options['timeFormat'], strtotime($item->eventEndTime)) ?>"
              }<?php if ( $key < count($list)-1 ) echo ",\n" ?>
            <?php endforeach; ?>
          }
          
          jQuery(".eventsCalendar-item").mouseout(function(){
            hideddrivetip();
          });
          jQuery(".eventsCalendar-item").mouseover(function(){
            var id = this.id.split("-")[1];
            var e = eventsCalendarData[id];
            tooltip = "";
            tooltip += "<div style='font-size:10px;'><span style='font-weight:bold'><?php printf(__('Title','events-calendar'))?>:</span> " + e.title + "</div>";
            if ( e.description != "" ) tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('Description','events-calendar'))?>:</span> " + e.description + "</div>";
            if ( e.location != "" ) tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('Location','events-calendar'))?>:</span> " + e.location + "</div>";
            if ( e.endDate == "" ) tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('Date','events-calendar'))?>:</span> ";
            else tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('Start Date','events-calendar'))?>:</span> ";
            tooltip += e.startDate;
            if ( e.startTime != "" ) tooltip += " - " + e.startTime;
            tooltip += "</div>";
            
            if ( e.endDate != "" ) {
              tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('End Date','events-calendar'))?>:</span> " + e.endDate;
              if ( e.endTime != "" ) tooltip += " - " + e.endTime;
              tooltip += "</div>";
            } else if ( e.endTime != "" ){
              tooltip += "<div style='font-size:10px;'><span style=\'font-weight:bold\'><?php printf(__('End Time','events-calendar'))?>:</span> " + e.endTime + "</div>";
            }
            ddrivetip(tooltip, "white", 175);
          });
        </script>
      <?php
    }
  }
}

endif;

?>
