<?php
require_once(EVENTSCALENDARPATH . "classes/db.class.php");

if( !class_exists("External_071181") ) :

class External_071181
{
  var $externalID;
  var $externalType;
  var $externalName;
  var $externalAddress;
  
  function External_071181($eType, $eName, $eAddress, $eID)
  {
    $this->externalID = $eID;
    $this->externalType = $eType;
    $this->externalName = $eName;
    $this->externalAddress = $eAddress;
  }
  
  function addAddress()
  {
    $db = new DB_071181();
    $db->addExternal($this->externalType, $this->externalName, $this->externalAddress, $this->externalID);
  }
  
  function updateAddress()
  {
    $db = new DB_071181();
    $db->updateExternal($this->externalType, $this->externalName, $this->externalAddress, $this->externalID);
  }
  
  function deleteAddress()
  {
    $db = new DB_071181();
    $db->deleteExternal($this->externalID);
  }
}

endif;

?>
