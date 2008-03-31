<script type="text/javascript">
	var blogurl="<?php echo get_settings('home');?>";
	var needemail="<?php echo get_option('comment_registration');?>";
</script>
<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');
  if (!empty($post->post_password)) {
  	if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {
  		?>
  		<p class="nocomments">This post is password protected. Enter the password to view comments.</p>
  		<?php
  		return;
  	}
  }
	if(!$tablecomments && $wpdb->comments)
		$tablecomments = $wpdb->comments;
		$comments = $wpdb->get_results("SELECT * FROM $tablecomments WHERE comment_post_ID = '$id' AND comment_approved = '1' ORDER BY comment_date");
		// You can start editing here
		$GLOBALS['comments_reply'] = array();

		function write_comment(&$c, $deep_id = -1) {
			global $max_level;
			$comments_reply = $GLOBALS['comments_reply'];
			if ($c->comment_type == 'trackback')
				$style = ' class="trackback"';
?>

<li id="comment-<?php echo $c->comment_ID ?>" <?php echo $style; ?>>
	<div class="commenthead">At <?php echo mysql2date('Y.m.d H:i', $c->comment_date);?>, 
		<a name='comment-<?php echo $c->comment_ID ?>'></a>
		<span class="author"><?php comment_author_link()?></span> said: 
	</div>
	<div class="body"><?php comment_text();?></div>
	<p class="meta">
		<?php
		global $user_ID, $post;
		get_currentuserinfo();
		if (user_can_edit_post_comments($user_ID, $post->ID) || ($GLOBALS['cmtDepth'] < $max_level))
			echo '';
		//	comment_favicon();
			edit_comment_link('EDIT', '',(($GLOBALS['cmtDepth'] < $max_level)?' | ': ''));
				if ($GLOBALS['cmtDepth'] < $max_level) {
					if ( get_option("comment_registration") && !$user_ID )
						echo '<a href="'. get_option('siteurl') . '/wp-login.php?redirect_to=' . get_permalink() .'">Log in to Reply</a> ]';
					else
						echo '<a href="javascript:moveForm('.$c->comment_ID.')" title="reply">REPLY</a>';
				}
		if (user_can_edit_post_comments($user_ID, $post->ID) || ($GLOBALS['cmtDepth'] < $max_level))
			echo '</p>';
				if ($comments_reply[$c->comment_ID]) {
					$id = $c->comment_ID;
					if($GLOBALS['cmtDepth'] < $max_level )
						echo '<ul>';
					foreach($comments_reply[$id] as $c) {
						$GLOBALS['cmtDepth']++;
						if($GLOBALS['cmtDepth'] == $max_level)
							write_comment($c, $c->comment_ID);
						else
							write_comment($c, $deep_id);
						$GLOBALS['cmtDepth']--;
					}
					if($GLOBALS['cmtDepth'] < $max_level )
						echo '</ul>';
				}
				echo '</li>';
		?>
<?php	} ?>

<h3><?php comments_number('0', '1', '%'); ?>条留言 <a href="#comment" title="Leave a reply">&raquo;</a></h3>
<ul class="commentlist" id="commentlist">
	<?php
		if ($comments) :
			foreach ($comments as $c) {
				$GLOBALS['comments_reply'][$c->comment_reply_ID][] = $c;
			}
			$GLOBALS['cmtDepth'] = 0;
			foreach($GLOBALS['comments_reply'][0] as $cmt) {
				$GLOBALS['comment'] = &$cmt;
				write_comment($GLOBALS['comment']);
			}
		else: echo '<li style="border:none;"></li>';
		endif;
	?>
</ul>
<?php if ('open' == $post->comment_status) : ?>
<div id="cmtForm">
<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>">logged in</a> to post a comment.</p>
<?php else : ?>
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform" onsubmit="AjaxSendComment();return false;">
<?php if ( $user_ID ) : ?>
<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>
<?php else : ?>
<div>
<label for="author">作者</label>
<input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" tabindex="1" /> 
  <span id="nm">(Required)</span><br/>
<label for="email">邮件</label>
<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" tabindex="2" />  (Required, not published)<br/>
<label for="url">网址</label>
<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" tabindex="3" /> 
</div>
<?php endif; ?>
<table width="100%"><tr><td><textarea name="comment" id="comment" tabindex="4" rows="4" cols="10"></textarea></td></tr></table>
<div><input value="Say it!" name="submit" type="submit" tabindex="5"/>
<input id="reRoot" type="button" onclick="javascript:moveForm(0)" style="display:none" value="Cancel" tabindex="6"/>
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<input type="hidden" name="comment_reply_ID" id="comment_reply_ID" value="0" />
<?php do_action('comment_form', $post->ID); ?></div>
</form>
</div>
<?php endif; // If registration required and not logged in ?>
<?php endif; // if you delete this the sky will fall on your head ?>
