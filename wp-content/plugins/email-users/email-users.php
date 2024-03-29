<?php
/*
Plugin Name: Email Users
Version: 3.1.5
Plugin URI: http://email-users.vincentprat.info
Description: Allows the site editors to send an e-mail to the blog users. Credits to <a href="http://www.catalinionescu.com">Catalin Ionescu</a> who gave me some ideas for the plugin and has made a similar plugin. Bug reports and corrections by Cyril Crua and Pokey.
Author: Vincent Prat (email : vpratfr@yahoo.fr)
Author URI: http://www.vincentprat.info
*/

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

// Version of the plugin
define( 'MAILUSERS_CURRENT_VERSION', '3.1.5' );

// i18n plugin domain
define( 'MAILUSERS_I18N_DOMAIN', 'email-users' );

// Capabilities used by the plugin
define( 'MAILUSERS_EMAIL_SINGLE_USER_CAP', 'email_single_user' );
define( 'MAILUSERS_EMAIL_MULTIPLE_USERS_CAP', 'email_multiple_users' );
define( 'MAILUSERS_EMAIL_USER_GROUPS_CAP', 'email_user_groups' );
define( 'MAILUSERS_NOTIFY_USERS_CAP', 'email_users_notify' );

// User meta
define( 'MAILUSERS_ACCEPT_NOTIFICATION_USER_META', 'email_users_accept_notifications' );
define( 'MAILUSERS_ACCEPT_MASS_EMAIL_USER_META', 'email_users_accept_mass_emails' );

/**
 * Initialise the internationalisation domain
 */
$is_mailusers_i18n_setup = false;
function mailusers_init_i18n() {
	global $is_mailusers_i18n_setup;

	if ($is_mailusers_i18n_setup == false) {
		load_plugin_textdomain(MAILUSERS_I18N_DOMAIN, 'wp-content/plugins/email-users');
		$is_mailusers_i18n_setup = true;
	}
}

/**
 * Set default values for the options (check against the version)
 */
add_action('activate_email-users/email-users.php','mailusers_plugin_activation');
function mailusers_plugin_activation() {
	mailusers_init_i18n();

	$installed_version = mailusers_get_installed_version();

	if ( $installed_version==mailusers_get_current_version() ) {
		// do nothing
	}
	else if ( $installed_version=='' ) {
		add_option(
			'mailusers_version',
			mailusers_get_current_version(),
			'version of the email users plugin' );
		add_option(
			'mailusers_default_subject',
			__('[%BLOG_NAME%] A post of interest: "%POST_TITLE%"', MAILUSERS_I18N_DOMAIN),
			'The default title to use when using the post notification functionality' );
		add_option(
			'mailusers_default_body',
			__('<p>Hello, </p><p>I would like to bring your attention on a new post published on the blog. Details of the post follow; I hope you will find it interesting.</p><p>Best regards, </p><p>%FROM_NAME%</p><hr><p><strong>%POST_TITLE%</strong></p><p>%POST_EXCERPT%</p><ul><li>Link to the post: <a href="%POST_URL%">%POST_URL%</a></li><li>Link to %BLOG_NAME%: <a href="%BLOG_URL%">%BLOG_URL%</a></li></ul>', MAILUSERS_I18N_DOMAIN),
			'Mail User - The default body to use when using the post notification functionality' );
		add_option(
			'mailusers_default_mail_format',
			'html',
			'Mail User - Default mail format (html or plain text)' );
		add_option(
			'mailusers_max_bcc_recipients',
			'0',
			'Mail User - Maximum number of recipients in the BCC field' );

		mailusers_add_default_capabilities();
		mailusers_add_default_user_meta();

	} else if ( $installed_version>='2.0' && $installed_version<'3.0.0' ) {
		// Version 2.x, a bug was corrected in the template, update it
		update_option(
			'mailusers_default_subject',
			__('[%BLOG_NAME%] A post of interest: "%POST_TITLE%"', MAILUSERS_I18N_DOMAIN) );
		update_option(
			'mailusers_default_body',
			__('<p>Hello, </p><p>I would like to bring your attention on a new post published on the blog. Details of the post follow; I hope you will find it interesting.</p><p>Best regards, </p><p>%FROM_NAME%</p><hr/><p><strong>%POST_TITLE%</strong></p><p>%POST_EXCERPT%</p><ul><li>Link to the post: <a href="%POST_URL%">%POST_URL%</a></li><li>Link to %BLOG_NAME%: <a href="%BLOG_URL%">%BLOG_URL%</a></li></ul>', MAILUSERS_I18N_DOMAIN) );
		add_option(
			'mailusers_default_mail_format',
			'html',
			'Mail User - Default mail format (html or plain text)' );
		add_option(
			'mailusers_max_bcc_recipients',
			'0',
			'Mail User - Maximum number of recipients in the BCC field' );

		delete_option('mailusers_mail_user_level');
		delete_option('mailusers_mail_method');
		delete_option('mailusers_smtp_port');
		delete_option('mailusers_smtp_server');
		delete_option('mailusers_smtp_user');
		delete_option('mailusers_smtp_authentication');
		delete_option('mailusers_smtp_password');

		// Remove old capabilities
		$role = get_role('editor');
		$role->remove_cap('email_users');

		mailusers_add_default_capabilities();
		mailusers_add_default_user_meta();
	} else {
	}

	// Update version number
	update_option( 'mailusers_version', mailusers_get_current_version() );
}

