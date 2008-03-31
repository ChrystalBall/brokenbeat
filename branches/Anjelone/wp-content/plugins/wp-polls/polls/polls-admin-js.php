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
|	- Polls Admin Javascript File													|
|	- wp-content/plugins/polls/polls-admin-js.php							|
|																							|
+----------------------------------------------------------------+
*/


### Include wp-config.php
@require('../../../wp-config.php');
cache_javascript_headers();

### Determine polls-admin-ajax.php Path
$polls_admin_ajax_url = dirname($_SERVER['PHP_SELF']);
if(substr($polls_admin_ajax_url, -1) == '/') {
	$polls_admin_ajax_url  = substr($polls_admin_ajax_url, 0, -1);
}
?>
// Variables
var polls_admin_ajax_url = "<?php echo $polls_admin_ajax_url; ?>/polls-admin-ajax.php";
var polls_admin = new sack(polls_admin_ajax_url);
var global_poll_id = 0;
var global_poll_aid = 0;
var global_poll_aid_votes  = 0;

// Function: Delete Poll Message
function delete_poll_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	Fat.fade_element("poll-" + global_poll_id, null, 1000, "#FF3333");
	setTimeout("remove_poll()", 1000);
}

// Function: Remove Poll From Manage Poll
function remove_poll() {
	document.getElementById("manage_polls").removeChild(document.getElementById("poll-" + global_poll_id));
}

// Function: Delete Poll
function delete_poll(poll_id, poll_confirm) {
	delete_poll_confirm = confirm(poll_confirm);
	if(delete_poll_confirm) {
		global_poll_id = poll_id;
		polls_admin.reset();
		polls_admin.setVar("do", "<?php _e('Delete Poll', 'wp-polls'); ?>");
		polls_admin.setVar("pollq_id", poll_id);
		polls_admin.method = 'POST';
		polls_admin.element = 'message';
		polls_admin.onCompletion = delete_poll_message;
		polls_admin.runAJAX();
	}
}

// Function: Delete Poll Logs Message
function delete_poll_logs_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	document.getElementById("poll_logs").innerHTML = "<?php _e('No poll logs available.', 'wp-polls'); ?>";
}

// Function: Delete Poll Logs
function delete_poll_logs(poll_confirm) {
	delete_poll_logs_confirm = confirm(poll_confirm);
	if(delete_poll_logs_confirm) {
		if(document.getElementById("delete_logs_yes").checked == true) {
			polls_admin.reset();
			polls_admin.setVar("do", "<?php _e('Delete All Logs', 'wp-polls'); ?>");
			polls_admin.setVar("delete_logs_yes", "yes");
			polls_admin.method = 'POST';
			polls_admin.element = 'message';
			polls_admin.onCompletion = delete_poll_logs_message;
			polls_admin.runAJAX();
		} else {
			alert("<?php _e('Please check the \'Yes\' checkbox if you want to delete all logs.', 'wp-polls'); ?>");
		}
	}
}

// Function: Delete Individual Poll Logs Message
function delete_this_poll_logs_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	document.getElementById("poll_logs").innerHTML = "<?php _e('No poll logs available for this poll.', 'wp-polls'); ?>";
	document.getElementById("poll_logs_display").style.display = 'none';
	document.getElementById("poll_logs_display_none").style.display = 'block';
}

// Function: Delete Individual Poll Logs
function delete_this_poll_logs(poll_id, poll_confirm) {
	delete_poll_logs_confirm = confirm(poll_confirm);
	if(delete_poll_logs_confirm) {
		if(document.getElementById("delete_logs_yes").checked == true) {
			global_poll_id = poll_id;
			polls_admin.reset();
			polls_admin.setVar("do", "<?php _e('Delete Logs For This Poll Only', 'wp-polls'); ?>");
			polls_admin.setVar("delete_logs_yes", "yes");
			polls_admin.setVar("pollq_id", poll_id);
			polls_admin.method = 'POST';
			polls_admin.element = 'message';
			polls_admin.onCompletion = delete_this_poll_logs_message;
			polls_admin.runAJAX();
		} else {
			alert("<?php _e('Please check the \'Yes\' checkbox if you want to delete all logs for this poll ONLY.', 'wp-polls'); ?>");
		}
	}
}

