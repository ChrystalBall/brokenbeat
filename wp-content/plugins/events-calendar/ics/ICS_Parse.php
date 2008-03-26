<?php 
/*  ICS Parser
  Copyright (C) 2008 Jeremy Austin-Bardo <tjaustinbardo@gmail.com>
  Parts based on iCalender File Parser by Ben Barnett <ben@menial.co.uk>
  Parts based on MagpieRSS cache code by Kellan Elliott-McCrea <kellan@protest.net>

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License version 2 as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
  MA 02110-1301, USA.

*/
$tagarray = array(
  'VEVENT' => array ('DTSTART','DTEND','RRULE','DESCRIPTION','LAST-MODIFIED',
             'LOCATION','SEQUENCE','SUMMARY'),
  'INFO' => array("X-WR-CALNAME","X-WR-CALDESC","X-WR-RELCALID",
            "X-WR-TIMEZONE","CALSCALE","VERSION","PRODID")
  );
  
/* RRULE:FREQ=[SECONDLY|MINUTELY|HOURLY|DAILY|WEEKLY|MONTHLY|YEARLY];
**** Must be specified and identifies the type of recurrence.
** INTERVAL=[0-366];
**** A positive integer that may be used for how often the rule occurs.
** BYMONTH=[1-12][,[1-12]];
**** Comma separated list of months of year may be used for when the rule occurs.
**** >> FREQ=YEARLY;BYMONTH=1; ## First Month of year;
** BYWEEKNO=(+|-)[1-53][,(+|-)[1-53]];
**** Comma separated list of weeks of year may be used for when the rule occurs.
**** Based on value of WKST and year begins with 4 or more days in the week.  
**** >> FREQ=YEARLY;BYWEEKNO=1; ## First four-day week of year;
**** >> FREQ=YEARLY;BYWEEKNO=-1; ## Last four-day week of year;
** BYYEARDAY=(+|-)[1-366][,(+|-)[1-366]];
**** Comma separated list of days of year may be used for when the rule occurs.
**** >> FREQ=YEARLY;BYYEARDAY=1; ## First day of year;
**** >> FREQ=YEARLY;BYYEARDAY=-1; ## Last day of year;
** BYMONTHDAY=[(+|-)1-31][,[(+|-)1-31];
**** Comma separated list of days of month may be used for when the rule occurs.
**** >> FREQ=MONTHLY;BYMONTHDAY=1; ## First day of month;
**** >> FREQ=MONTHLY;BYMONTHDAY=-1; ## Last day of month;
** BYDAY=[(+|-)[1-5][SU|MO|TU|WE|TH|FR|SA]];  
**** COMMA separated list of days of week may be used for when the rule occurs.
**** An integer may precede for nth occurrence of day of week.
**** >> FREQ=MONTHLY;BYDAY=1MO; ## First Monday of month;
**** >> FREQ=MONTHLY;BYDAY=-1MO; ## Last Monday of month;
**** >> FREQ=WEEKLY;BYDAY=MO; ## Every Monday of Week;
** BYHOUR=[0-23][,[0-23]];
**** Comma separated list of hours of day may be used for when the rule occurs.
**** >> FREQ=DAILY;BYHOUR=12; ## Every Day at 12:00pm;
** BYMINUTE=[0-59][,[0-59]];
**** Comma separated list of minutes of hours may be used for when the rule occurs.
**** >> FREQ=HOURLY;BYMINUTE=15; ## Every Hour at quarter after;  
** BYSECOND=[0-59][,[0-59]];
**** Comma separated list of seconds of minutes may be used for when the rule occurs.
**** >> FREQ=HOURLY;BYMINUTE=15; ## Every Hour at quarter after;  
** BYSETPOS=[1-366];
**** Comma separated list of the nth occurrence within a set may be used to filter rule.
**** >> FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=1 First work day of month
**** >> FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-1 Last work day of month
** UNTIL=datetimestamp; 
**** Date-time value which tells when the rule ends. Must not be used with COUNT.
** COUNT=[0-99];
**** n occurrences after which the rule ends. Must not be used with UNTIL.
** WKST=[SU|MO|TU|WE|TH|FR|SA]|;
**** Day which the week is start from. Usually SU or MO.
*/
function ICS_ErrorHandle($errno, $errstr, $errfile, $errline){
  if(ICS_DEBUG)
  {
		$error_msgs = array(
	    "range"=>"Event outside current month.",
	    "value"=>"Event has invalid value.",
	    "xpire"=>"Event has expired.",
	    "rules"=>"Event has unkown setting.",
	    "skip"=>"Event recurrance has been skipped."
	    );
	  switch ($errno) {
	  case E_USER_ERROR:
	    echo "<strong>".$errline."</strong>".$error_msgs[$errstr]."<br>";
	    exit(1);
	    break;
	  case E_USER_WARNING:
	    echo "<strong>".$errline."</strong>".$error_msgs[$errstr]."<br>";
	    break;
	  case E_USER_NOTICE:
	    echo "<strong>".$errline."</strong>".$error_msgs[$errstr]."<br>";
	    break;
	  }
	  /* Don't execute PHP internal error handler */
  }
  return true;
}
set_error_handler("ICS_ErrorHandle");
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
//include('ICS_File.php');
//include('ICS_Recur.php');