/**
* Plugin deactivation
*/
register_deactivation_hook( __FILE__, 'mailusers_plugin_deactivation' );
function mailusers_plugin_deactivation() {
	// update_option('mailusers_version', '2.3');
}

/**
* Add default user meta information
*/
function mailusers_add_default_user_meta() {
	global $wpdb;
	$table_users = $wpdb->prefix . "wpsb_users";
	
	$users = $wpdb->get_results("SELECT id FROM " . $table_users);
	foreach ($users as $user) {
		mailusers_user_register($user->id);
	}
}

/**
* Add capabilities to roles by default
*/
function mailusers_add_default_capabilities() {
	$role = get_role('contributor');
	$role->add_cap(MAILUSERS_EMAIL_SINGLE_USER_CAP);

	$role = get_role('author');
	$role->add_cap(MAILUSERS_EMAIL_SINGLE_USER_CAP);
	$role->add_cap(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP);

	$role = get_role('editor');
	$role->add_cap(MAILUSERS_NOTIFY_USERS_CAP);
	$role->add_cap(MAILUSERS_EMAIL_SINGLE_USER_CAP);
	$role->add_cap(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP);
	$role->add_cap(MAILUSERS_EMAIL_USER_GROUPS_CAP);

	$role = get_role('administrator');
	$role->add_cap(MAILUSERS_NOTIFY_USERS_CAP);
	$role->add_cap(MAILUSERS_EMAIL_SINGLE_USER_CAP);
	$role->add_cap(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP);
	$role->add_cap(MAILUSERS_EMAIL_USER_GROUPS_CAP);
}

/**
* Add the meta field when a user registers
*/
add_action('user_register', 'mailusers_user_register');
function mailusers_user_register($user_id) {
	if (get_usermeta($user_id, MAILUSERS_ACCEPT_NOTIFICATION_USER_META)=='')
		update_usermeta($user_id, MAILUSERS_ACCEPT_NOTIFICATION_USER_META, 'true');

	if (get_usermeta($user_id, MAILUSERS_ACCEPT_MASS_EMAIL_USER_META)=='')
		update_usermeta($user_id, MAILUSERS_ACCEPT_MASS_EMAIL_USER_META, 'true');
}

/**
* Add a related link to the post edit page to create a template from current post
*/
add_action('post_relatedlinks_list', 'mailusers_post_relatedlink');
function mailusers_post_relatedlink() {
	global $post_ID;
	if (isset($post_ID) && current_user_can(MAILUSERS_NOTIFY_USERS_CAP)) {
?>
<li><a href="post-new.php?page=email-users/email_users_notify_form.php&post_id=<?php echo $post_ID; ?>"><?php _e('Notify users about this post', MAILUSERS_I18N_DOMAIN); ?></a></li>
<?php
	}
}

