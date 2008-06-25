<?php

  require(dirname(__FILE__).'/../../../' .'wp-config.php');

	$p = ($_GET["p"][strlen($_GET["p"])-1]=='/') ? $_GET["p"] : $_GET["p"].'/';
	
	$base = $_SERVER["DOCUMENT_ROOT"];
	$path = $base.$p;
  $o = mBox_get_options(); 	
	$scriptdir = trailingslashit(get_settings('siteurl')) . 'wp-content/plugins/mBox';

  $results = array();
  
  $limit = ($o['maximages'] != "" && $o['maximages'] > 0) ? $o['maximages'] : 0;
  
  $d = dir($path);
  while($file=$d->read()) {
    if ($file == "." || $file == ".." || $file == ".DS_Store" || $file == "Thumbs.db" || !eregi("(\.jpg|\.gif|\.png)$", $file)) continue;    
    $fullpath = $path . $file;
    $fkey = strtolower($file);
    $results[$fkey]['img'] = $file;
    $results[$fkey]['name'] = substr($file, 0, strrpos($file, '.'));
    $results[$fkey]['desc'] = readInfo($path . 'thumbs/' . $results[$fkey]['name'] . '.txt');
  }
  $d->close();
  
  sort($results);
	
	if ($results) {	
	
	  $id = 1;
	  $output = '{"previews":[';
	  
	  foreach ($results as $item ) {
	    
	    if ($limit <= 0 || $id <= $limit) {
	    
        $img = $item['img'];
        $name = $item['name'];
        $desc = $item['desc'];
        
        if(file_exists($path . 'thumbs/' . $item['img'])) {   
          $thumb = 'thumbs/' . $item['img']; 
        } else {  
          $thumb = '';
        }

        if ($id > 1) { $output .= ','; }
        $output .= '{"id":"'.$id.'", "title":"'.htmlentities($name).'", "src":"'.$img.'", "thumb":"'.$thumb.'", "desc":"'.$desc.'"}';			 
        
        $id = $id + 1;
      
      }
	    
	  }
	  
	  $output .= ']}';
	  
	}

  echo $output;

?> 



<?php 

// Functions ///////////////////////////

function readInfo($file) {

  $txt = '';
  if (file_exists($file)) {
    $fh = fopen($file, 'r');
    while (!feof($fh)) {
      $txt .= fgets($fh);
    }
    fclose($fh);      
  }  
  return htmlentities($txt);
  
}

?>
