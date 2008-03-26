<?php 
/*  ICS Parser
  Copyright (C) 2008 Jeremy Austin-Bardo <tjaustinbardo@gmail.com>
  Parts based on iCalender File Parser by Ben Barnett <ben@menial.co.uk>

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
$DaysOfWeek = array("SU","MO","TU","WE","TH","FR","SA");
class ICS_DateTime{
  /* Determine Variance and Count for ICS File RRULE:INTERVAL=##
  ** $obj = new ICS_DateTime($stamp); creates a datetime from ICS datetime stamp.
  ** $value = $obj->unixtime($type); retrieve the NEW or OLD unix time.
  ** $value = $obj->is_newer(); compare OLD and NEW. Returns TRUE if NEW greater.
  ** $obj->date($date); set NEW date with unix time.
  ** $obj->time($time); set NEW time with unix time.
  ** $obj->set($value,$part); set NEW value with interger for part of datetime.
  ** $obj->add_days($Days); add number of days to NEW.
  ** $obj->add_weeks($weeks,$day); add number of weeks to NEW, optional set day of week.
  ** $obj->offset($interval,$type); determine offset from OLD to NEW, return offset and count.
  */ 
  var $Datetime,$Original;
  function __construct($Stamp){
    /* Convert ICS timestamp to unixtime. ######T######
    */
    if (strlen($Stamp)==8) {
      preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})/esi",
           $Stamp,$s_value);
    } else {
      preg_match("/([0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{0,2})([0-9]{0,2})([0-9]{0,2})/esi",
           $Stamp,$s_value);
    }
    $this->Datetime['hr'] = (isset($s_value[4]))?$s_value[4]:0; 
    $this->Datetime['mn'] = (isset($s_value[5]))?$s_value[5]:0;
    $this->Datetime['sc'] = (isset($s_value[6]))?$s_value[6]:0;
    $this->Datetime['yr'] = $s_value[1];
    $this->Datetime['mo'] = $s_value[2];
    $this->Datetime['dy'] = $s_value[3];
    $this->Original=$this->Datetime;
  }
  function unixtime($Type="NEW") {
    /* Return a unixtime from values stored, either NEW or OLD.
    */
    switch (strtoupper($Type)){
      case("NEW"):
        // Return NEW modified date
        return mktime (
          $this->Datetime['hr'],$this->Datetime['mn'],$this->Datetime['sc'],
          $this->Datetime['mo'],$this->Datetime['dy'],$this->Datetime['yr']);
      case ("OLD"):
        // Return OLD original date
        return mktime (
          $this->Original['hr'],$this->Original['mn'],$this->Original['sc'],
          $this->Original['mo'],$this->Original['dy'],$this->Original['yr']);    
  }   }
  function is_newer() {
    /* Return a whether NEW is greater than OLD.
    */
    return ($this->unixtime("NEW") > $this->unixtime("OLD")-86400)?TRUE:FALSE;
  }
  function date($Date) {
    /* Set NEW month, day, year with an unixtime value.
    */
    list($this->Datetime['mo'],$this->Datetime['dy'],$this->Datetime['yr']) 
      = explode("/",strftime("%D",$Date),3);
  }
  function time($Time) {
    /* Set NEW hour, minute, second with an unixtime value.
    */
    list($this->Datetime['hr'],$this->Datetime['mn'],$this->Datetime['sc']) 
      = explode(":",strftime("%T",$Tate),3);
  }
  function set($Value,$Part) {
    /* Set datetime part with an interger value from expected range.
    */
    switch (strtolower($Part)) {
      case ("month"):
        if (is_int($Value)||($Value>0&&$Value<13))
          $this->Datetime['mo'] = $Value;
        break;
      case ("day"):
        if (is_int($Value)||($Value>0&&$Value<32))
          $this->Datetime['dy'] = $Value;
        break;
      case ("year"):
        if (is_int($Value)||(strlen($Value)==2||strlen($Value)==4))
          $this->Datetime['yr'] = $Value;
        break;
      case ("hour"):
        if (is_int($Value)||($Value>-1&&$Value<24))
          $this->Datetime['hr'] = $Value;
        break;
      case ("minute"):
        if (is_int($Value)||($Value>-1&&$Value<60))
          $this->Datetime['mn'] = $Value;
        break;
      case ("second"):
        if (is_int($Value)||($Value>-1&&$Value<60))
          $this->Datetime['sc'] = $Value;
        break;
  }   }
  function add_days($Days) {
    /* Add days to NEW date time.
    */
    $this->date($this->unixtime("NEW")+$Days*86400);
  }
  function add_weeks($Weeks,$Day=NULL) {
    /* Add weeks to NEW date time, optional specify 2 letter abrev of day.
    */
    global $DaysOfWeek;
    $datetime = $this->unixtime("NEW");
    if (is_null($Day)) {
      $this->date($datetime+($Weeks*7*86400));
    } else {
      // Get NEW numerical day of week.
      $weekday = strftime("%w",$datetime);
      // Cycle next week of NEW
      for ($dayweeks = $datetime;$dayweeks < $datetime+(8*86400);
        $dayweeks +=86400,$weekday++) {
        $weekday = ($weekday==7)?0:$weekday;
        if ($DaysOfWeek[$weekday] == $Day) { 
          // Cycle day and passed day match. Compute New from here.
          $this->date($dayweeks+($Weeks*7*86400));
          break;
  }   }   }   }   
  function offset($Interval,$Type) {
    $Interval = (is_int($Interval)&&$Interval!=0)?$Interval:1;
    switch (strtolower($Type)){
      case ("yearly"||"year"):
        $offset=$Datetime['yr']-$Original['yr'];
        break;
      case ("monthly"||"month"):
        $offset = ((($Datetime['yr']-$Original['yr'])*12)-$Original['mn'])
          +$Datetime['mn'];
        break;
      case ("weekly"||"week"):
        list ($o_yr,$o_wk) = explode(" ",strftime("%G %U",$this->unixtime("OLD")));
        list ($n_yr,$n_wk) = explode(" ",strftime("%G %U",$this->unixtime("NEW")));
        $offset=0; 
        for ($year = $o_yr;$year < $c_yr;$year++)
          $offset += ((INT)strftime("%U",strtotime($year."1227")));
        $offset = (($offset+$n_wk)-$o_wk);      
        break;
      case ("daily"||"daily"):
        list ($o_yr,$o_dy) = explode(" ",strftime("%Y %j",$this->unixtime("OLD")));
        list ($n_yr,$n_dy) = explode(" ",strftime("%Y %j",$this->unixtime("NEW")));
        $offset=0; 
        for ($year = $o_yr;$year < $c_yr;$year++)
          $offset += ((INT)strftime("%j",strtotime($year."1231")));
        $offset = (($offset+$n_dy)-$o_dy);      
        break;
      case ("hourly"||"hour"):
        list ($o_yr,$o_dy) = explode(" ",strftime("%Y %j",$this->unixtime("OLD")));
        list ($n_yr,$n_dy) = explode(" ",strftime("%Y %j",$this->unixtime("NEW")));
        $offset=0; 
        for ($year = $o_yr;$year < $c_yr;$year++)
          $offset += ((INT)strftime("%j",strtotime($year."1231")));
        $offset = (($offset+$n_dy)-$o_dy)*24;      
        break;
      case ("minutely"||"minute"):
        list ($o_yr,$o_dy) = explode(" ",strftime("%Y %j",$this->unixtime("OLD")));
        list ($n_yr,$n_dy) = explode(" ",strftime("%Y %j",$this->unixtime("NEW")));
        $offset=0; 
        for ($year = $o_yr;$year < $c_yr;$year++)
          $offset += ((INT)strftime("%j",strtotime($year."1231")));
        $offset = (($offset+$n_dy)-$o_dy)*1440;      
        break;
      case ("secondly"||"second"):
        list ($o_yr,$o_dy) = explode(" ",strftime("%Y %j",$this->unixtime("OLD")));
        list ($n_yr,$n_dy) = explode(" ",strftime("%Y %j",$this->unixtime("NEW")));
        $offset=0; 
        for ($year = $o_yr;$year < $c_yr;$year++)
          $offset += ((INT)strftime("%j",strtotime($year."1231")));
        $offset = (($offset+$n_dy)-$o_dy)*86400;      
        break;   
      default:
        return array(0=>NULL,1=>NULL);
    }
    return array(
      0=>(($offset==0)?1:$offset%$Interval),        
      1=>(($offset==0)?1:floor($offset/$Interval)));
  }
  function is_expired($Until) {
    // Check if event date is expired at this date time.
    $expires = ($Until=="")?9999999999:(INT)$Until;
    return (($this->unixtime("NEW") > $expires) ? TRUE : FALSE);
  }
  function is_current($Monthyear) {
    //  Check if value is within the current month.
    list($month,$day,$year) = explode("/",strftime("%D",$Monthyear),3);
    $min = strtotime($month."/01/".$year);
    $numdays = cal_days_in_month(CAL_GREGORIAN,$month,$year);
    $max = strtotime($month."/".$numdays."/".$year);
    return (($this->unixtime("NEW") > $min-86400 && 
      $this->unixtime("NEW") < $max+86400) ? TRUE : FALSE);
  }
}
/* VERIFICATION FUNCTIONS 
*/
function ICS_IsWithin($Value,$Min,$Max){
  // Check if value is within range of expected values.
  return ((is_int($Value)||$Value>$Min-1&&$Value<$Max+1&&$Value!=0)?TRUE:FALSE);
  }   
  
