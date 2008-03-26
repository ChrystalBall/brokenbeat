<?php
if( !class_exists("DB_071181") ) :

class DB_071181 {
  var $mainTable;
  var $externalTable;
  
  function DB_071181() {
    global $wpdb;
    
    $this->mainTable = $wpdb->prefix . "EventsCalendar_main";
    $this->externalTable = $wpdb->prefix . "EventsCalendar_external";
  }
  
  function createTable() {
    global $wpdb;

    if( $wpdb->get_var("SHOW TABLES LIKE `" . $this->mainTable . "`") != $this->mainTable) {
      $sql = "CREATE TABLE `" . $this->mainTable ."` ("
          ."`id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , "
          ."`eventTitle` VARCHAR( 255 ) NOT NULL , "
          ."`eventDescription` TEXT NOT NULL , "
          ."`eventLocation` VARCHAR( 255 ) NULL , "
          ."`eventStartDate` DATE NOT NULL , "
          ."`eventStartTime` TIME NULL , "
          ."`eventEndDate` DATE NOT NULL , "
          ."`eventEndTime` TIME NULL , "
          ."`accessLevel` VARCHAR( 255 ));";
          
      require_once(ABSPATH . "wp-admin/upgrade-functions.php");
      dbDelta($sql);
    }
    if(!$this->columnExists('accessLevel')) {
      $wpdb->query("ALTER TABLE `" . $this->mainTable . "` ADD `accessLevel` VARCHAR(255) NOT NULL DEFAULT 'public'");
    }
    
    if( $wpdb->get_var("SHOW TABLES LIKE `" . $this->externalTable . "`") != $this->externalTable) {
      $sql = "CREATE TABLE `" . $this->externalTable ."` ("
          ."`id` MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , "
          ."`externalType` VARCHAR( 255 ) NOT NULL , "
          ."`externalName` VARCHAR( 255 ) NOT NULL , "
          ."`externalAddress` VARCHAR( 255 ) NOT NULL);";
          
      require_once(ABSPATH . "wp-admin/upgrade-functions.php");
      dbDelta($sql);
    }
  }
  
  function columnExists($name) {
    global $wpdb;
    $colExists = false;
    $col = $wpdb->get_results("SHOW COLUMNS FROM `" . $this->mainTable . "`");
    foreach($cols as $col) {
      if($col['Field'] == $name) {
        return TRUE;
      }
    }
    return FALSE;
  }
  
  function addEntry($eTitle, $eDescription, $eLocation, $eStartDate, $eStartTime, $eEndDate, $eEndTime, $accessLevel, $eID) {
    global $wpdb;
    
    $sql = "INSERT INTO `$this->mainTable` "
        ."(`id`, `eventDescription`, `eventTitle`, `eventLocation`, `eventStartDate`, `eventStartTime`, `eventEndDate`, `eventEndTime`, `accessLevel`) "
        ."VALUES "
        ."('$eID', '".str_replace('"', '&quot;', addslashes($eDescription))."', '".str_replace('"', '&quot;', addslashes($eTitle))."', '".str_replace('"', '&quot;', addslashes($eLocation))."', '".str_replace('"', '&quot;', addslashes($eStartDate))."', '".str_replace('"', '&quot;', addslashes($eStartTime))."', '".str_replace('"', '&quot;', addslashes($eEndDate))."', '".str_replace('"', '&quot;', addslashes($eEndTime))."', '".$accessLevel."');";
    
    $wpdb->query($sql);
  }
  
  function addExternal($eType, $eName, $eAddress, $eID) {
    global $wpdb;
    
    $sql = "INSERT INTO `$this->externalTable` "
        ."(`id`, `externalType`, `externalName`, `externalAddress`) "
        ."VALUES "
        ."('$eID', '$eType', '$eName', '$eAddress');";
        
    $wpdb->query($sql);
  }

  function updateEntry($eTitle, $eDescription, $eLocation, $eStartDate, $eStartTime, $eEndDate, $eEndTime, $accessLevel, $eID) {
    global $wpdb;
    
    $sql = "UPDATE `$this->mainTable` SET "
        ."`eventTitle` = '".str_replace('"', '&quot;', addslashes($eTitle))."', "
        ."`eventDescription` = '".str_replace('"', '&quot;', addslashes($eDescription))."', "
        ."`eventLocation` = '".str_replace('"', '&quot;', addslashes($eLocation))."', "
        ."`eventStartDate` = '".str_replace('"', '&quot;', addslashes($eStartDate))."', "
        ."`eventStartTime` = '".str_replace('"', '&quot;', addslashes($eStartTime))."', "
        ."`eventEndDate` = '".str_replace('"', '&quot;', addslashes($eEndDate))."', "
        ."`eventEndTime` = '".str_replace('"', '&quot;', addslashes($eEndTime))."', "
        ."`accessLevel` = '".$accessLevel."' "
        ."WHERE `id` = '$eID' LIMIT 1;";

    $wpdb->query($sql);
  }
  
  function updateExternal($eType, $eName, $eAddress, $eID) {
    global $wpdb;
    
    $sql = "UPDATE `$this->externalTable` SET "
        ."`externalType` = '$eType', "
        ."`externalName` = '$eName', "
        ."`externalAddress` = '$eAddress' "
        ."WHERE `id` = '$eID' LIMIT 1;";
        
    $wpdb->query($sql);
  }
  
  function deleteEntry($eID) {
    global $wpdb;
    
    $sql = "DELETE FROM `$this->mainTable` "
        ."WHERE `id` = '$eID' LIMIT 1;";
    
    $wpdb->query($sql);
  }
  
  function deleteExternal($eID) {
    global $wpdb;
    
    $sql = "DELETE FROM `$this->externalTable` "
        ."WHERE `id` = '$eID' LIMIT 1;";
        
    $wpdb->query($sql);
  }
  
  function getEntry($eID) {
    global $wpdb;
    
    $sql = "SELECT * FROM `$this->mainTable` "
        ."WHERE `id` = '$eID' LIMIT 1;";
    
    return $wpdb->get_results($sql);
  }
  
  function getDateRange($eStartDate, $eEndDate) {
    global $wpdb;
    
    $sql = "SELECT * FROM `$this->mainTable` "
        ."WHERE (`eventStartDate` >= '$eStartDate' AND `eventStartDate` <= '$eEndDate') OR (`eventEndDate` >= '$eStartDate' AND `eventEndDate` <= '$eEndDate') ORDER BY `eventStartDate` ASC, `eventStartTime` ASC, `eventEndTime` ASC;";
    
    return $wpdb->get_results($sql);
  }
  
  function getExternalCalendar($eID) {
    global $wpdb;
    
    $sql = "SELECT * FROM `$this->externalTable` "
        ."WHERE `id` = '$eID' LIMIT 1;";
       
    return $wpdb->get_results($sql);
  }
  
  function getExternalCalendarList() {
    global $wpdb;
    
    $sql = "SELECT * FROM `$this->externalTable` ORDER BY `externalType` ASC, `externalName` ASC;";
    
    return $wpdb->get_results($sql);
  }
  
  function getUpcoming($count) {
    global $wpdb;
    
    $sql = "SELECT * FROM `$this->mainTable` "
        ."WHERE `eventStartDate` >= '". date('Y-m-d', mktime(0,0,0,date("m"),date("d"),date("Y"))) ."' ORDER BY `eventStartDate` ASC, `eventStartTime` ASC, `eventEndTime` ASC LIMIT " . (int) $count;
    
    return $wpdb->get_results($sql);
  }
}
endif;
?>