// Function: Delete Poll Answer Message
function delete_poll_ans_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	Fat.fade_element("poll-answer-" + global_poll_aid, null, 1000, "#FF3333");
	setTimeout("remove_poll_ans()", 1000);
	document.getElementById('poll_total_votes').innerHTML = (parseInt(document.getElementById('poll_total_votes').innerHTML) - parseInt(global_poll_aid_votes));
	poll_total_votes = parseInt(document.getElementById('pollq_totalvotes').value);
	poll_answer_vote = parseInt(document.getElementById("polla_votes-" + global_poll_aid).value);
	poll_total_votes_new = (poll_total_votes - poll_answer_vote);
	if(poll_total_votes_new < 0) {
		poll_total_votes_new = 0;
	}
	document.getElementById('pollq_totalvotes').value = parseInt(poll_total_votes_new);
}

// Function: Remove Poll From Manage Poll
function remove_poll_ans() {
	document.getElementById("poll_answers").removeChild(document.getElementById("poll-answer-" + global_poll_aid));
}

// Function: Delete Poll Answer
function delete_poll_ans(poll_id, poll_aid, poll_aid_vote, poll_confirm) {
	delete_poll_ans_confirm = confirm(poll_confirm);
	if(delete_poll_ans_confirm) {
		global_poll_id = poll_id;
		global_poll_aid = poll_aid;
		global_poll_aid_votes = poll_aid_vote;
		polls_admin.reset();
		polls_admin.setVar("do", "<?php _e('Delete Poll Answer', 'wp-polls'); ?>");
		polls_admin.setVar("pollq_id", poll_id);
		polls_admin.setVar("polla_aid", poll_aid);
		polls_admin.method = 'POST';
		polls_admin.element = 'message';
		polls_admin.onCompletion = delete_poll_ans_message;
		polls_admin.runAJAX();
	}
}

// Function: Open Poll Message
function opening_poll_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	document.getElementById("open_poll").style.display = "none";
	document.getElementById("close_poll").style.display = "inline";
}

// Function: Open Poll
function opening_poll(poll_id, poll_confirm) {
	open_poll_confirm = confirm(poll_confirm);
	if(open_poll_confirm) {
		global_poll_id = poll_id;
		polls_admin.reset();
		polls_admin.setVar("do", "<?php _e('Open Poll', 'wp-polls'); ?>");
		polls_admin.setVar("pollq_id", poll_id);
		polls_admin.method = 'POST';
		polls_admin.element = 'message';
		polls_admin.onCompletion = opening_poll_message;
		polls_admin.runAJAX();
	}
}

// Function: Close Poll Message
function closing_poll_message() {
	document.getElementById('message').style.display = "block";
	Fat.fade_element("message", null, 3000, "#FFFF00");
	document.getElementById("open_poll").style.display = "inline";
	document.getElementById("close_poll").style.display = "none";
}

// Function: Close Poll
function closing_poll(poll_id, poll_confirm) {
	close_poll_confirm = confirm(poll_confirm);
	if(close_poll_confirm) {
		global_poll_id = poll_id;
		polls_admin.reset();
		polls_admin.setVar("do", "<?php _e('Close Poll', 'wp-polls'); ?>");
		polls_admin.setVar("pollq_id", poll_id);
		polls_admin.method = 'POST';
		polls_admin.element = 'message';
		polls_admin.onCompletion = closing_poll_message;
		polls_admin.runAJAX();
	}
}

// Function: Insert Poll Quick Tag
function insertPoll(where, myField) {
	var poll_id = prompt("<?php _e('Enter Poll ID', 'wp-polls'); ?>");
	while(isNaN(poll_id)) {
		poll_id = prompt("<?php _e('Error: Poll ID must be numeric', 'wp-polls'); ?>\n\n<?php _e('Please enter Poll ID again', 'wp-polls'); ?>");
	}
	if (poll_id > 0) {
		if(where == 'code') {
			edInsertContent(myField, '[poll=' + poll_id + ']');
		} else {
			return '[poll=' + poll_id + ']';
		}
	}
}