add_action('page_relatedlinks_list', 'mailusers_page_relatedlink');
function mailusers_page_relatedlink() {
	global $post_ID;
	if (isset($post_ID) && current_user_can(MAILUSERS_NOTIFY_USERS_CAP)) {
?>
<li><a href="post-new.php?page=email-users/email_users_notify_form.php&post_id=<?php echo $post_ID; ?>"><?php _e('Notify users about this page', MAILUSERS_I18N_DOMAIN); ?></a></li>
<?php
	}
}

/**
 * Add a new menu under Write:, visible for all users with access levels 8+ (administrator role).
 */
add_action( 'admin_menu', 'mailusers_add_pages' );
function mailusers_add_pages() {
	mailusers_init_i18n();

	if (	current_user_can(MAILUSERS_EMAIL_SINGLE_USER_CAP)
		|| 	current_user_can(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP)) {
		add_submenu_page( 'post-new.php',
			__('Email', MAILUSERS_I18N_DOMAIN),
			__('Email', MAILUSERS_I18N_DOMAIN),
			0,
			'email-users/email_users_user_mail_form.php' );
	} else if ( current_user_can(MAILUSERS_EMAIL_USER_GROUPS_CAP) ) {
		add_submenu_page( 'post-new.php',
			__('Email', MAILUSERS_I18N_DOMAIN),
			__('Email', MAILUSERS_I18N_DOMAIN),
			0,
			'email-users/email_users_group_mail_form.php' );
	}

	if (current_user_can('manage_options')) {
		add_options_page( __('Email Users', MAILUSERS_I18N_DOMAIN),
			__('Email Users', MAILUSERS_I18N_DOMAIN),
			0,
			'email-users/email_users_options_form.php' );
	}
}

/**
* Add a form to change user preferences in the profile
*/
add_action('show_user_profile', 'mailusers_user_profile_form');
function mailusers_user_profile_form() {
	global $user_ID;
?>
	<h3><?php _e('Email Preferences', MAILUSERS_I18N_DOMAIN); ?></h3>

	<table class="form-table">
	<tbody>
		<tr>
			<th></th>
			<td>
				<input 	type="checkbox"
						name="<?php echo MAILUSERS_ACCEPT_NOTIFICATION_USER_META; ?>"
						id="<?php echo MAILUSERS_ACCEPT_NOTIFICATION_USER_META; ?>"
						value="true"
						<?php if (get_usermeta($user_ID, MAILUSERS_ACCEPT_NOTIFICATION_USER_META)=="true") echo 'checked="checked"'; ?> ></input>
				<?php _e('Accept to recieve post or page notification emails', MAILUSERS_I18N_DOMAIN); ?><br/>
				<input 	type="checkbox"
						name="<?php echo MAILUSERS_ACCEPT_MASS_EMAIL_USER_META; ?>"
						id="<?php echo MAILUSERS_ACCEPT_MASS_EMAIL_USER_META; ?>"
						value="true"
						<?php if (get_usermeta($user_ID, MAILUSERS_ACCEPT_MASS_EMAIL_USER_META)=="true") echo 'checked="checked"'; ?> ></input>
				<?php _e('Accept to recieve emails sent to multiple recipients (but still accept emails sent only to me)', MAILUSERS_I18N_DOMAIN); ?>
			</td>
		</tr>
	</tbody>
	</table>
<?php
}

/**
* Save our profile data
*/
add_action('personal_options_update', 'mailusers_user_profile_update');
function mailusers_user_profile_update() {
	global $_POST, $user_ID;

	if (isset($_POST[MAILUSERS_ACCEPT_NOTIFICATION_USER_META])) {
		update_usermeta($user_ID, MAILUSERS_ACCEPT_NOTIFICATION_USER_META, 'true');
	} else {
		update_usermeta($user_ID, MAILUSERS_ACCEPT_NOTIFICATION_USER_META, 'false');
	}

	if (isset($_POST[MAILUSERS_ACCEPT_MASS_EMAIL_USER_META])) {
		update_usermeta($user_ID, MAILUSERS_ACCEPT_MASS_EMAIL_USER_META, 'true');
	} else {
		update_usermeta($user_ID, MAILUSERS_ACCEPT_MASS_EMAIL_USER_META, 'false');
	}
}

/**
 * Wrapper for the option 'mailusers_default_subject'
 */
function mailusers_get_default_subject() {
	return stripslashes(get_option( 'mailusers_default_subject' ));
}

