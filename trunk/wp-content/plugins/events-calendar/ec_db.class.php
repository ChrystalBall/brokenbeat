<?php
if(!class_exists('EC_DB')):

class EC_DB {
  var $db;
  var $mainTable;
  var $dbVersion;
  
  function EC_DB() {
    global $wpdb;
    $this->dbVersion = "106";
    $this->db = $wpdb;
    $this->mainTable = $this->db->prefix . 'eventscalendar_main';
    $this->mainTableCaps = $this->db->prefix . 'EventsCalendar_main';
    $this->postsTable = $this->db->prefix . 'posts';
    if($this->db->get_var("show tables like '$this->mainTableCaps'") == $this->mainTableCaps )
      $this->mainTable = $this->mainTableCaps;
  }
  
  function createTable() {
    if($this->db->get_var("show tables like '$this->mainTable'") != $this->mainTable ) {
      $sql = "CREATE TABLE " . $this->mainTable . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            eventTitle varchar(255) CHARACTER SET utf8 NOT NULL,
            eventDescription text CHARACTER SET utf8 NOT NULL,
            eventLocation varchar(255) CHARACTER SET utf8 default NULL,
            eventStartDate date NOT NULL,
            eventStartTime time default NULL,
            eventEndDate date NOT NULL,
            eventEndTime time default NULL,
            accessLevel varchar(255) CHARACTER SET utf8 NOT NULL default 'public',
            postID mediumint(9) NULL DEFAULT NULL,
            PRIMARY KEY  id (id)
            );";
      require_once(ABSPATH . "wp-admin/upgrade-functions.php");
      dbDelta($sql);
      
      add_option("events_calendar_db_version", $this->dbVersion);
    }

    
    $installed_ver = get_option( "eventscalendar_db_version" );

    if($installed_ver != $this->dbVersion) {
      $sql = "CREATE TABLE " . $this->mainTable . " (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            eventTitle varchar(255) CHARACTER SET utf8 NOT NULL,
            eventDescription text CHARACTER SET utf8 NOT NULL,
            eventLocation varchar(255) CHARACTER SET utf8 default NULL,
            eventStartDate date NOT NULL,
            eventStartTime time default NULL,
            eventEndDate date NOT NULL,
            eventEndTime time default NULL,
            accessLevel varchar(255) CHARACTER SET utf8 NOT NULL default 'public',
            postID mediumint(9) NULL DEFAULT NULL,
            PRIMARY KEY  id (id)
            );";
      require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
      dbDelta($sql);
      
      $this->db->query("UPDATE " . $this->mainTable . " SET `eventLocation` = REPLACE(`eventLocation`,' ','');");
      $this->db->query("UPDATE " . $this->mainTable . " SET `eventLocation` = REPLACE(`eventLocation`,'',NULL);");
      $this->db->query("UPDATE " . $this->mainTable . " SET `eventStartTime` = REPLACE(`eventStartTime`,'00:00:00',NULL);");
      $this->db->query("UPDATE " . $this->mainTable . " SET `eventEndTime` = REPLACE(`eventEndTime`,'00:00:00',NULL);");

      update_option( "events_calendar_db_version", $this->dbVersion);
    }
  }
  
  function addEvent($title, $location, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel, $postID) {
    $postID = is_null($postID) ? "NULL" : "'$postID'";
    $location = is_null($location) ? "NULL" : "'$location'";
    $startTime = is_null($startTime) ? "NULL" : "'$startTime'";
    $endTime = is_null($endTime) ? "NULL" : "'$endTime'";
    $sql = "INSERT INTO `$this->mainTable` ("
          ."`id`, `eventTitle`, `eventDescription`, `eventLocation`, `eventStartDate`, `eventStartTime`, `eventEndDate`, `eventEndTime`, `accessLevel`, `postID`) "
          ."VALUES ("
          ."NULL , '$title', '$description', $location, '$startDate', $startTime, '$endDate', $endTime , '$accessLevel', $postID);";
    $this->db->query($sql);
  }
  
  function editEvent($id, $title, $location, $description, $startDate, $startTime, $endDate, $endTime, $accessLevel) {
    $location = is_null($location) ? "NULL" : "'$location'";
    $startTime = is_null($startTime) ? "NULL" : "'$startTime'";
    $endTime = is_null($endTime) ? "NULL" : "'$endTime'";
    $sql = "UPDATE `$this->mainTable` SET "
          ."`eventTitle` = '$title', "
          ."`eventDescription` = '$description', "
          ."`eventLocation` = $location, "
          ."`eventStartDate` = '$startDate', "
          ."`eventStartTime` = $startTime, "
          ."`eventEndDate` = '$endDate', "
          ."`eventEndTime` = $endTime, "
          ."`accessLevel` = '$accessLevel' WHERE `id` = $id LIMIT 1;";
    $this->db->query($sql);
  }
  
  function deleteEvent($id) {
    $sql = "DELETE FROM `$this->mainTable` WHERE `id` = $id LIMIT 1;";
    $this->db->query($sql);  
  }
  
  function getDaysEvents($d) {
    $sql = "SELECT * FROM `$this->mainTable` WHERE `eventStartDate` <= '$d' AND `eventEndDate` >= '$d' ORDER BY `eventStartTime`, `eventEndTime`;";
    return $this->db->get_results($sql);
  }
  
  function getEvent($id) {
    $sql = "SELECT * FROM `$this->mainTable` WHERE `id` = $id LIMIT 1;";
    return $this->db->get_results($sql);
  }
  
  function getUpcomingEvents($num) {
    $sql = "SELECT * FROM `$this->mainTable` WHERE `eventStartDate` >= '" . date('Y-m-d') . "' OR `eventEndDate` >= '" . date('Y-m-d') . "' ORDER BY eventStartDate, eventStartTime LIMIT $num";
    return $this->db->get_results($sql);
  }
  
  function getLatestPost() {
    $sql = "SELECT `id` FROM `$this->postsTable` ORDER BY `id` DESC LIMIT 1;";
    return $this->db->get_results($sql);
  }
}
endif;
?>