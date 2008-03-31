<?php
/*
Plugin Name: Ajax Comments-Reply
Plugin URI: http://blog.nahoya.com/archives/2006_04/109
Version: 1.0
Description: 可以在迴響之後跟隨回覆，使用Ajax無刷新
Author: Beata
Author URI: http://blog.nahoya.com
*/

$max_level = 5; //get_option("comments_reply_max_level");

function reply_column_checker() {
	global $wpdb;
	$column_name = 'comment_reply_ID';
	foreach ($wpdb->get_col("DESC $wpdb->comments", 0) as $column) {
		if ($column == $column_name) {
		    return true;
		}
	}
	$q = $wpdb->query("ALTER TABLE $wpdb->comments ADD COLUMN comment_reply_ID INT NOT NULL DEFAULT 0;");
	foreach ($wpdb->get_col("DESC $wpdb->comments", 0) as $column) {
		if  ($column == $column_name) {
			return true;
		}
	}
	return false;
}
function commentreply_load_scripts() {
	echo '<script type="text/javascript" src="'.get_settings('home').'/wp-content/plugins/ajaxcomment/comment.js"></script>';
	echo '<link rel="stylesheet" href="'.get_settings('home').'/wp-content/plugins/ajaxcomment/comment.css" type="text/css" media="screen" />';
}
function add_reply_id_formfield() {
	echo '<input type="hidden" name="comment_reply_ID" id="comment_reply_ID" value="0" />';
}
function add_reply_ID($id) {
	global $wpdb;
	$reply_id = mysql_escape_string($_REQUEST['comment_reply_ID']);
	$q = $wpdb->query("UPDATE $wpdb->comments SET comment_reply_ID='$reply_id' WHERE comment_ID='$id'");
}

add_action('wp_head','reply_column_checker');
add_action('wp_head','commentreply_load_scripts');
add_action('comment_post','add_reply_id');
?>