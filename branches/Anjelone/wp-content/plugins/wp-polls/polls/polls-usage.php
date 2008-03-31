<?php
/*
+----------------------------------------------------------------+
|																							|
|	WordPress 2.1 Plugin: WP-Polls 2.21										|
|	Copyright (c) 2007 Lester "GaMerZ" Chan									|
|																							|
|	File Written By:																	|
|	- Lester "GaMerZ" Chan															|
|	- http://lesterchan.net															|
|																							|
|	File Information:																	|
|	- How To Use WP-Polls															|
|	- wp-content/plugins/polls/polls-usage.php								|
|																							|
+----------------------------------------------------------------+
*/


### Check Whether User Can Manage Polls
if(!current_user_can('manage_polls')) {
	die('Access Denied');
}
?>
<div class="wrap"> 
	<h2><?php _e('General Usage (Without Widget)', 'wp-polls'); ?></h2>
	<ol>
		<li>
			<?php _e('Open ', 'wp-polls'); ?><strong>wp-content/themes/&lt;<?php _e('YOUR THEME NAME', 'wp-polls'); ?>&gt;/sidebar.php</strong>
		</li>
		<li>
			<?php _e('Add:', 'wp-polls'); ?>
			<blockquote>
				<pre class="wp-polls-usage-pre">&lt;?php if (function_exists('vote_poll') &amp;&amp; !in_pollarchive()): ?&gt;
&lt;li&gt;
&nbsp;&nbsp;&nbsp;&lt;h2&gt;Polls&lt;/h2&gt;
&nbsp;&nbsp;&nbsp;&lt;ul&gt;
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;?php get_poll();?&gt;&lt;/li&gt;
&nbsp;&nbsp;&nbsp;&lt;/ul&gt;
&nbsp;&nbsp;&nbsp;&lt;?php display_polls_archive_link(); ?&gt;
&lt;/li&gt;
&lt;?php endif; ?&gt;	</pre>
			</blockquote>
			<?php _e('To show specific poll, use :', 'wp-polls'); ?>
			<blockquote><pre class="wp-polls-usage-pre">&lt;?php get_poll(<strong>2</strong>);?&gt;</pre></blockquote>
			<?php _e('where <strong>2</strong> is your poll id.', 'wp-polls'); ?>
			<?php _e('To embed a specific poll in your post, use :', 'wp-polls'); ?>
			<blockquote><pre class="wp-polls-usage-pre">[poll=<strong>2</strong>]</pre></blockquote>
			<?php _e('where <strong>2</strong> is your poll id.', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('Scroll down for instructions on how to create <strong>Polls Archive</strong>.', 'wp-polls'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('General Usage (With Widget)', 'wp-polls'); ?></h2>
	<ol>
		<li>
			<?php _e('<strong>Activate</strong> WP-Polls Widget Plugin', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('Go to \'WP-Admin -> Presentation -> Sidebar Widgets\'', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('<strong>Drag</strong> the Polls Widget to your sidebar', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('You can <strong>configure</strong> the Polls Widget by clicking on the configure icon', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('Click \'Save changes\'', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e(' down for instructions on how to create a <strong>Polls Archive</strong>.', 'wp-polls'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Polls Archive', 'wp-polls'); ?></h2>
	<ol>
		<li>
			<?php _e('Go to \'WP-Admin -> Write -> Write Page\'', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('Type any title you like in the post\'s title area', 'wp-polls'); ?>
		</li>
		<li>
			<?php printf(__('Type \'<strong>%s</strong>\' in the post\'s content area (without the quotes)', 'wp-polls'), '[page_polls]'); ?>
		</li>
		<li>
			<?php _e('Type \'<strong>pollsarchive</strong>\' in the post\'s slug area (without the quotes)', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('Click \'Publish\'', 'wp-polls'); ?>
		</li>
		<li>
			<?php _e('If you <strong>ARE NOT</strong> using nice permalinks, you need to go to \'WP-Admin -> Polls -> Poll Option\' and under \'<strong>Poll Archive -> Polls Archive URL</strong>\', you need to fill in the URL to the Polls Archive Page you created above.', 'wp-polls'); ?>
		</li>
	</ol>
</div>
<div class="wrap"> 
	<h2><?php _e('Polls Stats', 'wp-polls'); ?></h2> 
	<h3><?php _e('To Display Total Polls', 'wp-polls'); ?></h3>
	<blockquote>
		<pre class="wp-polls-usage-pre">&lt;?php if (function_exists('get_pollquestions')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;?php get_pollquestions(); ?&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
	<h3><?php _e('To Display Total Poll Answers', 'wp-polls'); ?></h3>
	<blockquote>
		<pre class="wp-polls-usage-pre">&lt;?php if (function_exists('get_pollanswers')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;?php get_pollanswers(); ?&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
	<h3><?php _e('To Display Total Poll Votes', 'wp-polls'); ?></h3>
	<blockquote>
		<pre class="wp-polls-usage-pre">&lt;?php if (function_exists('get_pollvotes')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;?php get_pollvotes(); ?&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
	<h3><?php _e('To Display Total Poll Voters', 'wp-polls'); ?></h3>
	<blockquote>
		<pre class="wp-polls-usage-pre">&lt;?php if (function_exists('get_pollvoters')): ?&gt;
&nbsp;&nbsp;&nbsp;&lt;?php get_pollvoters(); ?&gt;
&lt;?php endif; ?&gt;	</pre>
	</blockquote>
</div>
<div class="wrap"> 
	<h2><?php _e('Note', 'wp-polls'); ?></h2>
	<ul>
		<li>
			<?php _e('In IE, some of the poll\'s text may appear jagged (this is normal in IE). To solve this issue,', 'wp-polls'); ?>
			<ol>
				<li>
					<?php _e('Open <strong>poll-css.css</strong>', 'wp-polls'); ?>
				</li>
				<li>
					<?php _e('Find:', 'wp-polls'); ?>
					<blockquote><pre class="wp-polls-usage-pre">/* background-color: #ffffff; */</pre></blockquote>
				</li>
				<li>
					<?php _e('Replace:', 'wp-polls'); ?>
					<blockquote><pre class="wp-polls-usage-pre">background-color: #ffffff;</pre></blockquote>
					<?php _e('Where <strong>#ffffff</strong> should be your background color for the poll.', 'wp-polls'); ?>
				</li>
			</ol>
		</li>
	</ul>
</div>