<?php
/*  Copyright 2006 Vincent Prat  (email : vpratfr@yahoo.fr)

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
?>

<?php 
	if (!current_user_can('manage_options')) {		
		wp_die(__("You are not allowed to change the options of this plugin.", MAILUSERS_I18N_DOMAIN));
	} 
?>

<?php
	$err_msg = '';
	
	get_currentuserinfo();
	$from_name = $user_identity;
	$from_address = $user_email;
	$mail_format = mailusers_get_default_mail_format();
	$subject = mailusers_get_default_subject();
	$mail_content = mailusers_get_default_body();

	// Replace the template variables concerning the blog details
	// --
	$subject = mailusers_replace_blog_templates($subject);
	$mail_content = mailusers_replace_blog_templates($mail_content);
		
	// Replace the template variables concerning the sender details
	// --	
	get_currentuserinfo();
	$from_name = $user_identity;
	$from_address = $user_email;
	$subject = mailusers_replace_sender_templates($subject, $from_name);
	$mail_content = mailusers_replace_sender_templates($mail_content, $from_name);

	// Replace the template variables concerning the post
	// --	
	$post = get_post( $post_id );
	$post_title = $post->post_title;
	$post_url = get_permalink( $post_id );			
	$post_content = explode( '<!--more-->', $post->post_content, 2 );
	$post_excerpt = $post_content[0];
	
	$subject = mailusers_replace_post_templates($subject, $post_title, $post_excerpt, $post_url);
	$mail_content = mailusers_replace_post_templates($mail_content, $post_title, $post_excerpt, $post_url);
	
?>

<div class="wrap">
<?php 
	// Fetch users
	// --
	$recipients = mailusers_get_recipients_from_ids(array($user_ID));

	if (empty($recipients)) {
?>
		<p><strong><?php _e('No recipients were found.', MAILUSERS_I18N_DOMAIN); ?></strong></p>
<?php
	} else {
		mailusers_send_mail($recipients, format_to_post($subject), $mail_content, $mail_format, $from_name, $from_address);
?>
		<div class="updated fade">
			<p><?php echo sprintf(__("Test email sent to %s.", MAILUSERS_I18N_DOMAIN), $from_address); ?></p>
		</div>		
<?php
		include 'email_users_options_form.php';
	}
?>
</div>
		
	