/* RRULE PARSING FUNCTION
*/
function ICS_GetRecur($Now,$Event){
  // Process RRULE line of an ICS file
  global $DaysOfWeek;
  $ruleset = $Event['RRULE'];
  // Check if RRULE has an UNTIL value that has expired
  $curr_date = getdate($Now);
  $dtstart = new ICS_DateTime($Event['DTSTART']);
  $interval = (isset($ruleset['INTERVAL'])) ? $ruleset['INTERVAL']:1; 
  //$ruleset['COUNT'] = (isset($ruleset['COUNT'])) ? $ruleset['COUNT']:0;
  switch (strtoupper($ruleset['FREQ'])){
    //
    // Process RRULE:FREQ=YEARLY return this month's date 
    //
    case ('YEARLY'):
      $dtstart->set($curr_date['year'],"year");
      // Get offset to next event and number of prior events
      list ($offset,$count) = $dtstart->offset($interval,"year");
      if (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1) {
        trigger_error("xpire",E_USER_NOTICE); return array(0=>NULL);
      }
      if ($offset != 1) {
        trigger_error("range",E_USER_NOTICE); return array(0=>NULL);
      }
            
      if (isset($ruleset['BYMONTH'])&&isset($ruleset['BYMONTHDAY'])) {
        // RRULE:FREQ=YEARLY;
        //  BYMONTH=[1-12](,[1-12],[..]); 
        //  BYDAY=[[1-5][SU|MO|TU|WE|TH|FR|SA]](,
            //  [[1-5][SU|MO|TU|WE|TH|FR|SA]],[..]);
        // Combines BYMONTH and BYMONTHDAY with current year to form date.
        // ICAL spec states values could be negative, but we ignore these.
        $bymonths = explode(",",$ruleset['BYMONTH']);
        // Set month of event from array of BYMONTH values.
        foreach ($bymonths as $bymonth) {
          if (ICS_IsWithin($bymonth,1,12)) {
            $dtstart->set($bymonth,"month");
          } else {
            trigger_error("value",E_USER_WARNING);
            continue;
          }
          $bymonthdays = explode(",",$ruleset['BYMONTHDAY']);
          // Set day of event from array of BYMONTHDAY values.
          foreach ($bymonthdays as $bymonthday) {
            
            if (ICS_IsWithin($bymonthday,1,31)) {
              $dtstart->set($bymonthday,"day");
            } else {
              trigger_error("value",E_USER_WARNING);
              continue;
            }
            // Validate reccur event date for current month.
            if ($dtstart->is_current($Now)) {
              if ($dtstart->is_expired($ruleset['UNTIL'])):
                trigger_error("xpire",E_USER_NOTICE);
              else:
                $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
              endif;
            } else { trigger_error("range",E_USER_NOTICE); }
        }   }   
        return (is_array($n_date)) ? $n_date : array(0=>NULL);
      }

      if (isset($ruleset['BYMONTH'])&&isset($ruleset['BYDAY'])) {
        // RRULE:FREQ=YEARLY;
        //  BYMONTH=[1-12](,[1-12],[..]); 
        //  BYDAY=[1-31](,[1-31],[..]); 
        // Combines BYMONTH and BYMONTHDAY with current year to form date.
        // ICAL spec states values could be negative, but we ignore these.
        $bymonths = explode(",",$ruleset['BYMONTH']);
        // Set month of event from array of BYMONTH values.
        foreach ($bymonths as $bymonth) {
          if (ICS_IsWithin($bymonth,1,12)) {
            $dtstart->set($bymonth,"month");
          } else {
            trigger_error("value",E_USER_WARNING);
            continue;
          }
          $bydays = explode(",",$ruleset['BYDAY']);
          foreach ($bydays as $byday) {
            $dtstart->set(1,"day");
            if (strlen($byday) == 3){
              // RRULE:FREQ=MONTHLY;
              //  BYDAY=[[1-5][SU|MO|TU|WE|TH|FR|SA]](,
              //  [[1-5][SU|MO|TU|WE|TH|FR|SA]],[..]);
              // Determine week of month and day abbr to form event date.
              $r_week = (INT)substr($byday,0,1);
              $r_day = substr($byday,1,2);
              if (!in_array($r_day,$DaysOfWeek) || !ICS_IsWithin($r_week,1,5)) {
                trigger_error("value",E_USER_WARNING); continue;
              }
              // Set date of recurrance
              $dtstart->add_weeks($r_week-1,$r_day);
              // Validate date of recurrance
              if ($dtstart->is_current($Now)) {
                if ($dtstart->is_expired($ruleset['UNTIL'])):
                  trigger_error("xpire",E_USER_NOTICE); continue;
                else:
                  $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
                endif;            
              } else { trigger_error("range",E_USER_NOTICE); }
            } else { trigger_error("rules",E_USER_WARNING); }
      }   }   } 
   
      if (isset($ruleset['BYWEEKNO']) && isset($ruleset['BYDAY'])) {
        // RRULE:FREQ=YEARLY;
        //  BYWEEKNO=[1-53](,[1-53],[..]);
        //  BYDAY=[SU|MO|TU|WE|TH|FR|SA](,[SU|MO|TU|WE|TH|FR|SA],[..]);
        // Determine BYWEEKNO of current year on BYDAY day abbr to form date.
        // ICAL spec states values could be negative, but we ignore these.
        $byweeknos = explode(",",$ruleset['BYWEEKNO']);
        foreach ($byweeknos as $byweekno) {
          if (!ICS_IsWithin($byweekno,1,53)) {
            trigger_error("value",E_USER_WARNING); continue;
          }
          $bydays = explode(",",$ruleset['BYDAY']);
          foreach ($bydays as $byday) {
            if (!in_array($byday,$DaysOfWeek)) {
              trigger_error("value",E_USER_WARNING); continue;
            }
            $dtstart->set(1,"month");
            // Check 1st 4 days for first ISO week of 4+ days. 
            for ($day=1;$day<5;$day++) {
              $dtstart->set($day,"day");
              if (strftime("%V",$dtstart->unixtime()) == 1) break;   
            }   
            // Set date of recurrance
            $dtstart->add_weeks($byweekno,$byday);
            // Validate date of recurrance
            if ($dtstart->is_current($Now)) {
              if ($dtstart->is_expired($ruleset['UNTIL'])):
                trigger_error("xpire",E_USER_NOTICE);
              else:
                $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
              endif;
            } else { trigger_error("range",E_USER_NOTICE); }
        }   }   
        return (is_array($n_date)) ? $n_date : array(0=>NULL);   
      }
      
      if (isset($ruleset['BYYEARDAY'])) {
        // RRULE:FREQ=YEARLY;
        //  BYYEARDAY=[1-366](,[1-366],[..]);
        // Determine BYYEARDAY of current year to form date.
        // ICAL spec states values could be negative, but we ignore these.
        $byyeardays = explode(",",$ruleset['BYYEARDAY']);
        foreach ($byyeardays as $byyearday) {
          if (!ICS_IsWithin($byyearday,1,366)) {
            trigger_error("value",E_USER_WARNING); continue;
          }        
          // Set date of recurrance
          $dtstart->set(1,"month"); 
          $dtstart->set(1,"day");
          $dtstart->add_days($ruleset['BYYEARDAY']);
          // Validate date of recurrance
          if ($dtstart->is_current($Now)) {
            if ($dtstart->is_expired($ruleset['UNTIL'])):
              trigger_error("xpire",E_USER_NOTICE);
            else:
            $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
            endif;
          } else { trigger_error("range",E_USER_NOTICE); }
        }
        return (is_array($n_date)) ? $n_date : array(0=>NULL);
      }
      
      // Process RRULE:FREQ=YEARLY; Ignore other setting combinations.
      // Perhaps we should be checking for other unhandled combinations.
      // Validate date of reccurrance
      if ($dtstart->is_current($Now)) {
        if ($dtstart->is_expired($ruleset['UNTIL'])):
          trigger_error("xpire",E_USER_NOTICE);
        else:
          $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
        endif;
      } else { 
        trigger_error("range",E_USER_NOTICE); return array(0=>NULL);
      }
      return (is_array($n_date)) ? $n_date : array(0=>NULL);
      break;
    //
    // Process RRULE:FREQ=MONTHLY: return this month's date 
    //
    case ('MONTHLY'):
      $dtstart->set($curr_date['mon'],"month");
      $dtstart->set($curr_date['year'],"year");
      // Get offset to next event and number of prior events
      list ($offset,$count) = $dtstart->offset($interval,"month");
      if (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1) {
        trigger_error("xpire",E_USER_NOTICE); return array(0=>NULL);
      }
      if ($offset != 1) {
        trigger_error("range",E_USER_NOTICE); return array(0=>NULL);
      }
      
      if (isset($ruleset['BYMONTHDAY'])){
        // RRULE:FREQ=MONTHLY;
        // BYMONTHDAY=[1-31](,[1-31],[..]);
        // Combines current month, BYMONTHDAY, and current year to form date.
        // ICAL spec states values could be negative, but we ignore these.
        $bymonthdays = explode(",",$ruleset['BYMONTHDAY']);
        foreach ($bymonthdays as $bymonthday) {
          if (!ICS_IsWithin($bymonthday,1,31)) {
            trigger_error("value",E_USER_WARNING); continue;
          } 
          // Validate date of recurrance
          if ($dtstart->is_current($Now)) {
            if ($dtstart->is_expired($ruleset['UNTIL'])):
              trigger_error("xpire",E_USER_NOTICE);
            else:
              $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
            endif;
          } else { trigger_error("range",E_USER_NOTICE); }
        }
        return (is_array($n_date)) ? $n_date : array(0=>NULL);
      }
      
      if (isset($ruleset['BYDAY'])) {
        // RRULE:FREQ=MONTHLY;BYDAY=[1-5][SU|MO|TU|WE|TH|FR|SA];
        // ICAL spec states values could be negative, but we ignore these.
        $bydays = explode(",",$ruleset['BYDAY']);
        foreach ($bydays as $byday) {
          $dtstart->set(1,"day");
          if (strlen($byday) == 3){
            // RRULE:FREQ=MONTHLY;
            //  BYDAY=[[1-5][SU|MO|TU|WE|TH|FR|SA]](,
            //  [[1-5][SU|MO|TU|WE|TH|FR|SA]],[..]);
            // Determine week of month and day abbr to form event date.
            $r_week = (INT)substr($byday,0,1);
            $r_day = substr($byday,1,2);
            if (!in_array($r_day,$DaysOfWeek) || !ICS_IsWithin($r_week,1,5)) {
              trigger_error("value",E_USER_WARNING); continue;
            }
            // Set date of recurrance
            $dtstart->add_weeks($r_week-1,$r_day);
            // Validate date of recurrance
            if ($dtstart->is_current($Now)) {
              if ($dtstart->is_expired($ruleset['UNTIL'])):
                trigger_error("xpire",E_USER_NOTICE); continue;
              else:
                $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
              endif;            
            } else { trigger_error("range",E_USER_NOTICE); continue; }
          } else {
            // Process RRULE:FREQ=MONTHLY;
            //  BYDAY=[SU|MO|TU|WE|TH|FR|SA](,[SU|MO|TU|WE|TH|FR|SA],[..]);
            // Determine day abbr repeat each week to form date with current year.
            if (!in_array($byday,$DaysOfWeek)) {
              trigger_error("value",E_USER_WARNING); continue; 
            }
            // Set start date of recurrance
            $dtstart->add_weeks(0,$byday);
            do {
              $dtstart->add_weeks($offset);
              if ($dtstart->is_expired($ruleset['UNTIL']) ||
                (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1)): 
                trigger_error("xpire",E_USER_NOTICE); break;
              else:
                $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
              endif;
              $offset = $interval; $count++;
            } while ($dtstart->is_current($Now));
        }   }
        return (is_array($n_date)) ? $n_date : array(0=>NULL);
      }
      
      // Process RRULE:FREQ=MONTHLY; Ignore other setting combinations.
      // Perhaps we should be checking for other unhandled combinations.
      // Validate date of reccurrance
      if ($dtstart->is_current($Now)) {
        if ($dtstart->is_expired($ruleset['UNTIL'])):
          trigger_error("xpire",E_USER_NOTICE);
        else:
          $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
        endif;
      } else { 
        trigger_error("range",E_USER_NOTICE); return array(0=>NULL);
      }
      return (is_array($n_date)) ? $n_date : array(0=>NULL);
    //
    // Process RRULE:FREQ=WEEKLY return this months' dates
    //
    case ('WEEKLY'):
      $dtstart->set($curr_date['mon'],"month");
      $dtstart->set($curr_date['year'],"year");
      // Get offset to next event and number of prior events
      list ($offset,$count) = $dtstart->offset($interval,"week");
      if (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1) {
        trigger_error("xpire",E_USER_NOTICE); return array(0=>NULL); 
      }
      if (!ICS_IsWithin($offset,1,5)) {
        trigger_error("range",E_USER_NOTICE); return array(0=>NULL); 
      }
      
      if (isset($ruleset['BYDAY'])) {
        //  BYDAY=[SU|MO|TU|WE|TH|FR|SA](,[SU|MO|TU|WE|TH|FR|SA],[..]);
        // Determine day abbr repeat each week to form date with current year.
        $bydays = explode(",",$ruleset['BYDAY']);
        foreach ($bydays as $byday) {
          if (!in_array($byday,$DaysOfWeek)) {
            trigger_error("value",E_USER_WARNING); continue; 
          }
          // Set start date of recurrance
          $dtstart->set(1,"day");
          $dtstart->add_weeks(0,$ruleset['BYDAY']);
          do {
            $dtstart->add_weeks($offset);
            if ($dtstart->is_expired($ruleset['UNTIL']) ||
              (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1)): 
              trigger_error("xpire",E_USER_NOTICE); break;
            else:
              $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
            endif;
            $offset = $interval; $count++;
          } while ($dtstart->is_current($Now));
        }
        return (is_array($n_date)) ? $n_date : array(0=>NULL);
      }
      // Process RRULE:FREQ=WEEKLY; Ignore other setting combinations.
      // Perhaps we should be checking for other unhandled combinations.
      do {
        $dtstart->add_weeks($offset);
        if ($dtstart->is_expired($ruleset['UNTIL']) ||
          (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1)): 
          trigger_error("xpire",E_USER_NOTICE); break;
        else:
          $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
        endif;
        $offset = $interval; $count++;
      } while ($dtstart->is_current($Now));
      return (is_array($n_date)) ? $n_date : array(0=>NULL);
      break;
    //
    // Process RRULE:FREQ=DAILY return this months' dates
    //
    case ('DAILY'):
      $dtstart->set($curr_date['mon'],"month");
      $dtstart->set($curr_date['year'],"year");
      $dtstart->set(1,"day");
      // Get offset to next event and number of prior events
      list ($offset,$count) = $dtstart->offset($interval,"day");
      if (isset($ruleset['COUNT']) && $ruleset['COUNT'] < $count) {
        trigger_error("xpire",E_USER_NOTICE);
        return array(0=>NULL);
      }
      if (!ICS_IsWithin($offset,1,31)) {
        trigger_error("range",E_USER_NOTICE);
        return array(0=>NULL);
      }
      
      //Process RRULE:FREQ=DAILY; Ignore other setting combinations.
      // Perhaps we should be checking for other unhandled combinations.
      do {
        $dtstart->add_days($offset);
        if ($dtstart->is_expired($ruleset['UNTIL']) ||
          (isset($ruleset['COUNT']) && $ruleset['COUNT'] > $count+1)): 
          trigger_error("xpire",E_USER_NOTICE); break;
        else:
          $n_date[] = ($dtstart->is_newer()) ? $dtstart->unixtime() : NULL;
        endif;
        $offset = $interval; $count++;
      } while ($dtstart->is_current($Now));
      return (is_array($n_date)) ? $n_date : array(0=>NULL);
      break;
    //
    // Process RRULE:FREQ=HOURLY return this months' times
    //
    case ('HOURLY'):
      // Unsure how to implement this without taking too much resources
      trigger_error("rules",E_USER_WARNING);
      return array(0=>NULL);
    //
    // Process RRULE:FREQ=MINUTELY return this months' times
    //
    case ('MINUTELY'):
      // Unsure how to implement this without taking too much resources
      trigger_error("rules",E_USER_WARNING);
      return array(0=>NULL);
    //
    // Process RRULE:FREQ=SECONDLY return this months' times
    //
    case ('SECONDLY'):
      // Unsure how to implement this without taking too much resources
      trigger_error("rules",E_USER_WARNING);
      return array(0=>NULL);
  }
  trigger_error("rules",E_USER_WARNING);
}
?>