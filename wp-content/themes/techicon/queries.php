<?php
function src_simple_recent_comments($src_count=7, $src_length=40, $pre_HTML='', $post_HTML='') {
	global $wpdb;
	
	$sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_date_gmt, comment_approved, comment_type, 
			SUBSTRING(comment_content,1,$src_length) AS com_excerpt 
		FROM $wpdb->comments 
		LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) 
		WHERE comment_approved = '1' AND comment_type = '' AND post_password = '' 
		ORDER BY comment_date_gmt DESC 
		LIMIT $src_count";
	$comments = $wpdb->get_results($sql);

	$output = $pre_HTML;
	foreach ($comments as $comment) {
		$output .= "&raquo; <a href=\"" . get_permalink($comment->ID) . "#comment-" . $comment->comment_ID  . "\" title=\"on " . $comment->post_title . "\">" . strip_tags($comment->com_excerpt) ."...</a><br />";
	}
	$output .= $post_HTML;
	echo $output;
}

function mdv_most_commented($no_posts = 5, $before = '&raquo; ', $after = '<br />', $show_pass_post = false, $duration='') {
    global $wpdb;
	$request = "SELECT ID, post_title, COUNT($wpdb->comments.comment_post_ID) AS 'comment_count' FROM $wpdb->posts, $wpdb->comments";
	$request .= " WHERE comment_approved = '1' AND $wpdb->posts.ID=$wpdb->comments.comment_post_ID AND post_status = 'publish'";
	if(!$show_pass_post) $request .= " AND post_password =''";

        if($duration !="") { $request .= " AND DATE_SUB(CURDATE(),INTERVAL ".$duration." DAY) < post_date ";
}

	$request .= " GROUP BY $wpdb->comments.comment_post_ID ORDER BY comment_count DESC LIMIT $no_posts";
    $posts = $wpdb->get_results($request);
    $output = '';
	if ($posts) {
		foreach ($posts as $post) {
			$post_title = stripslashes($post->post_title);
			$comment_count = $post->comment_count;
			$permalink = get_permalink($post->ID);
			$output .= $before . '<a href="' . $permalink . '" title="' . $post_title.'">' . $post_title . '</a> (' . $comment_count.')' . $after;
		}
	} else {
		$output .= $before . "None found" . $after;
	}
    echo $output;
}
?>