=== mBox ===
Tags: slideshow, gallery, images, mootools
Requires at least: 2.0
Tested up to: 2.3
Stable tag: trunk

mBox allows you to easy include slideshow galleries into Wordpress posts, pages and sidebar using the pictures uploaded, flickr account or a folder.

== Description ==

mBox allows you to easy include slideshow galleries into Wordpress posts, pages and sidebar. It can use the Image and File Attachments to show the pictures uploaded [posts or pages], load photos from flickr accounts or a folder.

**Features**

    * Gallery generated automatically from the images uploaded.
    * AJAX loading to display the page faster.
    * Thumbnail navigation bar.
    * Pagination.
    * Enable / Disable slideshow presentation.
    * Download image icon
    * Load photos from flickr.
    * Widget sidebar support [flickr mode only].
    
mBox uses mootools javascript framework and it had been tested on Firefox 2.x, Safari and Internet Explorer 7.    

** Default Usage **

    * Create a post.
    * Use the Image and File Attachments to upload the images to show. You can input a Title and Description for each one.
    * Press the mBox quicktag where you want the slideshow to be. If you don’t see the mBox quicktag, you can alternatively copy and paste <mbox /> where you want it to appear.
    * If you want to personalize only certain slideshows, you can add parameters to the call like:
      <mbox width="750" height="200" />
      
** Flickr Usage **

    * Create a post.
    * Press the mBox quicktag where you want the slideshow to be. If you don’t see the mBox quicktag, you can alternatively copy and paste <mbox /> where you want it to appear.
    * If 'Use flickr as default' option is activated, mBox will ask you for the tags.
      Insert all the tags you want to filter comma separated.
    * The code should be similar to:
      <mbox width="750" height="200" mode="f" flickr_tags="spain, beach, summer" />

** Folder Usage **

    * Create a post.
    * Press the mBox quicktag where you want the slideshow to be. If you don’t see the mBox quicktag, you can alternatively copy and paste <mbox /> where you want it to appear.
    * If 'Use folder mode' option is activated, mBox will ask you for the folder path.
      This path must be based on Wordpress installation. For more info visit http://www.hnkweb.com/code/mbox#using.
    * The code should be similar to:
      <mbox width="750" height="200" mode="d" folder="galleries/beach" />

** Widget Usage **   

    * Activate the mBox widget with drag'n'drop over the slidebar.
    * Click on Configure button.
    * Set title, flickr tags [comma separated], width, height and autostart slideshow options.
    * Save.


== Installation ==

Unpack the zip file and upload the content into the folder wp-content/plugins/ (the WordPress plugins folder). Note: Do not change the folder names.

For upgrade: delete entire directory before.

After installation, you should see a new tab on the Options section in the Wordpress Administration.
Click on that tab to set the default options:

    * Show Quicktag: Enable a new button in the post/page editor [if you are using the default set].
    * Autostart slideshow: begin slideshow automatically.
    * Default Widht/Height: Default size values of the slideshow div [also max. image size].
    * Mootools Components: If you are already using mootools framework in your theme, you can try to load only the required components.
                           Other case, check to 'yes' all of them.

Flickr Options:

    * Use flickr as default: If activated, quicktag will append mode="f" to mBox call by default.
    * API Key: API key is needed to use flickr photo searcher. Follow the link in the options page to register a new key.
    * User ID: If you set your flickr user ID, mBox will load only your photos. If this value is blank mBox will get photos from everyone.
    * Tags Mode: How the tag filter will work. Photo must be tagged with each and every one of the tags specified or with at least one on the tags specified.
    
Folder Options

    * Use folder mode [experimental]: If activated [and flickr option is desactivated], quicktag will append mode="d" to mBox call by default and it will ask you for a path folder.

== Screenshots ==

1. mBox

== Contact ==

Support, bugs and new features using my contact page or directly to hanokmail[at]gmail.com