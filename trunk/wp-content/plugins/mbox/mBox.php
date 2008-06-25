<?php

/*
Plugin Name: mBox
Version: 1.7.6
Plugin URI: http://www.hnkweb.com/code/mbox
Description: mBox allows you to easy include slideshow galleries into posts and pages using the images uploaded, flickr account or a folder.
Author: Hanok
Author URI: http://www.hnkweb.com/
Update: 25/04/2008
*/

// SCRIPT INFO ///////////////////////////////////////////////////////////////////////////

/*
  mBox for Wordpress
  
  Copyright 2008  Hanok (hanokmail@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

  This plugin includes:
    MooTools 1.11
    More info at: http://mootools.net/

  This Wordpress plugin has been tested with Wordpress 2.3.1;

*/  

/////////////////////////////////////////////////////////////////////////////////////////


// NO EDITING HERE!!!!! ////////////////////////////////////////////////////////////////
  
  add_filter('the_content', 'mBox', 1);
  
  add_action('wp_head', 'mBox_head');
  add_action('admin_menu', 'mBox_admin_menu');  
  
  function mBox_head() {
    $URLplug = trailingslashit(get_settings('siteurl')) . 'wp-content/plugins/mbox';
    $o = mBox_get_options(); 
    $theme = ($o['theme'] == '') ? 'default' : $o['theme'];
    echo "<!-- Added by mBox -->
            <link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"$URLplug/css/$theme.css\" />";
    if ($o['slimbox'] == 'true') { 
      echo "<link rel=\"stylesheet\" type=\"text/css\" media=\"screen\" href=\"$URLplug/css/slimbox/slimbox.css\" />";
    }            
    
    if ($o['secure_js'] == 'true') {
      if ($o['mootools'] == 'true') { 
        echo "<script type=\"text/javascript\" src=\"$URLplug/js/mootools.base.js\"></script>";
      }
      if ($o['effects'] == 'true') { 
        echo "<script type=\"text/javascript\" src=\"$URLplug/js/mootools.effects.js\"></script>";
      }
      if ($o['remote'] == 'true') { 
        echo "<script type=\"text/javascript\" src=\"$URLplug/js/mootools.remote.js\"></script>";
      }
      if ($o['slimbox'] == 'true') { 
        echo "<script type=\"text/javascript\" src=\"$URLplug/js/slimbox.compressed.js\"></script>";
      }
      echo "<script type=\"text/javascript\" src=\"$URLplug/js/mbox.compressed.js\"></script>";
    } else {
      echo "<script type=\"text/javascript\" src=\"$URLplug/js/js.php\"></script>";
    }        
    echo "<!-- End mBox -->";
  }
  
  function mBox_admin_menu(){
    add_options_page('mBox, options page', 'mBox', 9, basename(__FILE__), 'mBox_options_page');
  }
  
  if(strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'post-new.php') ||  strpos($_SERVER['REQUEST_URI'], 'page-new.php') ) {
    add_action('admin_footer', 'mBoxbutton');    
  }


  function mBox($content) {
  
    global $post;

    $URLplug = trailingslashit(get_settings('siteurl')) . 'wp-content/plugins/mbox';
    $siteurl = get_settings('siteurl');
    $base =  str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME']);    

    preg_match_all ('!<mBox([^>]*)[ ]*[/]*>!imU', $content, $matches);

    if (count($matches[1]) > 0) {

      $template = "<!-- Added by mBox -->
               <div id=\"mbox-$post->ID\" class=\"box\"></div>
               <p style=\"display: none;\"><script type=\"text/javascript\">
               <!--               
                   window.addEvent(\"domready\", function() {                   
                     new mBox({dir: \"$base\", siteurl: \"$siteurl\", id: \"$post->ID\", post: \"$post->ID\", %PARAM%});
                   });
               --></script></p>
               <!-- End mBox -->";

      $strings = $matches[0];
      $attributes = $matches[1];

      for ($i = 0; $i < count($attributes); $i++){

        $params = array();

        preg_match_all('!(gal|showthumbs|download|zoom|autostart|mode|width|height|help|alert|flickr_tags|folder)="([^"]*)"!i',$attributes[$i], $matches);
        
        for ($j = 0; $j < count($matches[1]); $j++){ 
          $vars[$j] = $matches[1][$j];
          $vals[$j] = $matches[2][$j];
          $params[] = $matches[1][$j].': "'.$matches[2][$j].'"';
        }
        
        $content = str_replace($strings[$i], str_replace('%PARAM%', join(', ', $params), $template), $content);
        
      }
    
    }
    
    return $content;
    
  }

  function mBox_get_options(){
    $defaults = array();
    $defaults['quicktags'] = 1;
    $defaults['sort_column'] = 'title';
    $defaults['sort_order'] = 'ASC';
    $defaults['theme'] = 'default';
    $defaults['mootools'] = 1;
    $defaults['effects'] = 1;
    $defaults['remote'] = 1;
    $defaults['slimbox'] = 1;
    $defaults['secure_js'] = 1;
    $defaults['autostart'] = 1;
    $defaults['width'] = '600';
    $defaults['height'] = '450';
    $defaults['maximages'] = '0';
    $defaults['help'] = 'Puedes usar las flechas de direcci&oacute;n para moverte por las im&aacute;genes.';
    $defaults['alert'] = 'No se han encontrado im&aacute;genes.';
    $defaults['flickr'] = 0;
    $defaults['flickr_tagsmode'] = 0;
    $defaults['folder'] = 0;

    $options = get_option('mBox_settings');
    if (!is_array($options)){
      $options = $defaults;
      update_option('mBox_settings', $options);
    }

    return $options;
  }
  
  function mBox_options_page(){
    if ($_POST['mBox']){
      update_option('mBox_settings', $_POST['mBox']);
      $message = '<div class="updated"><p><strong>Options saved.</strong></p></div>';
    }

    $o = mBox_get_options();    

    $qtyes = ($o['quicktags'] == 'true') ? ' checked="checked"' : '';
    $qtno = ($o['quicktags'] == 'true') ? '' : ' checked="checked"';
    
    $myes = ($o['mootools'] == 'true') ? ' checked="checked"' : '';
    $mno = ($o['mootools'] == 'true') ? '' : ' checked="checked"';
    
    $eyes = ($o['effects'] == 'true') ? ' checked="checked"' : '';
    $eno = ($o['effects'] == 'true') ? '' : ' checked="checked"';
    
    $ryes = ($o['remote'] == 'true') ? ' checked="checked"' : '';
    $rno = ($o['remote'] == 'true') ? '' : ' checked="checked"';
    
    $byes = ($o['slimbox'] == 'true') ? ' checked="checked"' : '';
    $bno = ($o['slimbox'] == 'true') ? '' : ' checked="checked"';
    
    $jyes = ($o['secure_js'] == 'true') ? ' checked="checked"' : '';
    $jno = ($o['secure_js'] == 'true') ? '' : ' checked="checked"';
    
    $syes = ($o['autostart'] == 'true') ? ' checked="checked"' : '';
    $sno = ($o['autostart'] == 'true') ? '' : ' checked="checked"';
    
    $flickr = ($o['flickr'] == 'true') ? ' "checked"' : '';
    $tmand = ($o['flickr_tagsmode'] == 'true') ? ' checked="checked"' : '';
    $tmor = ($o['flickr_tagsmode'] == 'true') ? '' : ' checked="checked"';
    
    $folder = ($o['folder'] == 'true') ? ' "checked"' : '';
    
    ?>
    <div class="wrap">
      <h2>mBox Options</h2>
      <?php echo $message; ?>
      <form name="form1" method="post" action="options-general.php?page=mBox.php">
        <h3>General</h3>
        <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
          <tr valign="top">
            <th>Show Quicktag</th>
            <td>
              <input type="radio" value="true" name="mBox[quicktags]"<?php echo $qtyes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[quicktags]"<?php echo $qtno; ?> /> no
            </td>
          </tr>
          <tr valign="top">
            <th>Autostart Slideshow</th>
            <td>
              <input type="radio" value="true" name="mBox[autostart]"<?php echo $syes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[autostart]"<?php echo $sno; ?> /> no
            </td>
          </tr>
          <tr valign="top">
            <th>Default Width</th>
            <td>
              <input type="text" value="<?php echo $o['width']; ?>" name="mBox[width]" size="3" maxlength="4" />px
            </td>
          </tr>
          <tr valign="top">
            <th>Default Height</th>
            <td>
              <input type="text" value="<?php echo $o['height']; ?>" name="mBox[height]" size="3" maxlength="4" />px
            </td>
          </tr>    
          <tr valign="top">
            <th>Help [Text after the slideshow]</th>
            <td>
              <input type="text" value="<?php echo $o['help']; ?>" name="mBox[help]" size="50" maxlength="255" />
            </td>
          </tr>  
          <tr valign="top">
            <th>Alert [Text to show if no images found]</th>
            <td>
              <input type="text" value="<?php echo $o['alert']; ?>" name="mBox[alert]" size="50" maxlength="255" />
            </td>
          </tr>
          <tr valign="top">
            <th>Max. Numer of Images</th>
            <td>
              <input type="text" value="<?php echo $o['maximages']; ?>" name="mBox[maximages]" size="3" maxlength="4" />
            </td>
          </tr>  
          <tr valign="top">
            <th>Sort Images</th>
            <td>
              <select name="mBox[sort_column]">
                <option value="date"<?php if ($o['sort_column']=='date') { echo ' selected'; } ?>>date</option>
                <option value="name"<?php if ($o['sort_column']=='name') { echo ' selected'; } ?>>name</option>
                <option value="title"<?php if ($o['sort_column']=='title'||$o['sort_column']=='') { echo ' selected'; } ?>>title</option>
              </select>
              <select name="mBox[sort_order]">
                <option value="ASC"<?php if ($o['sort_order']=='ASC'||$o['sort_order']=='') { echo ' selected'; } ?>>ASC</option>
                <option value="DESC"<?php if ($o['sort_order']=='DESC') { echo ' selected'; } ?>>DESC</option>
              </select>
            </td>
          </tr>
          <tr valign="top">
            <th>Theme</th>
            <td>
              <select name="mBox[theme]">
                <?php
                $d = dir(ABSPATH . PLUGINDIR .'/mbox/css');
                while($file=$d->read()) {
                  if ($file == "." || $file == ".." || $file == ".DS_Store" || $file == "Thumbs.db" || !eregi("(\.css)$", $file)) continue;    
                  $theme = substr($file, 0, strrpos($file, '.'));
                  $selected = ($o['theme'] == $theme || ($o['theme'] == '' && $theme == 'default')) ? ' selected' : '';
                  echo "<option value=\"$theme\"$selected>$theme</option>";
                }
                $d->close();
                ?>              
              </select>
            </td>
          </tr>        
        </table>
        <h3>Mootools Components</h3>
        <table width="100%" cellspacing="2" cellpadding="5" class="form-table">
          <tr valign="top">
            <th width="33%">Load MooTools Base</th>
            <td>
              <input type="radio" value="true" name="mBox[mootools]"<?php echo $myes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[mootools]"<?php echo $mno; ?> /> no
            </td>
          </tr>
          <tr valign="top">
            <th width="33%">Load Effects Components</th>
            <td>
              <input type="radio" value="true" name="mBox[effects]"<?php echo $eyes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[effects]"<?php echo $eno; ?> /> no
            </td>
          </tr>
          <tr valign="top">
            <th width="33%">Load Remote Components</th>
            <td>
              <input type="radio" value="true" name="mBox[remote]"<?php echo $ryes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[remote]"<?php echo $rno; ?> /> no
            </td>
          </tr>
          <tr valign="top">
            <th width="33%">Load Slimbox Components [Zoom Option]</th>
            <td>
              <input type="radio" value="true" name="mBox[slimbox]"<?php echo $byes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[slimbox]"<?php echo $bno; ?> /> no
              Use <code>zoom="true"</code> parameter in your mBox call.
            </td>
          </tr>  
          <tr valign="top">
            <th width="33%">Use secure mode to load JS files [no PHP packed]</th>
            <td>
              <input type="radio" value="true" name="mBox[secure_js]"<?php echo $jyes; ?> /> yes<br />
              <input type="radio" value="false" name="mBox[secure_js]"<?php echo $jno; ?> /> no
            </td>
          </tr>      
        </table>
      <h3>Flickr Options</h3>
      <table class="form-table">
       <tr valign="top">
         <th width="33%">&nbsp;</th>         
         <td><p><input name="mBox[flickr]" type="checkbox" value="true" <?php echo $flickr; ?> />  <label for="mBox[flickr]"><strong>Use flickr as default.</strong></label></p></td>
       </tr>
       <tr valign="top">
          <th width="33%">API Key</lth>
         <td><input name="mBox[flickr_key]" type="text" value="<?php echo $o['flickr_key']; ?>" size="35" />
             Visit <a href="http://www.flickr.com/services/api/keys/">flickr</a> to register your key.</td>
       </tr>
       <tr valign="top">
         <th width="33%">User ID</th>
         <td><input name="mBox[flickr_user]" type="text" value="<?php echo $o['flickr_user']; ?>" size="20" />
              <br />Use the <a href="http://idgettr.com">idGettr</a> to find your id.
              <br /><small>* If you don't set an user ID, mBox will load photos from all people.</small></td>
       </tr>
       <tr valign="top">
          <th width="33%">Tags Mode</th>
          <td>
            <input type="radio" value="true" name="mBox[flickr_tagsmode]"<?php echo $tmand; ?> /> Display photos tagged with each and every one of the tags specified.<br />
            <input type="radio" value="false" name="mBox[flickr_tagsmode]"<?php echo $tmor; ?> /> Display photos with at least one on the tags specified.            
          </td>
        </tr>        
       </table>
       <h3>Folder Mode Options</h3>
      <table class="form-table">
       <tr valign="top">
         <th>&nbsp;</th>       
         <td><p><input name="mBox[folder]" type="checkbox" value="true" <?php echo $folder; ?> />  <label for="mBox[folder]"><strong>Use folder mode [experimental].</strong></label>
             <br /><small>* Path folder based in Wordpress installation directory. For more info visit <a href="http://www.hnkweb.com/code/mbox#using">http://www.hnkweb.com/code/mbox#using</a></small></p>
         </td>
       </tr>
       </table>
       <p class="submit"><input type="submit" name="Submit" value="<?php _e('Save') ?> &raquo;" /></p>
      </form>
    </div>
<?php
  }
  
  function mBoxbutton(){
  
    $o = mBox_get_options();
    
    $auto = ($o['autostart'] == 'true') ? 'autostart=\"true\"' : '';
  
    if($o['quicktags'] == 'true'){
  
    echo "<!-- Added by mBox -->  
          <script type=\"text/javascript\">
          <!--
            var qtbar = document.getElementById(\"ed_toolbar\");
            if(qtbar){
              var qtbar_number_buttons = edButtons.length;
              edButtons[edButtons.length] = new edButton('ed_mBox','','','','');
              var qtbar_button = qtbar.lastChild;
              while (qtbar_button.nodeType != 1){
                qtbar_button = qtbar_button.previousSibling;
              }
              qtbar_button = qtbar_button.cloneNode(true);
              qtbar.appendChild(qtbar_button);
              qtbar_button.value = 'mBox';
              qtbar_button.onclick = edInsert_mBox;
              qtbar_button.title = \"Add mBox Gallery\";
              qtbar_button.id = \"ed_mBox\";
            }

            function edInsert_mBox() {
              if(!edCheckOpenTags(qtbar_number_buttons)){
                var theTag = '<mBox width=\"".$o['width']."\" height=\"".$o['height']."\" help=\"".$o['help']."\" alert=\"".$o['alert']."\" ".$auto." ';";              
    if ($o['flickr'] == 'true') {
      echo "    theTag += ' mode=\"f\" ';";
      echo "    var tags = prompt('Tags [comma separated]:' , '');";
      echo "    theTag += ' flickr_tags=\"'+tags+'\" ';";
    }  else {
      if ($o['folder'] == 'true') {
      echo "    theTag += ' mode=\"d\" ';";
      echo "    var dir = prompt('Path folder:' , '');";
      echo "    theTag += ' folder=\"'+dir+'\" ';";
    }
    }          
    echo "      theTag += '/>';
                edButtons[qtbar_number_buttons].tagStart  = theTag;
                edInsertTag(edCanvas, qtbar_number_buttons);
              } else {
                edInsertTag(edCanvas, qtbar_number_buttons);
              }
            }

          //-->
          </script>
          <!-- End mBox -->";

    }
  
  }

function mBox_widget_init() {

  if ( !function_exists('register_sidebar_widget') )
    return;

  function mBox_widget($args) {
    
    extract($args);

    $options = get_option('mBox_widget');
    $title = $options['title'];
    $mode =  ($options['mode'] == '') ? 'f' : $options['mode'];
    $tags = $options['tags'];
    $folder = $options['folder'];
    $postid = $options['postid'];
    $width = $options['width'];
    $height = $options['height'];
    $autostart = $options['autostart'];
    $download = $options['download'];
    $zoom = $options['zoom'];

    echo $before_widget . $before_title . $title . $after_title;
    
    $URLplug = trailingslashit(get_settings('siteurl')) . 'wp-content/plugins/mbox';
    $siteurl = get_settings('siteurl');
    $base =  str_replace(basename($_SERVER['SCRIPT_FILENAME']), '', $_SERVER['SCRIPT_FILENAME']); 
    
    switch ($mode) { 
      case 'f': 
        $params = ' mode: "f", flickr_tags: "'.$tags.'" ';
        break;
      case 'd': 
        $params = ' mode: "d", folder: "'.$folder.'" ';
        break;
      case 'p':         
        $params = ' post: "'.$postid.'" ';
        break;
    }
    
    echo "<!-- Added by mBox -->
             <div id=\"mbox-widget\" class=\"box\"></div>
             <p style=\"display: none;\"><script type=\"text/javascript\">
             <!--                   
               window.addEvent(\"domready\", function() {                   
                 new mBox({dir: \"$base\", siteurl: \"$siteurl\", id: \"widget\", width: \"$width\", height: \"$height\", autostart: \"$autostart\", download: \"$download\", zoom: \"$zoom\", $params});
               });
             --></script></p>
             <!-- End mBox -->";
             
    echo $after_widget;
    
  }

  // This is the function that outputs the form to let the users edit
  // the widget's title. It's an optional feature that users cry for.
  function mBox_widget_control() {

    // Get our options and see if we're handling a form submission.
    $options = get_option('mBox_widget');
    if ( !is_array($options) )
      $options = array('title'=>'', 'tags'=>'', 'width'=>'200', 'height'=>'150', 'autostart'=>'false', 'num'=>'1');
    if ( $_POST['mBox-submit'] ) {

      // Remember to sanitize and format use input appropriately.
      $options['title'] = strip_tags(stripslashes($_POST['mBox_title']));
      $options['mode'] = strip_tags(stripslashes($_POST['mBox_mode']));
      $options['tags'] = strip_tags(stripslashes($_POST['mBox_tags']));
      $options['folder'] = strip_tags(stripslashes($_POST['mBox_folder']));
      $options['postid'] = strip_tags(stripslashes($_POST['mBox_postid']));
      $options['width'] = strip_tags(stripslashes($_POST['mBox_width']));
      $options['height'] = strip_tags(stripslashes($_POST['mBox_height']));
      $options['autostart'] = strip_tags(stripslashes($_POST['mBox_autostart']));
      $options['download'] = strip_tags(stripslashes($_POST['mBox_download']));
      $options['zoom'] = strip_tags(stripslashes($_POST['mBox_zoom']));
      update_option('mBox_widget', $options);
    }

    // Be sure you format your options to be valid HTML attributes.
    $title = htmlspecialchars($options['title'], ENT_QUOTES);
    $mode = htmlspecialchars($options['mode'], ENT_QUOTES);
    switch ($mode) { 
      case 'f': 
        $mf = ' checked="checked"'; $md = ''; $mp = '';
        break;
      case 'd': 
        $mf = ''; $md = ' checked="checked"'; $mp = '';
        break;
      case 'p':         
        $mf = ''; $md = ''; $mp = ' checked="checked"';
        break;
    }
    $tags = htmlspecialchars($options['tags'], ENT_QUOTES);
    $folder = htmlspecialchars($options['folder'], ENT_QUOTES);
    $postid = htmlspecialchars($options['postid'], ENT_QUOTES);
    $width = (htmlspecialchars($options['width'], ENT_QUOTES) == '') ? '200' : htmlspecialchars($options['width'], ENT_QUOTES);
    $height = (htmlspecialchars($options['height'], ENT_QUOTES) == '') ? '150' : htmlspecialchars($options['height'], ENT_QUOTES);
    $autostart = (htmlspecialchars($options['autostart'], ENT_QUOTES) == '') ? 'false' : htmlspecialchars($options['autostart'], ENT_QUOTES);;
    $download = (htmlspecialchars($options['download'], ENT_QUOTES) == '') ? 'false' : htmlspecialchars($options['download'], ENT_QUOTES);;
    $zoom = (htmlspecialchars($options['zoom'], ENT_QUOTES) == '') ? 'false' : htmlspecialchars($options['zoom'], ENT_QUOTES);;
    $syes = ($autostart == 'true') ? ' checked="checked"' : '';
    $sno = ($autostart == 'true') ? '' : ' checked="checked"';
    $dyes = ($download == 'true') ? ' checked="checked"' : '';
    $dno = ($download == 'true') ? '' : ' checked="checked"';
    $zyes = ($zoom == 'true') ? ' checked="checked"' : '';
    $zno = ($zoom == 'true') ? '' : ' checked="checked"';

    // Here is our little form segment. Notice that we don't need a
    // complete form. This will be embedded into the existing form.
    echo '<p style="text-align:right;"><label for="mBox_title">' . __('Title:') . ' <input style="width: 240px;" id="mBox_title" name="mBox_title" type="text" value="'.$title.'" /></label></p>';
    echo '<p style="text-align:right;"><label for="mBox_width">' . __('Width:') . ' <input style="width: 50px;" id="mBox_width" name="mBox_width" type="text" value="'.$width.'" />px</label>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;<label for="mBox_height">' . __('Height:') . ' <input style="width: 50px;" id="mBox_height" name="mBox_height" type="text" value="'.$height.'" />px</label></p>';
    echo '<p style="text-align:right;"><label for="mBox_autostart">' . __('Autostart Slideshow:') . ' <input type="radio" value="true" name="mBox_autostart" '.$syes.' /> yes&nbsp;&nbsp;<input type="radio" value="false" name="mBox_autostart" '.$sno.'/> no</label></p>';
    echo '<p style="text-align:right;"><label for="mBox_download">' . __('Download Icon:') . ' <input type="radio" value="true" name="mBox_download" '.$dyes.' /> yes&nbsp;&nbsp;<input type="radio" value="false" name="mBox_download" '.$dno.'/> no</label></p>';
    echo '<p style="text-align:right;"><label for="mBox_zoom">' . __('Zoom Icon:') . ' <input type="radio" value="true" name="mBox_zoom" '.$zyes.' /> yes&nbsp;&nbsp;<input type="radio" value="false" name="mBox_zoom" '.$zno.'/> no</label></p>';
    echo '<p style="text-align:right;"><label for="mBox_mode">' . __('Mode:') . ' <input type="radio" value="f" name="mBox_mode" '.$mf.' /> flickr&nbsp;&nbsp;<input type="radio" value="d" name="mBox_mode" '.$md.'/> folder&nbsp;&nbsp;<input type="radio" value="p" name="mBox_mode" '.$mp.' /> post</label></p>';
    echo '<fieldset class="option"><legend>Flickr Mode</legend><p style="text-align:right;"><label for="mBox_tags">' . __('Tags [comma separated]:') . ' <input style="width: 260px;" id="mBox_tags" name="mBox_tags" type="text" value="'.$tags.'" /></label></p></fieldset>';
    echo '<fieldset class="option"><legend>Folder Mode</legend><p style="text-align:right;"><label for="mBox_folder">' . __('Folder:') . ' <input style="width: 260px;" id="mBox_folder" name="mBox_folder" type="text" value="'.$folder.'" /></label></p></fieldset>';
    echo '<fieldset class="option"><legend>Post Mode</legend><p style="text-align:right;"><label for="mBox_postid">' . __('Post ID:') . ' <input style="width: 60px;" id="mBox_postid" name="mBox_postid" type="text" value="'.$postid.'" /></label></p></fieldset>';    
    echo '<input type="hidden" id="mBox-submit" name="mBox-submit" value="1" />';
  }
  
  // This registers our widget so it appears with the other available
  // widgets and can be dragged and dropped into any active sidebars.
  register_sidebar_widget(array('mBox', 'widgets'), 'mBox_widget');

  // This registers our optional widget control form. Because of this
  // our widget will have a button that reveals a 300x100 pixel form.
  register_widget_control(array('mBox', 'widgets'), 'mBox_widget_control', 300, 400);
}

// Run our code later in case this loads prior to any required plugins.
add_action('widgets_init', 'mBox_widget_init');

  
?>