/**
 * Wrapper for the option 'mailusers_default_subject'
 */
function mailusers_update_default_subject( $subject ) {
	return update_option( 'mailusers_default_subject', stripslashes($subject) );
}

/**
 * Wrapper for the option 'mailusers_default_body'
 */
function mailusers_get_default_body() {
	return stripslashes(get_option( 'mailusers_default_body' ));
}

/**
 * Wrapper for the option 'mailusers_default_body'
 */
function mailusers_update_default_body( $body ) {
	return update_option( 'mailusers_default_body', stripslashes($body) );
}

/**
 * Wrapper for the option 'mailusers_version'
 */
function mailusers_get_installed_version() {
	return get_option( 'mailusers_version' );
}

/**
 * Wrapper for the option 'mailusers_version'
 */
function mailusers_get_current_version() {
	return MAILUSERS_CURRENT_VERSION;
}

/**
 * Wrapper for the option default_mail_format
 */
function mailusers_get_default_mail_format() {
	return get_option( 'mailusers_default_mail_format' );
}

/**
 * Wrapper for the option default_mail_format
 */
function mailusers_update_default_mail_format( $default_mail_format ) {
	return update_option( 'mailusers_default_mail_format', $default_mail_format );
}

/**
 * Wrapper for the option mail_method
 */
function mailusers_get_max_bcc_recipients() {
	return get_option( 'mailusers_max_bcc_recipients' );
}

/**
 * Wrapper for the option mail_method
 */
function mailusers_update_max_bcc_recipients( $max_bcc_recipients ) {
	return update_option( 'mailusers_max_bcc_recipients', $max_bcc_recipients );
}

/**
 * Get the users
 * $meta_filter can be '', MAILUSERS_ACCEPT_NOTIFICATION_USER_META, or MAILUSERS_ACCEPT_MASS_EMAIL_USER_META
 */
function mailusers_get_users( $exclude_id='', $meta_filter = '') {
	global $wpdb;
	$table_users = $wpdb->prefix . "wpsb_users";

	$additional_sql_filter = "";

	if ($meta_filter=='') {
		if ($exclude_id!='') {
			$additional_sql_filter = " WHERE (id<>" . $exclude_id . ") ";
		}
		
	    $users = $wpdb->get_results(
			  "SELECT id, user_email, user_email AS display_name "
			. "FROM " . $table_users . " "
			. $additional_sql_filter );
	} else {
		if ($exclude_id!='') {
			$additional_sql_filter .= " AND (id<>" . $exclude_id . ") ";
		}
		$additional_sql_filter .= " AND (meta_key='" . $meta_filter . "') ";
		$additional_sql_filter .= " AND (meta_value='true') ";
		
	    $users = $wpdb->get_results(
			  "SELECT id, user_email, user_emai AS display_name "
			. "FROM $wpdb->usermeta, " . $table_users . " "
			. "WHERE "
			. " (user_id = id)"
			. $additional_sql_filter );
	}

	return $users;
}

/**
 * Get the users
 * $meta_filter can be '', MAILUSERS_ACCEPT_NOTIFICATION_USER_META, or MAILUSERS_ACCEPT_MASS_EMAIL_USER_META
 */
function mailusers_get_roles( $exclude_id='', $meta_filter = '') {
	$roles = array();

	$wp_roles = new WP_Roles();
	foreach ($wp_roles->get_names() as $key => $value) {
		$users_in_role = mailusers_get_recipients_from_roles(array($key), $exclude_id, $meta_filter);
		if (!empty($users_in_role)) {
			$roles[$key] = $value;
		}
	}

	return $roles;
}

/**
 * Get the users given a role or an array of ids
 * $meta_filter can be '', MAILUSERS_ACCEPT_NOTIFICATION_USER_META, or MAILUSERS_ACCEPT_MASS_EMAIL_USER_META
 */
