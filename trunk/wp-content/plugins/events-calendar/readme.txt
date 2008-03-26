=== Events Calendar ===
Contributors: snumb130
Donate link: http://www.lukehowell.com/donate
Version: 5.8.1
Tags: widget, admin, sidebar, google, plugin, javascript, date, time, calendar
Requires at least: 2.1
Tested up to: 2.5
Stable tag: 5.8.1

== Description ==
When updating, make sure to check that any new widget options are as you want them.  You should also deactivate and reactivate the plugin when updating.
I wrote this because a friend of mine requested it.  I am sure there are some things to work out so I await your feedback.  Once you activate the plugin you can start adding events.  I am working on adding support for external calendars but it is not complete yet. There are instructions under installation for using this widget as a k2 sidebar module.  Please send me any errors you get during testing, if find a fix, let me know and I will incorporate it.  I am also open to any ideas.  If you have previously submitted a bug, please send it again if you are still having trouble.  I wasn't keeping track with what was fix and not as much as I should.  If you have an error please send me as much information as you can regarding the error so that I can best approach it.

The calendar will display your events in the admin section and allow you to edit and delete them.  Details will be shown when you hover over the event title.

The widget section will show dates with events in red and show events when hovering over date.

<b>Supports iCal now.  You can add links to iCal at bottom of admin section.</b>
This allows to add google calendars.
There are some problems with recurring events.  I am working on this but reading the iCal is a chore. :)

== Change Log ==
<pre><code>
Version 5.8.1
  Fixed some alignment errors.
Version 5.8.0
  Fixed some of the cache problems that were occurring because of permissions to write to the directories.  I basically just took it out so the cache will not be used.
  Also added the ability to select the visibility level of the events.  You can choose the level of access that is required to see the events.  All existing events will default to Public access.  You will need to deactivate and reactivate the plugin to make sure that the database gets updated.
Version 5.7.15
  Added a widget option to allow for the choosing of font size in the widget.
Version 5.7.14
  Fixed some css stuff.
Version 5.7.13
  Hopefully cleared up the Fatal Cache Error when trying to use iCal.
Version 5.7.12
  Added link to widget title that will carry you to the admin page for the calendar
Version 5.7.11
  Moved the Events Calendar tab under the Manage tab to show easier with Wordpress 2.5.
Version 5.7.10
  Feature Added - Support for K2 sidebar modules has been added.  When you extract the plugin you will see a new folder.  If you are not using K2 sidebar modules then there is nothing extra you need to do.  If you are wanting to use this as a K2 sidebar module you will have to upload a file to the K2 theme.  The file is located in the plugin folder under the folder k2-module.  This file inside must be uploaded to the k2 module folder.  The directory is as follows: wp-content/themes/k2/app/modules
Version 5.6.10
  Bug Fix - Fixed errors caused in PHP4 with ical parsing
Version 5.5.10
  Bug Fix - Displaying duplicate March in drop down menu on widget.
Version 5.5.9
  Added ability to show events from ical.  There is still some issue with irregular occurrances.  However, seems to work fine with DAILY, WEEKLY, MONTHLY, YEARLY standard reccurring events.
Version 4.5.9
  Bug Fix - Error cause by themes not containging <?php wp_footer();?>
  
Version 4.5.8
  Bug Fix - Fixed error caused by using single quotes.
  Added Spanish translation from Covi
  Changed events list format to show current days events as well as future.
  
Version 4.4.7
  Added ability for multiple languages, patch from Rauli Haverinen
  Added Finnish translation files from Rauli Haverinen
Version 3.4.6
  Just straightened some code.
Version 3.4.5
  Code beautification

Version 3.3.5
  Took out donate link as it is on my site now.

Version 3.3.4
  Added fix from Kerwin Kanago to correct problem in php4 fix from Brett Minnie
  
Version 3.3.3
  Set color of text to black in hover box.  Fixed problem showing up with themes with light color text.
  Added option to choose minimum user level to edit event. (You must go to widget options and resave them or management page will not show up)

Versino 2.3.2
  Bug Fix - Calendar wouldn't accept events with day having leading zero.  Converted string to int to fix.

Version 2.2.2
  Bug Fix - Problem caused with redeclaring str_split

Version 2.2.1
  Display Calendar as upcoming event list. (revision by Dan Coulter - http://www.dancoulter.com)
  Choose diplay format of Date and Time. (revision by Dan Coulter)

Version 1.2.1
  Hide empty fields.  (suggestion by Dan Coulter)
  php4 fix.  (revision by Brett Minnie - http://www.fractalmetal.co.za)

Version 1.1.1
  Bug Fix - Wordpress variabl clashing with events variable.  Fixed bug displaying archives.

Version 1.1.0
  Title Option
  Day Name Length Option

Version 1.0.0
  First release.
</code></pre>

== Installation ==
1. Upload `events-calendar` folder to the `/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

	When updating you will need to deactivate and reactivate the plugin.
	Also, to be safe you should go to widget options and resave them.
	
	If you are wanting to use this as a K2 sidebar module you will have to upload a file to the K2 theme. The file is located in the plugin folder under the folder k2-module. This file inside must be uploaded to the k2 module folder. The directory is as follows: wp-content/themes/k2/app/modules

	Once you add the widget to the sidebar you will be able to see the management page.

== Frequently Asked Questions ==

= Why does nothing happens when I hover over the event? =
In the admin section under presentation you will see a tab that says theme editor.  If you click that it will bring you to a text editor for editing you theme.  On the right side of the page you will see a list of links.  Click on the link for the footer.  Make sure that some where in the file the following line appears:  "wp_footer();" (without the quotes).  If this line is not there the event information will not show up when you hover over the date.

= Where do I add my events? =
Under the "Manage" tab of the admin page, there is a "Calendar" subpage.  You can also click on the widget title on your sidebar and it will take you to the admin section for the calendar.

= Is this plugin supported as a k2 module? =
Yes, it is now supported with the k2 theme.
