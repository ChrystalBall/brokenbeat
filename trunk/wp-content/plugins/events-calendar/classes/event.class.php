<?php
require_once(EVENTSCALENDARPATH . "classes/db.class.php");

if( !class_exists("Event_071181") ) :

class Event_071181
{
  var $eventID;
  var $eventTitle;
  var $eventDescription;
  var $eventLocation;
  var $eventStartDate;
  var $eventStartTime;
  var $eventEndDate;
  var $eventEndTime;
  
  function Event_071181($eTitle, $eDescription, $eLocation, $eStartDate, $eStartTime, $eEndDate, $eEndTime, $accessLevel, $eID)
  {
    $this->eventTitle = $eTitle;
    $this->eventDescription = $eDescription;
    $this->eventLocation = $eLocation;
    $this->eventStartDate = $eStartDate;
    $this->eventStartTime = $eStartTime;
    $this->eventEndDate = $eEndDate;
    $this->eventEndTime = $eEndTime;
    $this->accessLevel = $accessLevel;
    $this->eventID = $eID;
  }
  
  function addEvent()
  {
    $db = new DB_071181();
    $db->addEntry($this->eventTitle, $this->eventDescription, $this->eventLocation, $this->eventStartDate, $this->eventStartTime, $this->eventEndDate, $this->eventEndTime, $this->accessLevel, NULL);
  }
  
  function updateEvent()
  {
    $db = new DB_071181();
    $db->updateEntry($this->eventTitle, $this->eventDescription, $this->eventLocation, $this->eventStartDate, $this->eventStartTime, $this->eventEndDate, $this->eventEndTime, $this->accessLevel, $this->eventID);
  }
  
  function deleteEvent()
  {
    $db = new DB_071181();
    $db->deleteEntry($this->eventID);
  }
}

endif;

?>
