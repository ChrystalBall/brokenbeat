<?php
/*
Plugin Name: Events Calendar
Plugin URI: http://www.lukehowell.com/events-calendar/
Description: There are options under the widget options to specify the view of the calendar in the sidebar.  The widget can be a list for upcoming events or a calendar.  If you do not have a widget ready theme then you can place '&lt;?php sidebarEventsCalendar();?&gt;' in the sidebar file.  If you want to display a large calendar in a post or a page, simply place "[[EventsCalendarLarge]]" in the html of the post or page.  Make sure to leave off the quotes.
Version: 6.4
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
define('EVENTSCALENDARPATH', ABSPATH.'wp-content/plugins/events-calendar');
define('EVENTSCALENDARCLASSPATH', EVENTSCALENDARPATH);
define('EVENTSCALENDARURL', get_option('siteurl').'/wp-content/plugins/events-calendar');
define('EVENTSCALENDARJSURL', EVENTSCALENDARURL.'/js');
define('EVENTSCALENDARCSSURL', EVENTSCALENDARURL.'/css');
define('EVENTSCALENDARIMAGESURL', EVENTSCALENDARURL . '/images');

require_once(EVENTSCALENDARCLASSPATH . '/ec_day.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_calendar.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_db.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_widget.class.php');
require_once(EVENTSCALENDARCLASSPATH . '/ec_management.class.php');

/* Added for localisation by Heirem -------------------------------------------***/
load_plugin_textdomain('events-calendar','wp-content/plugins/events-calendar/lang');
$locale = get_locale(); $setloc = explode("_",$locale); $setloc = $setloc[0];
setlocale(LC_TIME, $setloc); // setlocale(LC_TIME, 'fr');
/***---------------------------------------------------------------------------***/

if(isset($_GET['EC_view']) && $_GET['EC_view'] == 'day') {
  $EC_date = date('Y-m-d', mktime(0, 0, 0, $_GET['EC_month'], $_GET['EC_day'], $_GET['EC_year']));
  $day = new EC_Day();
  $day->display($EC_date);
  exit();
}

if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'switchMonth') {
  $calendar = new EC_Calendar();
  $calendar->displayWidget($_GET['EC_year'], $_GET['EC_month']);
  exit();
}

if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'switchMonthLarge') {
  $calendar = new EC_Calendar();
  $calendar->displayLarge($_GET['EC_year'], $_GET['EC_month']);
  exit();
}

if(isset($_GET['EC_action']) && $_GET['EC_action'] == 'ajaxDelete') {
  $db = new EC_DB();
  $db->deleteEvent($_GET['EC_id']);
  exit();
}

function EventsCalendarINIT() {
  wp_enqueue_script('jquerydimensions', '/wp-content/plugins/events-calendar/js/jquery.dimensions.min.js', array('jquery'), '1.2');
  wp_enqueue_script('jquerytooltip', '/wp-content/plugins/events-calendar/js/jquery.tooltip.min.js', array('jquery'), '1.2');
  wp_enqueue_script('thickbox');
  $widget = new EC_Widget();
  $management = new EC_Management();
  if(!function_exists('register_sidebar_widget')) return;
  register_sidebar_widget(__('Events Calendar','events-calendar'), array(&$widget, 'display'));
  register_widget_control(__('Events Calendar','events-calendar'), array(&$management, 'widgetControl'));
}

function EventsCalendarManagementINIT() {
  if(isset($_GET['page']) && $_GET['page'] == 'events-calendar') {
    wp_enqueue_script('jquerydimensions', '/wp-content/plugins/events-calendar/js/jquery.dimensions.min.js', array('jquery'), '1.2');
    wp_enqueue_script('jquerytooltip', '/wp-content/plugins/events-calendar/js/jquery.tooltip.min.js', array('jquery'), '1.2');
    wp_enqueue_script('jquerydatepicker', '/wp-content/plugins/events-calendar/js/jquery.datepicker.min.js', array('jquery'), '3.4.3');
    wp_enqueue_script('jquerytimepicker', '/wp-content/plugins/events-calendar/js/jquery.timepicker.min.js', array('jquery'), '0.1');
  }
  $options = get_option('optionsEventsCalendar');
  $EC_userLevel = isset($options['accessLevel']) && !empty($options['accessLevel']) ? $options['accessLevel'] : 'level_10';
  $management = new EC_management();
  /* Added for localisation by Heirem -------------------------------------------***/
  add_submenu_page('events-calendar', __('Events Calendar','events-calendar'), __('Calendar','events-calendar'), $EC_userLevel,  'events-calendar', array(&$management, 'calendarOptions'));
  add_menu_page(__('Events Calendar','events-calendar'), __('Events Calendar','events-calendar'), $EC_userLevel, 'events-calendar', array(&$management, 'display'));
  add_submenu_page('events-calendar', __('Events Calendar Options','events-calendar'), __('Options','events-calendar'), $EC_userLevel, 'events-calendar-options', array(&$management, 'calendarOptions'));
  /***---------------------------------------------------------------------------***/
}
  

function EventsCalendarHeaderScript() {
?>
  <link type="text/css" rel="stylesheet" href="<?php echo get_option('siteurl');?>/wp-includes/js/thickbox/thickbox.css?1" />
  <link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/events-calendar.css" />
<?php
}

function EventsCalendarAdminHeaderScript() {
  if(isset($_GET['page']) && $_GET['page'] == 'events-calendar') {
?>
  <link type="text/css" rel="stylesheet" href="<?php echo EVENTSCALENDARCSSURL;?>/events-calendar-management.css" />
<?php
  }
}

function EventsCalendarActivated() {
  $db = new EC_DB();
  $db->createTable();
}

function filterEventsCalendarLarge($content) {
  if(preg_match("[EventsCalendarLarge]",$content)) {
    $calendar = new EC_Calendar();  
    $content = str_replace("[[EventsCalendarLarge]]", $calendar->displayLarge(date('Y'), date('m')), $content);
  }
  return $content;
}

function SidebarEventsCalendar() {
  $calendar = new EC_Calendar();
  $calendar->displayWidget(date('Y'), date('m'));
}
add_action('activate_events-calendar/events-calendar.php', 'EventsCalendarActivated');
add_action('plugins_loaded', 'EventsCalendarINIT');
add_action('admin_menu', 'EventsCalendarManagementINIT');
add_action('wp_head', 'EventsCalendarHeaderScript');
add_action('admin_head', 'EventsCalendarAdminHeaderScript');
add_filter('the_content', 'filterEventsCalendarLarge');
?>
