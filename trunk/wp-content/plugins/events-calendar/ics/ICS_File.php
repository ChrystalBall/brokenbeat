<?php
/*  ICS Parser
  Copyright (C) 2008 Jeremy Austin-Bardo <tjaustinbardo@gmail.com>
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
class ICS_FiletoArray{
  /* Load an ICS file from the Internet for storage and retrieval locally.
  **
  ** $obj = new ICS_FiletoArray($url); creates and load the file object.
  ** $line = $obj->line(); return next line of file object.
  ** $check = $obj->check(); return whether at last line of file object. 
  ** $obj->reset(); reset the file array to read again.
  */
  var $Index, $Contents;
  function __construct($Url){
    /* Check ICS file in cache and update if its older or not exists 
    ** Sets $Contents array and $Index variable for later usage
    */
    /////$file = ICS_CACHE."/".md5($Url);
    // Is ICS file cached? If not cache the contents.
    /////if (!file_exists($file)) {
      /////if (ICS_DEBUG) echo "Creating cache for ".$Url.".<br/>";
      /////$this->cache($Url) or die('Fatal Cache Error.');
    /////}
    // Has ICS file updated? If not cache the contents.
    /////if (ICS_MAXAGE < time()-filemtime($file)) {
      /////if (ICS_DEBUG) echo "Updating cache for ".$Url.".<br/>";
      /////$this->cache($Url) or die('Fatal Cache Error.');
    /////}
    // Set content from file and array index.
    $this->Contents = $this->fetch($Url);
    $this->Index = 0;
  }
  function cache($Url){
    /* Write ICS file to local cache directory.
    ** Return the success of local caching. 
    */
    /////$file = ICS_CACHE."/".md5($Url);
    // Open ICS file cache, check for file errors.
    if (!$fp = @fopen($file,'w')) {
      if (ICS_DEBUG) echo "Cannot Write Cache for ".$Url."<br/>";
      fclose($fp);
      return FALSE;
    }
    // Write ICS file to local cache.
    fwrite($fp,join("",$this->fetch($Url)));
    fclose($fp);
    return TRUE;
  }   
  function fetch($Url){    
    /* Read ICS file and verify its header format. 
    ** Return on succes the file content, on failure FALSE.
    */
    $contents=file($Url);  
    // Verify actually it is an ICS file.
    if (trim(strtoupper($contents[0])) != "BEGIN:VCALENDAR") {
      if (ICS_DEBUG) echo "Cannot Validate File for ".$Url."<br/>"; 
      return FALSE;
    }
    return $contents;
  }
  function line(){
    /* Read ICS File line and increment file contents array 
    ** Return next full line of content by combining line parts.
    */
    $line = preg_replace("/[\r\n]/","",$this->Contents[$this->Index]);
    //$line = $this->Contents[$this->Index];
    $this->Index++;
    $line_next = preg_replace("/[\r\n]/","",$this->Contents[$this->Index]);
    //$line_next = $this->Contents[$this->Index];
    // Is a this multi-line part which begin with a space? Combine each part.
    while (substr($line_next,0,1)==" ") {
      $this->Index++; 
      $line .= substr($line_next,1);
      $line_next = preg_replace("/[\r\n]/"," ",$this->Contents[$this->Index]);
      //$line_next = $this->Contents[$this->Index];
    }
    return $line;
  }
  function reset(){
    $this->Index=0;
  }
  function check(){
    /* Check ICS File for valid footer format for read termination.
    ** Return the existence of the valid ICS footer.
    */
    if (trim(strtoupper($this->Contents[$this->Index])) == 'END:VCALENDAR') {
      return FALSE;
    } else {
      return TRUE;
}   }   }
?>