function ICS_ParseParse($obj,$now,$then,&$eventlist,&$sortlist){
  // Parse ICS FILE and RRULE Lines for repeat events
  
  // Loop through $events array
  while ($obj->check()) {
    $event = ICS_ParseFile($obj);
    if (!empty($event['RRULE'])) {
      // Process $event RRULE line for events between $now and $then
      for ($time=$now;$time < $then;$time = $time+(86400*31)){
      if (ICS_DEBUG) e_array($event['RRULE']);
      if (strtotime($event['DTSART'])>$time) {
        trigger_error("skip",E_USER_NOTICE);continue;
      }
      $r_events = ICS_GetRecur($time,$event);
      // Loop through new date timestamps
      foreach ($r_events as $r_event){
        if ($r_event===NULL) {
          trigger_error("skip",E_USER_NOTICE);continue;
        }
        // Split original DTSTART and DTEND timestamp values
        preg_match("/([0-9]{8}).?([0-9]{6})?/esi",$event['DTSTART'],$dtvalues);
        list($null,$dstart,$tstart) = $dtvalues;
        $event['DTSTART'] = strftime("%Y%m%d",$r_event).(($tstart!="")?"T".$tstart:"");
        if (isset($event['DTEND'])) {
          preg_match("/([0-9]{8}).?([0-9]{6})?/esi",$event['DTEND'],$dtvalues);
          list($null,$dend,$tend) = $dtvalues;  
          $dif_dt = $dend - $dstart;
          $event['DTEND'] = strftime("%Y%m%d",$r_event)+($diff_dt).
            (($tend!="")?"T".$tend:"");
        }
        // assign modified event array to new array
        $eventlist[]=$event;
        // assign DTSTART to sort array
        $sortlist[]=strtotime($event['DTSTART']);  
      }   }
      unset($event);
    } else {
      // assign unmodified event array to new array
      $eventlist[]=$event;
      // assign DTSTART to sort array
      $sortlist[]=strtotime($event['DTSTART']);
      unset($event);
  }   }
  $obj->reset();
}  
function ICS_ParseFile($obj,$section="VEVENT") {
   // Parse ICS File for $section blocks
  global $tagarray;
  while ($obj->check()) {
    $line = $obj->line();
    // Begin parsing directives in current buffer
    if ($line =="BEGIN:".$section){
      do {
        preg_match ("/^(.*?):(.*)$/e", $line, $fieldvalue);
        list($null,$field,$value) = $fieldvalue;
        $field = (strpos($field,';')) ?
          strtoupper(substr($field,0,strpos($field,';'))) : strtoupper($field);
        $value = htmlspecialchars(stripslashes(preg_replace('/\\\\n/'," ",$value)));
        if (in_array($field,$tagarray[$section])) {
          if ($field == "RRULE") {
            $rules = explode (";",$value);
            $event[$field][0] = $value;
            foreach ($rules as $rule) {
              list($rulefield,$rulevalue) = explode("=",$rule,2);
              $event[$field][$rulefield] = $rulevalue;
          }   } else {
            $event[$field] = $value;
        }   }
        $line = $obj->line();
      } while ($line !="END:".$section);
      // Unset the BEGIN arr
      unset ($event["BEGIN"]);      
      return $event;
}   }   }


function ICS_FileInfo($obj) {
  global $tagarray;
  // Now loop through line by line...
  $max_fields = count($tagarray['INFO'])+1;
  while ($obj->check()||count($info)<$max_fields) {
    $line = $obj->line();
    // Begin parsing directives in current buffer
    preg_match ("/^(.*?):(.*)$/e", $line, $fieldvalue);
    list($null,$field,$value) = $fieldvalue;
    $field = (strpos($field,';')) ?
      strtoupper(substr($field,0,strpos($field,';'))) : strtoupper($field);
    $value = htmlspecialchars(stripslashes(preg_replace('/\\\\n/'," ",$value)));
    if (in_array($field,$tagarray['INFO'])) $info[$field] = $value;
  }
  $obj->reset();
  if (ICS_DEBUG) e_array ($info);
  //fclose($fp);
  return $info;
} 

/*HELPFUL DEBUG FUNCTIONS */
function e_array($array,$space=0) {
  foreach ($array as $key=>$value) {
    for ($indent=0; $indent < $space; $indent++) echo "&nbsp;";
    if (is_array($value)){
      echo "<b>[".$key."]</b> <br/>";
      e_array($value,$space+2);
    } else {
      echo "<b>".$key."</b> =>".$value."<br/>";
}   }   }
function e_var($value,$space=0) {
  for ($indent=0; $indent < $space; $indent++) echo "&nbsp;";
  echo $value."<br/>";
}
?>
