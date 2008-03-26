<?php

  require(dirname(__FILE__).'/../../../' .'wp-config.php');

  global $table_prefix;
  
  $connexion = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD) or die("Can't connect.<br />".mysql_error());
  $dbconnexion = mysql_select_db(DB_NAME, $connexion);	
	
	if (!$dbconnexion) {
		echo mysql_error();
	  die();
	}
	
	$id = $_GET["id"];

	$scriptdir = trailingslashit(get_settings('siteurl')) . 'wp-content/plugins/mBox';
	
	$sql = "SELECT p.post_title, p.post_content, p.guid, m.meta_value "
	     . "  FROM `".$table_prefix."posts` p INNER JOIN `".$table_prefix."postmeta` m ON m.post_id = p.ID "
	     . " WHERE p.post_parent = '".$id."' "
	     . "   AND p.post_status IN ('inherit') "
	     . "   AND p.post_mime_type LIKE 'image/%' "
	     . "   AND m.meta_key = '_wp_attached_file' ";

	$results = mysql_query($sql);
	
	if ($results) {	
	
	  $id = 1;
	  $output = '{"previews":[';
	
	  while ($row = mysql_fetch_assoc($results)) {
	    
	    $img = $row['guid'];
	    $name = $row['post_title'];
	    $desc = $row['post_content'];

      $thumb = str_replace('.jpg', '.thumbnail.jpg', $row['meta_value']);  

      if(!file_exists($thumb)) {
        $thumb = str_replace('.jpg', '-150x150.jpg', $row['meta_value']);   // Wordpress 2.5 thumbnail
        if(!file_exists($thumb)) {
          $thumb = $scriptdir.'/img/noimage.gif';
        } else {
          $thumb = str_replace('.jpg', '-150x150.jpg', $img);
        }
      } else {
      	$thumb = str_replace('.jpg', '.thumbnail.jpg', $img);
      }

      if ($id > 1) { $output .= ','; }
      $output .= '{"id":"'.$id.'", "title":"'.htmlentities($name).'", "src":"'.$img.'", "thumb":"'.$thumb.'", "desc":"'.$desc.'"}';			 
      
      $id = $id + 1;
	    
	  }
	  
	  $output .= ']}';
	  
	}

  echo $output;

?> 