function mailusers_get_recipients_from_ids( $ids, $exclude_id='', $meta_filter = '') {
	global $wpdb;
	$table_users = $wpdb->prefix . "wpsb_users";

	if (empty($ids)) {
		return array();
	}

	$id_filter = implode(", ", $ids);

	$additional_sql_filter = "";
	if ($exclude_id!='') {
		$additional_sql_filter .= " AND (id<>" . $exclude_id . ") ";
	}

	if ($meta_filter=='') {
	    $users = $wpdb->get_results(
			  "SELECT id, user_email, display_name "
			. "FROM " . $table_users . " "
			. "WHERE "
			. " (id IN (" . implode(", ", $ids) . ")) "
			. $additional_sql_filter );
	} else {
		$additional_sql_filter .= " AND (meta_key='" . $meta_filter . "') ";
		$additional_sql_filter .= " AND (meta_value='true') ";

	    $users = $wpdb->get_results(
			  "SELECT id, user_email, display_name "
			. "FROM $wpdb->usermeta, " . $table_users . " "
			. "WHERE "
			. " (user_id = id)"
			. $additional_sql_filter
			. " AND (id IN (" . implode(", ", $ids) . ")) " );
	}

	return $users;
}

/**
 * Get the users given a role or an array of roles
 * $meta_filter can be '', MAILUSERS_ACCEPT_NOTIFICATION_USER_META, or MAILUSERS_ACCEPT_MASS_EMAIL_USER_META
 */
function mailusers_get_recipients_from_roles($roles, $exclude_id='', $meta_filter = '') {
	global $wpdb;
	$table_users = $wpdb->prefix . "wpsb_users";

	if (empty($roles)) {
		return array();
	}

	// Build role filter for the list of roles
	//--
	$role_count = count($roles);
	$capability_filter = '';
	for ($i=0; $i<$role_count; $i++) {
		$capability_filter .= "meta_value like '%" . $roles[$i] . "%'";
		if ($i!=$role_count-1) {
			$capability_filter .= ' OR ';
		}
	}

	// Additional filter on the meta_filters if necessary
	//--
	if ($meta_filter!='') {
		// Get ids corresponding to the roles
		//--
	    $ids = $wpdb->get_results(
				  "SELECT id "
				. "FROM $wpdb->usermeta, " . $table_users . " "
				. "WHERE "
				. " (user_id = id) "
				. ($exclude_id!='' ? ' AND (id<>' . $exclude_id . ')' : '')
				. " AND (meta_key = '" . $wpdb->prefix . "capabilities') "
				. " AND (" . $capability_filter . ") " );

		if (count($ids)<1) {
			return array();
		}
				
		$id_list = "";
		for ($i=0; $i<count($ids)-1; $i++) {
			$id_list .= $ids[$i]->id . ",";
		}
		$id_list .= $ids[count($ids)-1]->id;

		$users = $wpdb->get_results(
				  "SELECT id, user_email, display_name "
				. "FROM $wpdb->usermeta, " . $table_users . " "
				. "WHERE "
				. " (user_id = id) "
				. " AND (id in (" . $id_list . ")) "
				. " AND (meta_key = '" . $meta_filter ."') "
				. " AND (meta_value = 'true') " );
	} else {
	    $users = $wpdb->get_results(
				  "SELECT id, user_email, display_name "
				. "FROM $wpdb->usermeta, " . $table_users . " "
				. "WHERE "
				. " (user_id = id) "
				. ( $exclude_id!='' ? ' AND (id<>' . $exclude_id . ')' : '' )
				. " AND (meta_key = '" . $wpdb->prefix . "capabilities') "
				. " AND (" . $capability_filter . ") " );
	}

	return $users;
}

/**
 * Check Valid E-Mail Address
 */
function mailusers_is_valid_email($email) {
   $regex = '/^[A-z0-9][\w.+-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/';
   return (preg_match($regex, $email));
}

/**
 * Replace the template variables in a given text.
 */
function mailusers_replace_post_templates($text, $post_title, $post_excerpt, $post_url) {
	$text = preg_replace( '/%POST_TITLE%/', $post_title, $text );
	$text = preg_replace( '/%POST_EXCERPT%/', $post_excerpt, $text );
	$text = preg_replace( '/%POST_URL%/', $post_url, $text );
	return $text;
}

/**
 * Replace the template variables in a given text.
 */
function mailusers_replace_blog_templates($text) {
	$blog_url = get_option( 'siteurl' );
	$blog_name = get_option( 'blogname' );

	$text = preg_replace( '/%BLOG_URL%/', $blog_url, $text );
	$text = preg_replace( '/%BLOG_NAME%/', $blog_name, $text );
	return $text;
}

