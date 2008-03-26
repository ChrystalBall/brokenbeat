<?php
/*
Plugin Name: Events Calendar
Plugin URI: http://www.lukehowell.com/events-calendar/
Description: Calendar to display events.  There is section under the manage tab for creating and editing events.  There are also widget options that allow for some pretty cool options.  This plugin can also be used with the k2 theme as a sidebar module.  Enjoy.
Version: 5.8.1
Author: Luke Howell
Author URI: http://www.lukehowell.com/

---------------------------------------------------------------------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You can see a copy of GPL at <http://www.gnu.org/licenses/>
---------------------------------------------------------------------
*/
define('EVENTSCALENDARPATH', ABSPATH."wp-content/plugins/events-calendar/");
define('EVENTSCALENDARURL', get_option('siteurl')."/wp-content/plugins/events-calendar/");
define("ICS_DEBUG",FALSE);
/////define("ICS_CACHE",EVENTSCALENDARPATH."cache");
define("ICS_MAXAGE",0);

if (function_exists('load_plugin_textdomain')) {
  load_plugin_textdomain('events-calendar', EVENTSCALENDARPATH . "languages/");
}

require_once(EVENTSCALENDARPATH . "classes/management.class.php");
require_once(EVENTSCALENDARPATH . "classes/event.class.php");
require_once(EVENTSCALENDARPATH . "classes/external.class.php");
require_once(EVENTSCALENDARPATH . "classes/display.class.php");
require_once(EVENTSCALENDARPATH . "classes/db.class.php");
require_once(EVENTSCALENDARPATH . "classes/js.class.php");

if( class_exists('Management_071181') && class_exists('Event_071181') && class_exists('DB_071181') ) :

if (isset($_POST['mmonth'])) { $mmonth = $_POST['mmonth']; $mmonth = ereg_replace ("[[:space:]]", "", $mmonth); $mmonth = ereg_replace ("[[:punct:]]", "", $mmonth); $mmonth = ereg_replace ("[[:alpha:]]", "", $mmonth); }
if (isset($_POST['yyear'])) { $yyear = $_POST['yyear']; $yyear = ereg_replace ("[[:space:]]", "", $yyear); $yyear = ereg_replace ("[[:punct:]]", "", $yyear); $yyear = ereg_replace ("[[:alpha:]]", "", $yyear); if ($yyear < 1990) { $yyear = 1990; } if ($yyear > 2035) { $yyear = 2035; } }
if (isset($_POST['today'])) { $ttoday = $_POST['today']; $ttoday = ereg_replace ("[[:space:]]", "", $ttoday); $ttoday = ereg_replace ("[[:punct:]]", "", $ttoday); $ttoday = ereg_replace ("[[:alpha:]]", "", $ttoday); }

if(isset($_POST['AddEventSubmitted']))
{
  $eStartTime = $_POST['eAllDay']=='checked' ? '' : $_POST['eStartTime'];
  $eEndTime = $_POST['eAllDay']=='checked' ? '' : $_POST['eEndTime'];
  $eStartDate = (isset($_POST['eStartDate'])&&!empty($_POST['eStartDate'])) ? $_POST['eStartDate'] : date("Y-m-d");
  $eEndDate = (isset($_POST['eEndDate'])&&!empty($_POST['eEndDate'])) ? $_POST['eEndDate'] : $eStartDate;
  $event = new Event_071181($_POST['eTitle'], $_POST['eDescription'], $_POST['eLocation'], $eStartDate, $eStartTime, $eEndDate, $eEndTime, $_POST['accessLevel'], NULL);
  $event->addEvent();
  $explodedDate = explode("-", $eStartDate);
  $mmonth = $explodedDate[1];
  $yyear = $explodedDate[0];
}
if(isset($_POST['EditEventSubmitted']))
{
  if($_POST['eAllDay']=='checked')
  {
    $eStartTime = '';
    $eEndTime = '';
  }
  else
  {
    $eStartTime = $_POST['eStartTime'];
    $eEndTime = $_POST['eEndTime'];
  }
  $event = new Event_071181($_POST['eTitle'], $_POST['eDescription'], $_POST['eLocation'], $_POST['eStartDate'], $eStartTime, $_POST['eEndDate'], $eEndTime, $_POST['accessLevel'], $_POST['eID']);
  $event->updateEvent();
  $explodedDate = explode("-", $_POST['eStartDate']);
  $mmonth = $explodedDate[1];
  $yyear = $explodedDate[0];
}
if(isset($_GET['DeleteEventSubmitted']))
{
  $event = new Event_071181(NULL, NULL, NULL, $_GET['eStartDate'], NULL, NULL, NULL, $_GET['eID']);
  $event->deleteEvent();
  $explodedDate = explode("-", $_GET['eDate']);
  $mmonth = $explodedDate[1];
  $yyear = $explodedDate[0];
}

