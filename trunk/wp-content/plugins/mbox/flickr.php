<?php

  require(dirname(__FILE__).'/../../../' .'wp-config.php');
  
  if ($_GET["t"] != "") {
    $tags = '&tags='.$_GET["t"];
  }
  
  $o = mBox_get_options(); 
  
  $api = $o['flickr_key'];
  $user = $o['flickr_user'];
  $tagmode = $o['flickr_tagsmode'];
  $maximages = $o['maximages'];
  if ($user != "") {
    $user = '&user_id='.$user;
  }
  if ($tagmode != "" && $tagmode == "true") {
    $tagmode = '&tag_mode=all';
  } else {
    $tagmode = '&tag_mode=any';
  }
  if ($maximages != "" && $maximages > 0) {
    $maximages = '&per_page='.$maximages;
  }
  
  $rss_url = 'http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=' . $api . $user . $tags . $tagmode . $maximages;

  $id = 1;
  $output = '{"previews":[';
  
  if (function_exists('simplexml_load_file')) {        // PHP 5
    $photos = simplexml_load_file($rss_url);
    
    if ($photos) {      
    
      foreach ( $photos->photos->photo as $item ) {
      
        $secret = $item['secret'];
        $server = $item['server'];
        $farm = $item['farm'];
      
        $img = "http://farm$farm.static.flickr.com/$server/" . $item['id'] . "_" . $secret . ".jpg";

        $thumb = "http://farm$farm.static.flickr.com/$server/" . $item['id'] . "_" . $secret . "_s.jpg";
        $name = $item['title'];
        $desc = "http://www.flickr.com/photos/" . $item['owner'] . "/" . $item['id'] . "/";
      
        if ($id > 1) { $output .= ','; }
        $output .= '{"id":"'.$id.'", "title":"'.htmlentities($name).'", "src":"'.$img.'", "thumb":"'.$thumb.'", "desc":"'.$desc.'"}';       
        
        $id = $id + 1;

      } 
    
    }    
    
  } else {                                             // PHP 4 [using MiniXML Class]
    require_once('minixml/minixml.inc.php');
    
    if( function_exists('curl_init') ) {               // cURL esta disponible      
      $ch = curl_init($rss_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $xml = curl_exec($ch);
      curl_close($ch);
    } else {
      $xml = file_get_contents($rss_url);       
    }    
    $parsed = new MiniXMLDoc();
    $parsed->fromString($xml);
    $root =& $parsed->getRoot();
    $rsp =& $root->getElement('rsp');
    $stat =  $rsp->attribute('stat');
    if ($stat != "fail") { 
      $photos =& $root->getElement('photos');
      $photo =& $photos->getAllChildren();
      for($i = 0; $i < $photos->numChildren(); $i++) {      
        $secret = $photo[$i]->attribute('secret');
        $server = $photo[$i]->attribute('server');
        $farm = $photo[$i]->attribute('farm');
        $img = "http://farm$farm.static.flickr.com/$server/" . $photo[$i]->attribute('id') . "_" . $secret . ".jpg";
        $thumb = "http://farm$farm.static.flickr.com/$server/" . $photo[$i]->attribute('id') . "_" . $secret . "_s.jpg";
        $name = $photo[$i]->attribute('title');
        $desc = "http://www.flickr.com/photos/" . $photo[$i]->attribute('owner') . "/" . $photo[$i]->attribute('id') . "/";
    
        if ($id > 1) { $output .= ','; }
          $output .= '{"id":"'.$id.'", "title":"'.htmlentities($name).'", "src":"'.$img.'", "thumb":"'.$thumb.'", "desc":"'.$desc.'"}';       
      
        $id = $id + 1;
        
      }
    }
    
  }
  
  $output .= ']}';

  echo $output;
?> 