/**
 * Replace the template variables in a given text.
 */
function mailusers_replace_sender_templates($text, $sender_name) {
	$text = preg_replace( '/%FROM_NAME%/', $sender_name, $text );
	return $text;
}

/**
 * Delivers email to recipients in HTML or plaintext
 *
 * Returns number of recipients addressed in emails or false on internal error.
 */
function mailusers_send_mail($recipients = array(), $subject = '', $message = '', $type='plaintext', $sender_name='', $sender_email='') {
	$num_sent = 0; // return value
	if ( (empty($recipients)) ) { return $num_sent; }
	if ('' == $message) { return false; }

	$headers  = "From: \"$sender_name\" <$sender_email>\n";
	$headers .= "Return-Path: <" . $sender_email . ">\n";
	$headers .= "Reply-To: \"" . $sender_name . "\" <" . $sender_email . ">\n";
	$headers .= "X-Mailer: PHP" . phpversion() . "\n";

	$subject = stripslashes($subject);
	$message = stripslashes($message);

	if ('html' == $type) {
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: " . get_bloginfo('html_type') . "; charset=\"". get_bloginfo('charset') . "\"\n";
		$mailtext = "<html><head><title>" . $subject . "</title></head><body>" . $message . "</body></html>";
	} else {
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
		$message = preg_replace('|&[^a][^m][^p].{0,3};|', '', $message);
		$message = preg_replace('|&amp;|', '&', $message);
		$mailtext = wordwrap(strip_tags($message), 80, "\n");
	}

	// If unique recipient, send mail using to field.
	//--
	if (count($recipients)==1) {
		if (mailusers_is_valid_email($recipients[0]->user_email)) {
			$headers .= "To: \"" . $recipients[0]->display_name . "\" <" . $recipients[0]->user_email . ">\n";
			$headers .= "Cc: " . $sender_email . "\n\n";
			@wp_mail($sender_email, $subject, $mailtext, $headers);
			$num_sent++;
		} else {
			echo "<p class=\"error\">The email address of the user you are trying to send mail to is not a valid email address format.</p>";
			return $num_sent;
		}
		return $num_sent;
	}

	// If multiple recipients, use the BCC field
	//--
	$bcc = '';
	$bcc_limit = mailusers_get_max_bcc_recipients();

	if ( $bcc_limit>0 && (count($recipients)>$bcc_limit) ) {
		$count = 0;
		$sender_emailed = false;

		for ($i=0; $i<count($recipients); $i++) {
			$recipient = $recipients[$i]->user_email;

			if (!mailusers_is_valid_email($recipient)) { continue; }
			if ( empty($recipient) || ($sender_email == $recipient) ) { continue; }

			if ($bcc=='') {
				$bcc = "Bcc: $recipient";
			} else {
				$bcc .= ", $recipient";
			}

			$count++;

			if (($bcc_limit == $count) || ($i==count($recipients)-1)) {
				if (!$sender_emailed) {
					$newheaders = $headers . "To: \"" . $sender_name . "\" <" . $sender_email . ">\n" . "$bcc\n\n";
					$sender_emailed = true;
				} else {
					$newheaders = $headers . "$bcc\n\n";
				}
				@wp_mail($sender_email, $subject, $mailtext, $newheaders);
				$count = 0;
				$bcc = '';
			}

			$num_sent++;
		}
	} else {
		$headers .= "To: \"" . $sender_name . "\" <" . $sender_email . ">\n";

		for ($i=0; $i<count($recipients); $i++) {
			$recipient = $recipients[$i]->user_email;

			if (!mailusers_is_valid_email($recipient)) { echo "$recipient email not valid"; continue; }
			if ( empty($recipient) || ($sender_email == $recipient) ) { continue; }

			if ($bcc=='') {
				$bcc = "Bcc: $recipient";
			} else {
				$bcc .= ", $recipient";
			}
			$num_sent++;
		}
		$newheaders = $headers . "$bcc\n\n";
		@wp_mail($sender_email, $subject, $mailtext, $newheaders);
	}

	return $num_sent;
}
?>