if(isset($_POST['AddExternalSubmitted']))
{
  $external = new External_071181($_POST['eType'], $_POST['eName'], $_POST['eAddress'], NULL);
  $external->addAddress();
  $mmonth = $_POST['mmonth'];
  $yyear = $_POST['yyear'];
}

if(isset($_POST['EditExternalSubmitted']))
{
  $external = new External_071181($_POST['eType'], $_POST['eName'], $_POST['eAddress'], $_POST['eID']);
  $external->updateAddress();
  $mmonth = $_POST['mmonth'];
  $yyear = $_POST['yyear'];
}

if(isset($_GET['DeleteExternalSubmitted']))
{
  $external = new External_071181(NULL, NULL, NULL, $_GET['id']);
  $external->deleteAddress();
  $mmonth = $_GET['month'];
  $yyear = $_GET['year'];
}

$mmonth = (isset($mmonth)) ? $mmonth : date("n",time());
$yyear = (isset($yyear)) ? $yyear : date("Y",time());
$ttoday = (isset($ttoday))? $ttoday : date("j", time());
$firstDay = date("Y-m-d", mktime(0,0,0,$mmonth,1,$yyear));
$lastDay = date("Y-m-t",mktime(0,0,0,$mmonth,1,$yyear));

$db = new DB_071181();
$display = new Display_071181($firstDay, $lastDay, $mmonth, $yyear);
$js = new JS_071181();
$management = new Management_071181($firstDay, $lastDay, $mmonth, $yyear);

function ManagementInit_071181()
{
  global $mmonth, $yyear, $firstDay, $lastDay;
  
  $management = new Management_071181($firstDay, $lastDay, $mmonth, $yyear);
  
  $options = get_option('widgetEventsCalendar');
  $accessLevel = $options['accessLevel'];
  
  add_submenu_page('edit.php', __('Events Calendar','events-calendar'), __('Events Calendar','events-calendar'), $accessLevel, basename(__FILE__), array(&$management, 'displayManagementPage'));
}
function widgetInit_071181()
{
  global $firstDay, $lastDay;
  $eDate = explode("-", $firstDay);
  $mmonth = $eDate[1];
  $yyear = $eDate[0];
  
  $management = new Management_071181($firstDay, $lastDay, $mmonth, $yyear);
  $display = new Display_071181($firstDay, $lastDay, $mmonth, $yyear);
  
  if( !function_exists('register_sidebar_widget') )
  {
    return;
  }
  
  register_sidebar_widget(__('Events Calendar','events-calendar'), array(&$display, 'displayWidget'));
  register_widget_control(__('Events Calendar','events-calendar'), array(&$management, 'displayWidgetControl'), 360, 360);
}
add_action('activate_events-calendar/events-calendar.php', array(&$db, 'createTable'));
add_action('admin_menu', 'ManagementInit_071181');
add_action('plugins_loaded', 'widgetInit_071181');
add_action('wp_head', array(&$js, 'addHeaderScript'));
add_action('wp_footer', array(&$js, 'printToolTipScript'));
add_action('admin_head', array(&$js, 'addHeaderScriptManagement'));
add_action('admin_footer', array(&$js, 'printToolTipScript'));
wp_enqueue_script('jquery');
endif;
?>
