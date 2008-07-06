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
	if (	!current_user_can(MAILUSERS_EMAIL_SINGLE_USER_CAP)
		|| 	!current_user_can(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP)) {
		wp_die(__("You are not allowed to send emails to users.", MAILUSERS_I18N_DOMAIN));
	}

	if (!isset($send_users)) {
		$send_users = array();
	}

	if (!isset($mail_format)) {
		$mail_format = mailusers_get_default_mail_format();
	}

	if (!isset($subject)) {
		$subject = '';
	}

	if (!isset($mail_content)) {
		$mail_content = '';
	}

	get_currentuserinfo();
	$from_name = $user_identity;
	$from_address = $user_email;
?>

<div class="wrap">
	<h2><?php _e('Write an email to individual users', MAILUSERS_I18N_DOMAIN); ?></h2>

	<?php if (current_user_can(MAILUSERS_EMAIL_USER_GROUPS_CAP)) { ?>
	<ul class="subsubsub">
	<li>
		<?php _e('Individual users', MAILUSERS_I18N_DOMAIN) ?>
	</li>
	<li>
		| <a href="post-new.php?page=email-users/email_users_group_mail_form.php" title="<?php _e('Send a mail to one or more user groups.', MAILUSERS_I18N_DOMAIN) ?>">
			<?php _e('User groups', MAILUSERS_I18N_DOMAIN) ?>
		</a>
	</li>
	</ul>
	<?php } ?>

	<?php 	if (isset($err_msg) && $err_msg!='') { ?>
			<p class="error"><?php echo $err_msg; ?></p>
			<p><?php _e('Please correct the errors displayed above and try again.', MAILUSERS_I18N_DOMAIN); ?></p>
	<?php	} ?>

	<form name="SendEmail" action="post-new.php?page=email-users/email_users_send_user_mail.php" method="post">
		<input type="hidden" name="send" value="true" />
		<input type="hidden" name="fromName" value="<?php echo $from_name;?>" />
		<input type="hidden" name="fromAddress" value="<?php echo $from_address;?>" />

		<table class="form-table" width="100%" cellspacing="2" cellpadding="5">
		<tr>
			<th scope="row" valign="top"><?php _e('Mail format', MAILUSERS_I18N_DOMAIN); ?></th>
			<td><select name="mail_format" style="width: 158px;">
				<option value="html" <?php if ($mail_format=='html') echo 'selected="selected"'; ?>><?php _e('HTML', MAILUSERS_I18N_DOMAIN); ?></option>
				<option value="plaintext" <?php if ($mail_format=='plaintext') echo 'selected="selected"'; ?>><?php _e('Plain text', MAILUSERS_I18N_DOMAIN); ?></option>
			</select></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label><?php _e('Sender', MAILUSERS_I18N_DOMAIN); ?></label></th>
			<td><?php echo $from_name;?> &lt;<?php echo $from_address;?>&gt;</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="send_users"><?php _e('Recipients', MAILUSERS_I18N_DOMAIN); ?>
			<br/><br/>
			<small><?php
				if (!current_user_can(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP))
					_e('You are only allowed to select one user at a time.', MAILUSERS_I18N_DOMAIN);
				else
					_e('You can select multiple users by pressing the CTRL key.', MAILUSERS_I18N_DOMAIN);
				?>
			</small></label></th>
			<td>
				<select id="send_users" name="send_users[]" size="8" style="width: 654px; height: 250px;" <?php if (current_user_can(MAILUSERS_EMAIL_MULTIPLE_USERS_CAP)) echo 'multiple="multiple"'; ?> >
				<?php
					$users = mailusers_get_users($user_ID);
					foreach ($users as $user) {
				?>
					<option value="<?php echo $user->id; ?>" <?php
						echo (in_array($user->id, $send_users) ? ' selected="yes"' : '');?>>
						<?php echo __('User', MAILUSERS_I18N_DOMAIN) . ' - ' . $user->display_name; ?>
					</option>
				<?php
					}
				?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="subject"><?php _e('Subject', MAILUSERS_I18N_DOMAIN); ?></label></th>
			<td><input type="text" id="subject" name="subject" value="<?php echo format_to_edit($subject);?>" style="width: 647px;" /></td>
		</tr>
		<tr>
			<th scope="row" valign="top"><label for="mailContent"><?php _e('Message', MAILUSERS_I18N_DOMAIN); ?></label></th>
			<td><textarea rows="10" cols="80" name="mailContent" id="mailContent" style="width: 647px;"><?php echo stripslashes($mail_content);?></textarea>
			</td>
		</tr>
		</table>

		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Send Email', MAILUSERS_I18N_DOMAIN); ?> &raquo;" />
		</p>
	</form>
</div>
