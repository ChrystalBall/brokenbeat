<?php

//
// Post functions
//

function get_attached_file( $attachment_id, $unfiltered = false ) {
	$file = get_post_meta( $attachment_id, '_wp_attached_file', true );
	if ( $unfiltered )
		return $file;
	return apply_filters( 'get_attached_file', $file, $attachment_id );
}

function update_attached_file( $attachment_id, $file ) {
	if ( !get_post( $attachment_id ) )
		return false;

	$old_file = get_attached_file( $attachment_id, true );

	$file = apply_filters( 'update_attached_file', $file, $attachment_id );

	if ( $old_file )
		return update_post_meta( $attachment_id, '_wp_attached_file', $file, $old_file );
	else
		return add_post_meta( $attachment_id, '_wp_attached_file', $file );
}

function &get_children($args = '', $output = OBJECT) {
	global $post_cache, $wpdb, $blog_id;

	if ( empty( $args ) ) {
		if ( isset( $GLOBALS['post'] ) ) {
			$args = 'post_parent=' . (int) $GLOBALS['post']->post_parent;
		} else {
			return false;
		}
	} elseif ( is_object( $args ) ) {
		$args = 'post_parent=' . (int) $args->post_parent;
	} elseif ( is_numeric( $args ) ) {
		$args = 'post_parent=' . (int) $args;
	}

	$defaults = array(
		'numberposts' => -1, 'post_type' => '',
		'post_status' => '', 'post_parent' => 0
	);

	$r = wp_parse_args( $args, $defaults );

	$children = get_posts( $r );

	if ( $children ) {
		foreach ( $children as $key => $child ) {
			$post_cache[$blog_id][$child->ID] =& $children[$key];
			$kids[$child->ID] =& $children[$key];
		}
	} else {
		return false;
	}

	if ( $output == OBJECT ) {
		return $kids;
	} elseif ( $output == ARRAY_A ) {
		foreach ( $kids as $kid )
			$weeuns[$kid->ID] = get_object_vars($kids[$kid->ID]);
		return $weeuns;
	} elseif ( $output == ARRAY_N ) {
		foreach ( $kids as $kid )
			$babes[$kid->ID] = array_values(get_object_vars($kids[$kid->ID]));
		return $babes;
	} else {
		return $kids;
	}
}

// get extended entry info (<!--more-->)
function get_extended($post) {
	//Match the new style more links
	if ( preg_match('/<!--more(.*?)?-->/', $post, $matches) ) {
		list($main, $extended) = explode($matches[0], $post, 2);
	} else {
		$main = $post;
		$extended = '';
	}

	// Strip leading and trailing whitespace
	$main = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $main);
	$extended = preg_replace('/^[\s]*(.*)[\s]*$/', '\\1', $extended);

	return array('main' => $main, 'extended' => $extended);
}

// Retrieves post data given a post ID or post object.
// Handles post caching.
function &get_post(&$post, $output = OBJECT, $filter = 'raw') {
	global $post_cache, $wpdb, $blog_id;

	if ( empty($post) ) {
		if ( isset($GLOBALS['post']) )
			$_post = & $GLOBALS['post'];
		else
			$_post = null;
	} elseif ( is_object($post) ) {
		if ( 'page' == $post->post_type )
			return get_page($post, $output, $filter);
		if ( !isset($post_cache[$blog_id][$post->ID]) )
			$post_cache[$blog_id][$post->ID] = &$post;
		$_post = & $post_cache[$blog_id][$post->ID];
	} else {
		$post = (int) $post;
		if ( isset($post_cache[$blog_id][$post]) )
			$_post = & $post_cache[$blog_id][$post];
		elseif ( $_post = wp_cache_get($post, 'pages') )
			return get_page($_post, $output, $filter);
		else {
			$query = "SELECT * FROM $wpdb->posts WHERE ID = '$post' LIMIT 1";
			$_post = & $wpdb->get_row($query);
			if ( 'page' == $_post->post_type )
				return get_page($_post, $output, $filter);
			$post_cache[$blog_id][$post] = & $_post;
		}
	}

	if ( defined('WP_IMPORTING') )
		unset($post_cache[$blog_id]);

	$_post = sanitize_post($_post, $filter);

	if ( $output == OBJECT ) {
		return $_post;
	} elseif ( $output == ARRAY_A ) {
		return get_object_vars($_post);
	} elseif ( $output == ARRAY_N ) {
		return array_values(get_object_vars($_post));
	} else {
		return $_post;
	}
}

function get_post_field( $field, $post, $context = 'display' ) {
	$post = (int) $post;
	$post = get_post( $post );

	if ( is_wp_error($post) )
		return $post;

	if ( !is_object($post) )
		return '';

	if ( !isset($post->$field) )
		return '';

	return sanitize_post_field($field, $post->$field, $post->ID, $context);
}

// Takes a post ID, returns its mime type.
function get_post_mime_type($ID = '') {
	$post = & get_post($ID);

	if ( is_object($post) )
		return $post->post_mime_type;

	return false;
}

function get_post_status($ID = '') {
	$post = get_post($ID);

	if ( is_object($post) ) {
		if ( ('attachment' == $post->post_type) && $post->post_parent && ($post->ID != $post->post_parent) )
			return get_post_status($post->post_parent);
		else
			return $post->post_status;
	}

	return false;
}

function get_post_type($post = false) {
	global $wpdb, $posts;

	if ( false === $post )
		$post = $posts[0];
	elseif ( (int) $post )
		$post = get_post($post, OBJECT);

	if ( is_object($post) )
		return $post->post_type;

	return false;
}

function get_posts($args) {
	global $wpdb;

	$defaults = array(
		'numberposts' => 5, 'offset' => 0,
		'category' => 0, 'orderby' => 'post_date',
		'order' => 'DESC', 'include' => '',
		'exclude' => '', 'meta_key' => '',
		'meta_value' =>'', 'post_type' => 'post',
		'post_status' => 'publish', 'post_parent' => 0
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$numberposts = (int) $numberposts;
	$offset = (int) $offset;
	$category = (int) $category;
	$post_parent = (int) $post_parent;

	$inclusions = '';
	if ( !empty($include) ) {
		$offset = 0;    //ignore offset, category, exclude, meta_key, and meta_value, post_parent if using include
		$category = 0;
		$exclude = '';
		$meta_key = '';
		$meta_value = '';
		$post_parent = 0;
		$incposts = preg_split('/[\s,]+/',$include);
		$numberposts = count($incposts);  // only the number of posts included
		if ( count($incposts) ) {
			foreach ( $incposts as $incpost ) {
				if (empty($inclusions))
					$inclusions = ' AND ( ID = ' . intval($incpost) . ' ';
				else
					$inclusions .= ' OR ID = ' . intval($incpost) . ' ';
			}
		}
	}
	if (!empty($inclusions))
		$inclusions .= ')';

	$exclusions = '';
	if ( !empty($exclude) ) {
		$exposts = preg_split('/[\s,]+/',$exclude);
		if ( count($exposts) ) {
			foreach ( $exposts as $expost ) {
				if (empty($exclusions))
					$exclusions = ' AND ( ID <> ' . intval($expost) . ' ';
				else
					$exclusions .= ' AND ID <> ' . intval($expost) . ' ';
			}
		}
	}
	if (!empty($exclusions))
		$exclusions .= ')';

	$query  = "SELECT DISTINCT * FROM $wpdb->posts ";
	$query .= empty( $category ) ? '' : ", $wpdb->term_relationships, $wpdb->term_taxonomy  ";
	$query .= empty( $meta_key ) ? '' : ", $wpdb->postmeta ";
	$query .= " WHERE 1=1 ";
	$query .= empty( $post_type ) ? '' : "AND post_type = '$post_type' ";
	$query .= empty( $post_status ) ? '' : "AND post_status = '$post_status' ";
	$query .= "$exclusions $inclusions " ;
	$query .= empty( $category ) ? '' : "AND ($wpdb->posts.ID = $wpdb->term_relationships.object_id AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id AND $wpdb->term_taxonomy.term_id = " . $category. ") ";
	$query .= empty( $post_parent ) ? '' : "AND $wpdb->posts.post_parent = '$post_parent' ";
	$query .= empty( $meta_key ) | empty($meta_value)  ? '' : " AND ($wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' AND $wpdb->postmeta.meta_value = '$meta_value' )";
	$query .= " GROUP BY $wpdb->posts.ID ORDER BY " . $orderby . ' ' . $order;
	if ( 0 < $numberposts )
		$query .= " LIMIT " . $offset . ',' . $numberposts;

	$posts = $wpdb->get_results($query);

	update_post_caches($posts);

	return $posts;
}

//
// Post meta functions
//

function add_post_meta($post_id, $key, $value, $unique = false) {
	global $wpdb, $post_meta_cache, $blog_id;

	$post_id = (int) $post_id;

	if ( $unique ) {
		if ( $wpdb->get_var("SELECT meta_key FROM $wpdb->postmeta WHERE meta_key = '$key' AND post_id = '$post_id'") ) {
			return false;
		}
	}

	$post_meta_cache[$blog_id][$post_id][$key][] = $value;

	$value = maybe_serialize($value);
	$value = $wpdb->escape($value);

	$wpdb->query("INSERT INTO $wpdb->postmeta (post_id,meta_key,meta_value) VALUES ('$post_id','$key','$value')");

	return true;
}

function delete_post_meta($post_id, $key, $value = '') {
	global $wpdb, $post_meta_cache, $blog_id;

	$post_id = (int) $post_id;

	if ( empty($value) ) {
		$meta_id = $wpdb->get_var("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '$key'");
	} else {
		$meta_id = $wpdb->get_var("SELECT meta_id FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '$key' AND meta_value = '$value'");
	}

	if ( !$meta_id )
		return false;

	if ( empty($value) ) {
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '$key'");
		unset($post_meta_cache[$blog_id][$post_id][$key]);
	} else {
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id = '$post_id' AND meta_key = '$key' AND meta_value = '$value'");
		$cache_key = $post_meta_cache[$blog_id][$post_id][$key];
		if ($cache_key) foreach ( $cache_key as $index => $data )
			if ( $data == $value )
				unset($post_meta_cache[$blog_id][$post_id][$key][$index]);
	}

	unset($post_meta_cache[$blog_id][$post_id][$key]);

	return true;
}

function get_post_meta($post_id, $key, $single = false) {
	global $wpdb, $post_meta_cache, $blog_id;

	$post_id = (int) $post_id;

	if ( isset($post_meta_cache[$blog_id][$post_id][$key]) ) {
		if ( $single ) {
			return maybe_unserialize( $post_meta_cache[$blog_id][$post_id][$key][0] );
		} else {
			return maybe_unserialize( $post_meta_cache[$blog_id][$post_id][$key] );
		}
	}

	if ( !isset($post_meta_cache[$blog_id][$post_id]) )
		update_postmeta_cache($post_id);

	if ( $single ) {
		if ( isset($post_meta_cache[$blog_id][$post_id][$key][0]) )
			return maybe_unserialize($post_meta_cache[$blog_id][$post_id][$key][0]);
		else
			return '';
	}	else {
		return maybe_unserialize($post_meta_cache[$blog_id][$post_id][$key]);
	}
}

function update_post_meta($post_id, $key, $value, $prev_value = '') {
	global $wpdb, $post_meta_cache, $blog_id;

	$post_id = (int) $post_id;

	$original_value = $value;
	$value = maybe_serialize($value);
	$value = $wpdb->escape($value);

	$original_prev = $prev_value;
	$prev_value = maybe_serialize($prev_value);
	$prev_value = $wpdb->escape($prev_value);

	if (! $wpdb->get_var("SELECT meta_key FROM $wpdb->postmeta WHERE meta_key = '$key' AND post_id = '$post_id'") ) {
		return false;
	}

	if ( empty($prev_value) ) {
		$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '$value' WHERE meta_key = '$key' AND post_id = '$post_id'");
		$cache_key = $post_meta_cache[$blog_id][$post_id][$key];
		if ( !empty($cache_key) )
			foreach ($cache_key as $index => $data)
				$post_meta_cache[$blog_id][$post_id][$key][$index] = $original_value;
	} else {
		$wpdb->query("UPDATE $wpdb->postmeta SET meta_value = '$value' WHERE meta_key = '$key' AND post_id = '$post_id' AND meta_value = '$prev_value'");
		$cache_key = $post_meta_cache[$blog_id][$post_id][$key];
		if ( !empty($cache_key) )
			foreach ($cache_key as $index => $data)
				if ( $data == $original_prev )
					$post_meta_cache[$blog_id][$post_id][$key][$index] = $original_value;
	}

	return true;
}


function delete_post_meta_by_key($post_meta_key) {
	global $wpdb, $post_meta_cache, $blog_id;
	$post_meta_key = $wpdb->escape($post_meta_key);
	if ( $wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '$post_meta_key'") ) {
		unset($post_meta_cache[$blog_id]); // not worth doing the work to iterate through the cache
		return true;
	}
	return false;
}


function get_post_custom($post_id = 0) {
	global $id, $post_meta_cache, $wpdb, $blog_id;

	if ( !$post_id )
		$post_id = (int) $id;

	$post_id = (int) $post_id;

	if ( !isset($post_meta_cache[$blog_id][$post_id]) )
		update_postmeta_cache($post_id);

	return $post_meta_cache[$blog_id][$post_id];
}

function get_post_custom_keys( $post_id = 0 ) {
	$custom = get_post_custom( $post_id );

	if ( !is_array($custom) )
		return;

	if ( $keys = array_keys($custom) )
		return $keys;
}


function get_post_custom_values( $key = '', $post_id = 0 ) {
	$custom = get_post_custom($post_id);

	return $custom[$key];
}

function sanitize_post($post, $context = 'display') {
	// TODO: Use array keys instead of hard coded list
	$fields = array('post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_content_filtered', 'post_title', 'post_excerpt', 'post_status', 'post_type', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_date', 'post_date_gmt', 'post_parent', 'menu_order', 'post_mime_type', 'post_category');

	$do_object = false;
	if ( is_object($post) )
		$do_object = true;

	foreach ( $fields as $field ) {
		if ( $do_object )
			$post->$field = sanitize_post_field($field, $post->$field, $post->ID, $context);
		else
			$post[$field] = sanitize_post_field($field, $post[$field], $post['ID'], $context);
	}

	return $post;
}

function sanitize_post_field($field, $value, $post_id, $context) {
	$int_fields = array('ID', 'post_parent', 'menu_order');
	if ( in_array($field, $int_fields) )
		$value = (int) $value;

	if ( 'raw' == $context )
		return $value;

	$prefixed = false;
	if ( false !== strpos($field, 'post_') ) {
		$prefixed = true;
		$field_no_prefix = str_replace('post_', '', $field);
	}

	if ( 'edit' == $context ) {
		$format_to_edit = array('post_content', 'post_excerpt', 'post_title', 'post_password');

		if ( $prefixed ) {
			$value = apply_filters("edit_$field", $value, $post_id);
			// Old school
			$value = apply_filters("${field_no_prefix}_edit_pre", $value, $post_id);
		} else {
			$value = apply_filters("edit_post_$field", $value, $post_id);
		}

		if ( in_array($field, $format_to_edit) ) {
			if ( 'post_content' == $field )
				$value = format_to_edit($value, user_can_richedit());
			else
				$value = format_to_edit($value);
		} else {
			$value = attribute_escape($value);
		}
	} else if ( 'db' == $context ) {
		if ( $prefixed ) {
			$value = apply_filters("pre_$field", $value);
			$value = apply_filters("${field_no_prefix}_save_pre", $value);
		} else {
			$value = apply_filters("pre_post_$field", $value);
			$value = apply_filters("${field}_pre", $value);
		}
	} else {
		// Use display filters by default.
		if ( $prefixed )
			$value = apply_filters($field, $value, $post_id, $context);
		else
			$value = apply_filters("post_$field", $value, $post_id, $context);
	}

	if ( 'attribute' == $context )
		$value = attribute_escape($value);
	else if ( 'js' == $context )
		$value = js_escape($value);

	return $value;
}

function wp_delete_post($postid = 0) {
	global $wpdb, $wp_rewrite;
	$postid = (int) $postid;

	if ( !$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = $postid") )
		return $post;

	if ( 'attachment' == $post->post_type )
		return wp_delete_attachment($postid);

	do_action('delete_post', $postid);

	// TODO delete for pluggable post taxonomies too
	wp_delete_object_term_relationships($postid, array('category', 'post_tag'));

	if ( 'page' == $post->post_type )
		$wpdb->query("UPDATE $wpdb->posts SET post_parent = $post->post_parent WHERE post_parent = $postid AND post_type = 'page'");

	$wpdb->query("UPDATE $wpdb->posts SET post_parent = $post->post_parent WHERE post_parent = $postid AND post_type = 'attachment'");

	$wpdb->query("DELETE FROM $wpdb->posts WHERE ID = $postid");

	$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_post_ID = $postid");

	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id = $postid");

	if ( 'page' == $post->post_type ) {
		clean_page_cache($postid);
		$wp_rewrite->flush_rules();
	}

	do_action('deleted_post', $postid);

	return $post;
}

function wp_get_post_categories( $post_id = 0, $args = array() ) {
	$post_id = (int) $post_id;

	$defaults = array('fields' => 'ids');
	$args = wp_parse_args( $args, $defaults );

	$cats = wp_get_object_terms($post_id, 'category', $args);
	return $cats;
}

function wp_get_post_tags( $post_id = 0, $args = array() ) {
	$post_id = (int) $post_id;

	$defaults = array('fields' => 'all');
	$args = wp_parse_args( $args, $defaults );

	$tags = wp_get_object_terms($post_id, 'post_tag', $args);

	return $tags;
}

function wp_get_post_events ( $post_id = 0, $args = array() ) {
	$post_id = (int) $post_id;

	$defaults = array('fields' => 'all');
	$args = wp_parse_args( $args, $defaults );

	$tags = wp_get_object_terms($post_id, 'post_tag', $args);

	return $tags;
}

function wp_get_recent_posts($num = 10) {
	global $wpdb;

	// Set the limit clause, if we got a limit
	$num = (int) $num;
	if ($num) {
		$limit = "LIMIT $num";
	}

	$sql = "SELECT * FROM $wpdb->posts WHERE post_type = 'post' ORDER BY post_date DESC $limit";
	$result = $wpdb->get_results($sql,ARRAY_A);

	return $result?$result:array();
}

function wp_get_single_post($postid = 0, $mode = OBJECT) {
	global $wpdb;

	$postid = (int) $postid;

	$post = get_post($postid, $mode);

	// Set categories and tags
	if($mode == OBJECT) {
		$post->post_category = wp_get_post_categories($postid);
		$post->tags_input = wp_get_post_tags($postid, array('fields' => 'names'));
	}
	else {
		$post['post_category'] = wp_get_post_categories($postid);
		$post['tags_input'] = wp_get_post_tags($postid, array('fields' => 'names'));
	}

	return $post;
}

function wp_insert_post($postarr = array()) {
	global $wpdb, $wp_rewrite, $allowedtags, $user_ID;

	$defaults = array('post_status' => 'draft', 'post_type' => 'post', 'post_author' => $user_ID,
		'ping_status' => get_option('default_ping_status'), 'post_parent' => 0,
		'menu_order' => 0, 'to_ping' =>  '', 'pinged' => '', 'post_password' => '');

	$postarr = wp_parse_args($postarr, $defaults);
	$postarr = sanitize_post($postarr, 'db');

	// export array as variables
	extract($postarr, EXTR_SKIP);

	// Are we updating or creating?
	$update = false;
	if ( !empty($ID) ) {
		$update = true;
		$previous_status = get_post_field('post_status', $ID);
	} else {
		$previous_status = 'new';
	}

	if ( ('' == $post_content) && ('' == $post_title) && ('' == $post_excerpt) )
		return 0;

	// Make sure we set a valid category
	if (0 == count($post_category) || !is_array($post_category)) {
		$post_category = array(get_option('default_category'));
	}

	if ( empty($post_author) )
		$post_author = $user_ID;

	if ( empty($post_status) )
		$post_status = 'draft';

	if ( empty($post_type) )
		$post_type = 'post';

	// Get the post ID.
	if ( $update )
		$post_ID = (int) $ID;

	// Create a valid post name.  Drafts are allowed to have an empty
	// post name.
	if ( empty($post_name) ) {
		if ( 'draft' != $post_status )
			$post_name = sanitize_title($post_title);
	} else {
		$post_name = sanitize_title($post_name);
	}

	// If the post date is empty (due to having been new or a draft) and status is not 'draft', set date to now
	if (empty($post_date)) {
		if ( !in_array($post_status, array('draft', 'pending')) )
			$post_date = current_time('mysql');
	}

	if (empty($post_date_gmt)) {
		if ( !in_array($post_status, array('draft', 'pending')) )
			$post_date_gmt = get_gmt_from_date($post_date);
	}

	if ( 'publish' == $post_status ) {
		$now = gmdate('Y-m-d H:i:59');
		if ( mysql2date('U', $post_date_gmt) > mysql2date('U', $now) )
			$post_status = 'future';
	}

	if ( empty($comment_status) ) {
		if ( $update )
			$comment_status = 'closed';
		else
			$comment_status = get_option('default_comment_status');
	}
	if ( empty($ping_status) )
		$ping_status = get_option('default_ping_status');

	if ( isset($to_ping) )
		$to_ping = preg_replace('|\s+|', "\n", $to_ping);
	else
		$to_ping = '';

	if ( ! isset($pinged) )
		$pinged = '';

	if ( isset($post_parent) )
		$post_parent = (int) $post_parent;
	else
		$post_parent = 0;

	if ( isset($menu_order) )
		$menu_order = (int) $menu_order;
	else
		$menu_order = 0;

	if ( !isset($post_password) )
		$post_password = '';

	if ( 'draft' != $post_status ) {
		$post_name_check = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name = '$post_name' AND post_type = '$post_type' AND ID != '$post_ID' AND post_parent = '$post_parent' LIMIT 1");

		if ($post_name_check || in_array($post_name, $wp_rewrite->feeds) ) {
			$suffix = 2;
			do {
				$alt_post_name = substr($post_name, 0, 200-(strlen($suffix)+1)). "-$suffix";
				$post_name_check = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name = '$alt_post_name' AND post_type = '$post_type' AND ID != '$post_ID' AND post_parent = '$post_parent' LIMIT 1");
				$suffix++;
			} while ($post_name_check);
			$post_name = $alt_post_name;
		}
	}

	if ($update) {
		$wpdb->query(
			"UPDATE IGNORE $wpdb->posts SET
			post_author = '$post_author',
			post_date = '$post_date',
			post_date_gmt = '$post_date_gmt',
			post_content = '$post_content',
			post_content_filtered = '$post_content_filtered',
			post_title = '$post_title',
			post_excerpt = '$post_excerpt',
			post_status = '$post_status',
			post_type = '$post_type',
			comment_status = '$comment_status',
			ping_status = '$ping_status',
			post_password = '$post_password',
			post_name = '$post_name',
			to_ping = '$to_ping',
			pinged = '$pinged',
			post_modified = '".current_time('mysql')."',
			post_modified_gmt = '".current_time('mysql',1)."',
			post_parent = '$post_parent',
			menu_order = '$menu_order'
			WHERE ID = $post_ID");
	} else {
		$wpdb->query(
			"INSERT IGNORE INTO $wpdb->posts
			(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type)
			VALUES
			('$post_author', '$post_date', '$post_date_gmt', '$post_content', '$post_content_filtered', '$post_title', '$post_excerpt', '$post_status', '$post_type', '$comment_status', '$ping_status', '$post_password', '$post_name', '$to_ping', '$pinged', '$post_date', '$post_date_gmt', '$post_parent', '$menu_order', '$post_mime_type')");
			$post_ID = (int) $wpdb->insert_id;
	}

	if ( empty($post_name) && 'draft' != $post_status ) {
		$post_name = sanitize_title($post_title, $post_ID);
		$wpdb->query( "UPDATE $wpdb->posts SET post_name = '$post_name' WHERE ID = '$post_ID'" );
	}

	wp_set_post_categories( $post_ID, $post_category );
	wp_set_post_tags( $post_ID, $tags_input );

	if ( 'page' == $post_type ) {
		clean_page_cache($post_ID);
	} else {
		clean_post_cache($post_ID);
	}

	// Set GUID
	if ( ! $update )
		$wpdb->query("UPDATE $wpdb->posts SET guid = '" . get_permalink($post_ID) . "' WHERE ID = '$post_ID'");

	$post = get_post($post_ID);
	if ( !empty($page_template) )
		$post->page_template = $page_template;

	wp_transition_post_status($post_status, $previous_status, $post);

	if ( $update)
		do_action('edit_post', $post_ID, $post);

	do_action('save_post', $post_ID, $post);
	do_action('wp_insert_post', $post_ID, $post);

	return $post_ID;
}

function wp_update_post($postarr = array()) {
	global $wpdb;

	if ( is_object($postarr) )
		$postarr = get_object_vars($postarr);

	// First, get all of the original fields
	$post = wp_get_single_post($postarr['ID'], ARRAY_A);

	// Escape data pulled from DB.
	$post = add_magic_quotes($post);

	// Passed post category list overwrites existing category list if not empty.
	if ( isset($postarr['post_category']) && is_array($postarr['post_category'])
			 && 0 != count($postarr['post_category']) )
		$post_cats = $postarr['post_category'];
	else
		$post_cats = $post['post_category'];

	// Drafts shouldn't be assigned a date unless explicitly done so by the user
	if ( in_array($post['post_status'], array('draft', 'pending')) && empty($postarr['edit_date']) && empty($postarr['post_date']) &&
			 ('0000-00-00 00:00:00' == $post['post_date']) )
		$clear_date = true;
	else
		$clear_date = false;

	// Merge old and new fields with new fields overwriting old ones.
	$postarr = array_merge($post, $postarr);
	$postarr['post_category'] = $post_cats;
	if ( $clear_date ) {
		$postarr['post_date'] = '';
		$postarr['post_date_gmt'] = '';
	}

	if ($postarr['post_type'] == 'attachment')
		return wp_insert_attachment($postarr);

	return wp_insert_post($postarr);
}

function wp_publish_post($post_id) {
	global $wpdb;

	$post = get_post($post_id);

	if ( empty($post) )
		return;

	if ( 'publish' == $post->post_status )
		return;

	$wpdb->query( "UPDATE $wpdb->posts SET post_status = 'publish' WHERE ID = '$post_id'" );

	$old_status = $post->post_status;
	$post->post_status = 'publish';
	wp_transition_post_status('publish', $old_status, $post);

	do_action('edit_post', $post_id, $post);
	do_action('save_post', $post_id, $post);
	do_action('wp_insert_post', $post_id, $post);
}

function wp_add_post_tags($post_id = 0, $tags = '') {
	return wp_set_post_tags($post_id, $tags, true);
}

function wp_set_post_tags( $post_id = 0, $tags = '', $append = false ) {
	/* $append - true = don't delete existing tags, just add on, false = replace the tags with the new tags */
	global $wpdb;

	$post_id = (int) $post_id;

	if ( !$post_id )
		return false;

	if ( empty($tags) )
		$tags = array();
	$tags = (is_array($tags)) ? $tags : explode( ',', $tags );
	wp_set_object_terms($post_id, $tags, 'post_tag', $append);
}

function wp_set_post_categories($post_ID = 0, $post_categories = array()) {
	global $wpdb;

	$post_ID = (int) $post_ID;
	// If $post_categories isn't already an array, make it one:
	if (!is_array($post_categories) || 0 == count($post_categories) || empty($post_categories))
		$post_categories = array(get_option('default_category'));
	else if ( 1 == count($post_categories) && '' == $post_categories[0] )
		return true;

	$post_categories = array_map('intval', $post_categories);
	$post_categories = array_unique($post_categories);

	return wp_set_object_terms($post_ID, $post_categories, 'category');
}	// wp_set_post_categories()

function wp_transition_post_status($new_status, $old_status, $post) {
	if ( $new_status != $old_status ) {
		do_action('transition_post_status', $new_status, $old_status, $post);
		do_action("${old_status}_to_$new_status", $post);
	}
	do_action("${new_status}_$post->post_type", $post->ID, $post);
}

//
// Trackback and ping functions
//

function add_ping($post_id, $uri) { // Add a URL to those already pung
	global $wpdb;
	$pung = $wpdb->get_var("SELECT pinged FROM $wpdb->posts WHERE ID = $post_id");
	$pung = trim($pung);
	$pung = preg_split('/\s/', $pung);
	$pung[] = $uri;
	$new = implode("\n", $pung);
	$new = apply_filters('add_ping', $new);
	return $wpdb->query("UPDATE $wpdb->posts SET pinged = '$new' WHERE ID = $post_id");
}

function get_enclosed($post_id) { // Get enclosures already enclosed for a post
	global $wpdb;
	$custom_fields = get_post_custom( $post_id );
	$pung = array();
	if ( !is_array( $custom_fields ) )
		return $pung;

	foreach ( $custom_fields as $key => $val ) {
		if ( 'enclosure' != $key || !is_array( $val ) )
			continue;
		foreach( $val as $enc ) {
			$enclosure = split( "\n", $enc );
			$pung[] = trim( $enclosure[ 0 ] );
		}
	}
	$pung = apply_filters('get_enclosed', $pung);
	return $pung;
}

function get_pung($post_id) { // Get URLs already pung for a post
	global $wpdb;
	$pung = $wpdb->get_var("SELECT pinged FROM $wpdb->posts WHERE ID = $post_id");
	$pung = trim($pung);
	$pung = preg_split('/\s/', $pung);
	$pung = apply_filters('get_pung', $pung);
	return $pung;
}

function get_to_ping($post_id) { // Get any URLs in the todo list
	global $wpdb;
	$to_ping = $wpdb->get_var("SELECT to_ping FROM $wpdb->posts WHERE ID = $post_id");
	$to_ping = trim($to_ping);
	$to_ping = preg_split('/\s/', $to_ping, -1, PREG_SPLIT_NO_EMPTY);
	$to_ping = apply_filters('get_to_ping',  $to_ping);
	return $to_ping;
}

// do trackbacks for a list of urls
// accepts a comma-separated list of trackback urls and a post id
function trackback_url_list($tb_list, $post_id) {
	if (!empty($tb_list)) {
		// get post data
		$postdata = wp_get_single_post($post_id, ARRAY_A);

		// import postdata as variables
		extract($postdata, EXTR_SKIP);

		// form an excerpt
		$excerpt = strip_tags($post_excerpt?$post_excerpt:$post_content);

		if (strlen($excerpt) > 255) {
			$excerpt = substr($excerpt,0,252) . '...';
		}

		$trackback_urls = explode(',', $tb_list);
		foreach($trackback_urls as $tb_url) {
				$tb_url = trim($tb_url);
				trackback($tb_url, stripslashes($post_title), $excerpt, $post_id);
		}
		}
}

//
// Page functions
//

function get_all_page_ids() {
	global $wpdb;

	if ( ! $page_ids = wp_cache_get('all_page_ids', 'pages') ) {
		$page_ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type = 'page'");
		wp_cache_add('all_page_ids', $page_ids, 'pages');
	}

	return $page_ids;
}


// Retrieves page data given a page ID or page object.
// Handles page caching.
function &get_page(&$page, $output = OBJECT, $filter = 'raw') {
	global $wpdb, $blog_id;

	if ( empty($page) ) {
		if ( isset( $GLOBALS['page'] ) && isset( $GLOBALS['page']->ID ) ) {
			$_page = & $GLOBALS['page'];
			wp_cache_add($_page->ID, $_page, 'pages');
		} else {
			// shouldn't we just return NULL at this point? ~ Mark
			$_page = null;
		}
	} elseif ( is_object($page) ) {
		if ( 'post' == $page->post_type )
			return get_post($page, $output, $filter);
		wp_cache_add($page->ID, $page, 'pages');
		$_page = $page;
	} else {
		$page = (int) $page;
		// first, check the cache
		if ( ! ( $_page = wp_cache_get($page, 'pages') ) ) {
			// not in the page cache?
			if ( isset($GLOBALS['page']->ID) && ($page == $GLOBALS['page']->ID) ) { // for is_page() views
				// I don't think this code ever gets executed ~ Mark
				$_page = & $GLOBALS['page'];
				wp_cache_add($_page->ID, $_page, 'pages');
			} elseif ( isset($GLOBALS['post_cache'][$blog_id][$page]) ) { // it's actually a page, and is cached
				return get_post($page, $output, $filter);
			} else { // it's not in any caches, so off to the DB we go
				// Why are we using assignment for this query?
				$_page = & $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID= '$page' LIMIT 1");
				if ( 'post' == $_page->post_type )
					return get_post($_page, $output, $filter);
				// Potential issue: we're not checking to see if the post_type = 'page'
				// So all non-'post' posts will get cached as pages.
				wp_cache_add($_page->ID, $_page, 'pages');
			}
		}
	}

	$_page = sanitize_post($_page, $filter);

	// at this point, one way or another, $_post contains the page object

	if ( $output == OBJECT ) {
		return $_page;
	} elseif ( $output == ARRAY_A ) {
		return get_object_vars($_page);
	} elseif ( $output == ARRAY_N ) {
		return array_values(get_object_vars($_page));
	} else {
		return $_page;
	}
}

function get_page_by_path($page_path, $output = OBJECT) {
	global $wpdb;
	$page_path = rawurlencode(urldecode($page_path));
	$page_path = str_replace('%2F', '/', $page_path);
	$page_path = str_replace('%20', ' ', $page_path);
	$page_paths = '/' . trim($page_path, '/');
	$leaf_path  = sanitize_title(basename($page_paths));
	$page_paths = explode('/', $page_paths);
	foreach($page_paths as $pathdir)
		$full_path .= ($pathdir!=''?'/':'') . sanitize_title($pathdir);

	$pages = $wpdb->get_results("SELECT ID, post_name, post_parent FROM $wpdb->posts WHERE post_name = '$leaf_path' AND post_type='page'");

	if ( empty($pages) )
		return NULL;

	foreach ($pages as $page) {
		$path = '/' . $leaf_path;
		$curpage = $page;
		while ($curpage->post_parent != 0) {
			$curpage = $wpdb->get_row("SELECT ID, post_name, post_parent FROM $wpdb->posts WHERE ID = '$curpage->post_parent' and post_type='page'");
			$path = '/' . $curpage->post_name . $path;
		}

		if ( $path == $full_path )
			return get_page($page->ID, $output);
	}

	return NULL;
}

function get_page_by_title($page_title, $output = OBJECT) {
	global $wpdb;
	$page_title = $wpdb->escape($page_title);
	$page = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '$page_title' AND post_type='page'");
	if ( $page )
		return get_page($page, $output);

	return NULL;
}

function &get_page_children($page_id, $pages) {
	global $page_cache, $blog_id;

	if ( empty($pages) )
		$pages = &$page_cache[$blog_id];

	$page_list = array();
	foreach ( $pages as $page ) {
		if ( $page->post_parent == $page_id ) {
			$page_list[] = $page;
			if ( $children = get_page_children($page->ID, $pages) )
				$page_list = array_merge($page_list, $children);
		}
	}
	return $page_list;
}

//fetches the pages returned as a FLAT list, but arranged in order of their hierarchy, i.e., child parents
//immediately follow their parents
function get_page_hierarchy($posts, $parent = 0) {
	$result = array ( );
	if ($posts) { foreach ($posts as $post) {
		if ($post->post_parent == $parent) {
			$result[$post->ID] = $post->post_name;
			$children = get_page_hierarchy($posts, $post->ID);
			$result += $children; //append $children to $result
		}
	} }
	return $result;
}

function get_page_uri($page_id) {
	$page = get_page($page_id);
	$uri = urldecode($page->post_name);

	// A page cannot be it's own parent.
	if ( $page->post_parent == $page->ID )
		return $uri;

	while ($page->post_parent != 0) {
		$page = get_page($page->post_parent);
		$uri = urldecode($page->post_name) . "/" . $uri;
	}

	return $uri;
}

function &get_pages($args = '') {
	global $wpdb;

	$defaults = array(
		'child_of' => 0, 'sort_order' => 'ASC',
		'sort_column' => 'post_title', 'hierarchical' => 1,
		'exclude' => '', 'include' => '',
		'meta_key' => '', 'meta_value' => '',
		'authors' => ''
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	$key = md5( serialize( $r ) );
	if ( $cache = wp_cache_get( 'get_pages', 'page' ) )
		if ( isset( $cache[ $key ] ) )
			return apply_filters('get_pages', $cache[ $key ], $r );

	$inclusions = '';
	if ( !empty($include) ) {
		$child_of = 0; //ignore child_of, exclude, meta_key, and meta_value params if using include
		$exclude = '';
		$meta_key = '';
		$meta_value = '';
		$incpages = preg_split('/[\s,]+/',$include);
		if ( count($incpages) ) {
			foreach ( $incpages as $incpage ) {
				if (empty($inclusions))
					$inclusions = ' AND ( ID = ' . intval($incpage) . ' ';
				else
					$inclusions .= ' OR ID = ' . intval($incpage) . ' ';
			}
		}
	}
	if (!empty($inclusions))
		$inclusions .= ')';

	$exclusions = '';
	if ( !empty($exclude) ) {
		$expages = preg_split('/[\s,]+/',$exclude);
		if ( count($expages) ) {
			foreach ( $expages as $expage ) {
				if (empty($exclusions))
					$exclusions = ' AND ( ID <> ' . intval($expage) . ' ';
				else
					$exclusions .= ' AND ID <> ' . intval($expage) . ' ';
			}
		}
	}
	if (!empty($exclusions))
		$exclusions .= ')';

	$author_query = '';
	if (!empty($authors)) {
		$post_authors = preg_split('/[\s,]+/',$authors);

		if ( count($post_authors) ) {
			foreach ( $post_authors as $post_author ) {
				//Do we have an author id or an author login?
				if ( 0 == intval($post_author) ) {
					$post_author = get_userdatabylogin($post_author);
					if ( empty($post_author) )
						continue;
					if ( empty($post_author->ID) )
						continue;
					$post_author = $post_author->ID;
				}

				if ( '' == $author_query )
					$author_query = ' post_author = ' . intval($post_author) . ' ';
				else
					$author_query .= ' OR post_author = ' . intval($post_author) . ' ';
			}
			if ( '' != $author_query )
				$author_query = " AND ($author_query)";
		}
	}

	$query = "SELECT * FROM $wpdb->posts " ;
	$query .= ( empty( $meta_key ) ? "" : ", $wpdb->postmeta " ) ;
	$query .= " WHERE (post_type = 'page' AND post_status = 'publish') $exclusions $inclusions " ;
	$query .= ( empty( $meta_key ) | empty($meta_value)  ? "" : " AND ($wpdb->posts.ID = $wpdb->postmeta.post_id AND $wpdb->postmeta.meta_key = '$meta_key' AND $wpdb->postmeta.meta_value = '$meta_value' )" ) ;
	$query .= $author_query;
	$query .= " ORDER BY " . $sort_column . " " . $sort_order ;

	$pages = $wpdb->get_results($query);

	if ( empty($pages) )
		return apply_filters('get_pages', array(), $r);

	// Update cache.
	update_page_cache($pages);

	if ( $child_of || $hierarchical )
		$pages = & get_page_children($child_of, $pages);

	$cache[ $key ] = $pages;
	wp_cache_set( 'get_pages', $cache, 'page' );

	$pages = apply_filters('get_pages', $pages, $r);

	return $pages;
}

function generate_page_uri_index() {
	global $wpdb;

	//get pages in order of hierarchy, i.e. children after parents
	$posts = get_page_hierarchy($wpdb->get_results("SELECT ID, post_name, post_parent FROM $wpdb->posts WHERE post_type = 'page'"));
	//now reverse it, because we need parents after children for rewrite rules to work properly
	$posts = array_reverse($posts, true);

	$page_uris = array();
	$page_attachment_uris = array();

	if ($posts) {

		foreach ($posts as $id => $post) {

			// URL => page name
			$uri = get_page_uri($id);
			$attachments = $wpdb->get_results("SELECT ID, post_name, post_parent FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = '$id'");
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					$attach_uri = get_page_uri($attachment->ID);
					$page_attachment_uris[$attach_uri] = $attachment->ID;
				}
			}

			$page_uris[$uri] = $id;
		}

		delete_option('page_uris');
		update_option('page_uris', $page_uris);

		if ( $page_attachment_uris ) {
			delete_option('page_attachment_uris');
			update_option('page_attachment_uris', $page_attachment_uris);
		}
	}
}

//
// Attachment functions
//

function is_local_attachment($url) {
	if (strpos($url, get_bloginfo('url')) === false)
		return false;
	if (strpos($url, get_bloginfo('url') . '/?attachment_id=') !== false)
		return true;
	if ( $id = url_to_postid($url) ) {
		$post = & get_post($id);
		if ( 'attachment' == $post->post_type )
			return true;
	}
	return false;
}

function wp_insert_attachment($object, $file = false, $parent = 0) {
	global $wpdb, $user_ID;

	$defaults = array('post_status' => 'draft', 'post_type' => 'post', 'post_author' => $user_ID,
		'ping_status' => get_option('default_ping_status'), 'post_parent' => 0,
		'menu_order' => 0, 'to_ping' =>  '', 'pinged' => '', 'post_password' => '');

	$object = wp_parse_args($object, $defaults);
	if ( !empty($parent) )
		$object['post_parent'] = $parent;

	$object = sanitize_post($object, 'db');

	// export array as variables
	extract($object, EXTR_SKIP);

	// Make sure we set a valid category
	if (0 == count($post_category) || !is_array($post_category)) {
		$post_category = array(get_option('default_category'));
	}

	if ( empty($post_author) )
		$post_author = $user_ID;

	$post_type = 'attachment';
	$post_status = 'inherit';

	// Are we updating or creating?
	$update = false;
	if ( !empty($ID) ) {
		$update = true;
		$post_ID = (int) $ID;
	}

	// Create a valid post name.
	if ( empty($post_name) )
		$post_name = sanitize_title($post_title);
	else
		$post_name = sanitize_title($post_name);

	$post_name_check =
		$wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name = '$post_name' AND post_status = 'inherit' AND ID != '$post_ID' LIMIT 1");

	if ($post_name_check) {
		$suffix = 2;
		while ($post_name_check) {
			$alt_post_name = $post_name . "-$suffix";
			$post_name_check = $wpdb->get_var("SELECT post_name FROM $wpdb->posts WHERE post_name = '$alt_post_name' AND post_status = 'inherit' AND ID != '$post_ID' AND post_parent = '$post_parent' LIMIT 1");
			$suffix++;
		}
		$post_name = $alt_post_name;
	}

	if ( empty($post_date) )
		$post_date = current_time('mysql');
	if ( empty($post_date_gmt) )
		$post_date_gmt = current_time('mysql', 1);

	if ( empty($comment_status) ) {
		if ( $update )
			$comment_status = 'closed';
		else
			$comment_status = get_option('default_comment_status');
	}
	if ( empty($ping_status) )
		$ping_status = get_option('default_ping_status');

	if ( isset($to_ping) )
		$to_ping = preg_replace('|\s+|', "\n", $to_ping);
	else
		$to_ping = '';

	if ( isset($post_parent) )
		$post_parent = (int) $post_parent;
	else
		$post_parent = 0;

	if ( isset($menu_order) )
		$menu_order = (int) $menu_order;
	else
		$menu_order = 0;

	if ( !isset($post_password) )
		$post_password = '';

	if ( ! isset($pinged) )
		$pinged = '';

	if ($update) {
		$wpdb->query(
			"UPDATE $wpdb->posts SET
			post_author = '$post_author',
			post_date = '$post_date',
			post_date_gmt = '$post_date_gmt',
			post_content = '$post_content',
			post_content_filtered = '$post_content_filtered',
			post_title = '$post_title',
			post_excerpt = '$post_excerpt',
			post_status = '$post_status',
			post_type = '$post_type',
			comment_status = '$comment_status',
			ping_status = '$ping_status',
			post_password = '$post_password',
			post_name = '$post_name',
			to_ping = '$to_ping',
			pinged = '$pinged',
			post_modified = '".current_time('mysql')."',
			post_modified_gmt = '".current_time('mysql',1)."',
			post_parent = '$post_parent',
			menu_order = '$menu_order',
			post_mime_type = '$post_mime_type',
			guid = '$guid'
			WHERE ID = $post_ID");
	} else {
		$wpdb->query(
			"INSERT INTO $wpdb->posts
			(post_author, post_date, post_date_gmt, post_content, post_content_filtered, post_title, post_excerpt,  post_status, post_type, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_parent, menu_order, post_mime_type, guid)
			VALUES
			('$post_author', '$post_date', '$post_date_gmt', '$post_content', '$post_content_filtered', '$post_title', '$post_excerpt', '$post_status', '$post_type', '$comment_status', '$ping_status', '$post_password', '$post_name', '$to_ping', '$pinged', '$post_date', '$post_date_gmt', '$post_parent', '$menu_order', '$post_mime_type', '$guid')");
			$post_ID = (int) $wpdb->insert_id;
	}

	if ( empty($post_name) ) {
		$post_name = sanitize_title($post_title, $post_ID);
		$wpdb->query( "UPDATE $wpdb->posts SET post_name = '$post_name' WHERE ID = '$post_ID'" );
	}

	wp_set_post_categories($post_ID, $post_category);

	if ( $file )
		update_attached_file( $post_ID, $file );

	clean_post_cache($post_ID);

	if ( $update) {
		do_action('edit_attachment', $post_ID);
	} else {
		do_action('add_attachment', $post_ID);
	}

	return $post_ID;
}

function wp_delete_attachment($postid) {
	global $wpdb;
	$postid = (int) $postid;

	if ( !$post = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE ID = '$postid'") )
		return $post;

	if ( 'attachment' != $post->post_type )
		return false;

	$meta = wp_get_attachment_metadata( $postid );
	$file = get_attached_file( $postid );

	// TODO delete for pluggable post taxonomies too
	wp_delete_object_term_relationships($postid, array('category', 'post_tag'));

	$wpdb->query("DELETE FROM $wpdb->posts WHERE ID = '$postid'");

	$wpdb->query("DELETE FROM $wpdb->comments WHERE comment_post_ID = '$postid'");

	$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id = '$postid'");

	if ( ! empty($meta['thumb']) ) {
		// Don't delete the thumb if another attachment uses it
		if (! $wpdb->get_row("SELECT meta_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%".$wpdb->escape($meta['thumb'])."%' AND post_id <> $postid")) {
			$thumbfile = str_replace(basename($file), $meta['thumb'], $file);
			$thumbfile = apply_filters('wp_delete_file', $thumbfile);
			@ unlink($thumbfile);
		}
	}

	$file = apply_filters('wp_delete_file', $file);

	if ( ! empty($file) )
		@ unlink($file);

	do_action('delete_attachment', $postid);

	return $post;
}

function wp_get_attachment_metadata( $post_id, $unfiltered = false ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	$data = get_post_meta( $post->ID, '_wp_attachment_metadata', true );
	if ( $unfiltered )
		return $data;
	return apply_filters( 'wp_get_attachment_metadata', $data, $post->ID );
}

function wp_update_attachment_metadata( $post_id, $data ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	$old_data = wp_get_attachment_metadata( $post->ID, true );

	$data = apply_filters( 'wp_update_attachment_metadata', $data, $post->ID );

	if ( $old_data )
		return update_post_meta( $post->ID, '_wp_attachment_metadata', $data, $old_data );
	else
		return add_post_meta( $post->ID, '_wp_attachment_metadata', $data );
}

function wp_get_attachment_url( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	$url = get_the_guid( $post->ID );

	if ( 'attachment' != $post->post_type || !$url )
		return false;

	return apply_filters( 'wp_get_attachment_url', $url, $post->ID );
}

function wp_get_attachment_thumb_file( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;
	if ( !$imagedata = wp_get_attachment_metadata( $post->ID ) )
		return false;

	$file = get_attached_file( $post->ID );

	if ( !empty($imagedata['thumb']) && ($thumbfile = str_replace(basename($file), $imagedata['thumb'], $file)) && file_exists($thumbfile) )
		return apply_filters( 'wp_get_attachment_thumb_file', $thumbfile, $post->ID );
	return false;
}

function wp_get_attachment_thumb_url( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;
	if ( !$url = wp_get_attachment_url( $post->ID ) )
		return false;

	if ( !$thumb = wp_get_attachment_thumb_file( $post->ID ) )
		return false;

	$url = str_replace(basename($url), basename($thumb), $url);

	return apply_filters( 'wp_get_attachment_thumb_url', $url, $post->ID );
}

function wp_attachment_is_image( $post_id = 0 ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	if ( !$file = get_attached_file( $post->ID ) )
		return false;

	$ext = preg_match('/\.([^.]+)$/', $file, $matches) ? strtolower($matches[1]) : false;

	$image_exts = array('jpg', 'jpeg', 'gif', 'png');

	if ( 'image/' == substr($post->post_mime_type, 0, 6) || $ext && 'import' == $post->post_mime_type && in_array($ext, $image_exts) )
		return true;
	return false;
}

function wp_mime_type_icon( $mime = 0 ) {
	$post_id = 0;
	if ( is_numeric($mime) ) {
		$mime = (int) $mime;
		if ( !$post =& get_post( $mime ) )
			return false;
		$post_id = (int) $post->ID;
		$mime = $post->post_mime_type;
	}

	if ( empty($mime) )
		return false;

	$icon_dir = apply_filters( 'icon_dir', get_template_directory() . '/images' );
	$icon_dir_uri = apply_filters( 'icon_dir_uri', get_template_directory_uri() . '/images' );

	$types = array(
		substr($mime, 0, strpos($mime, '/')),
		substr($mime, strpos($mime, '/') + 1),
		str_replace('/', '_', $mime)
	);

	$exts = array('jpg', 'gif', 'png');

	$src = false;

	foreach ( $types as $type ) {
		foreach ( $exts as $ext ) {
			$src_file = "$icon_dir/$type.$ext";
			if ( file_exists($src_file) ) {
				$src = "$icon_dir_uri/$type.$ext";
				break 2;
			}
		}
	}

	return apply_filters( 'wp_mime_type_icon', $src, $mime, $post_id ); // Last arg is 0 if function pass mime type.
}

function wp_check_for_changed_slugs($post_id) {
	if ( !strlen($_POST['wp-old-slug']) )
		return $post_id;

	$post = &get_post($post_id);

	// we're only concerned with published posts
	if ( $post->post_status != 'publish' || $post->post_type != 'post' )
		return $post_id;

	// only bother if the slug has changed
	if ( $post->post_name == $_POST['wp-old-slug'] )
		return $post_id;

	$old_slugs = (array) get_post_meta($post_id, '_wp_old_slug');

	// if we haven't added this old slug before, add it now
	if ( !count($old_slugs) || !in_array($_POST['wp-old-slug'], $old_slugs) )
		add_post_meta($post_id, '_wp_old_slug', $_POST['wp-old-slug']);

	// if the new slug was used previously, delete it from the list
	if ( in_array($post->post_name, $old_slugs) )
		delete_post_meta($post_id, '_wp_old_slug', $post->post_name);

	return $post_id;
}

/**
 * This function provides a standardized way to appropriately select on
 * the post_status of posts/pages. The function will return a piece of
 * SQL code that can be added to a WHERE clause; this SQL is constructed
 * to allow all published posts, and all private posts to which the user
 * has access.
 *
 * @param string $post_type currently only supports 'post' or 'page'.
 * @return string SQL code that can be added to a where clause.
 */
function get_private_posts_cap_sql($post_type) {
	global $user_ID;
	$cap = '';

	// Private posts
	if ($post_type == 'post') {
		$cap = 'read_private_posts';
	// Private pages
	} elseif ($post_type == 'page') {
		$cap = 'read_private_pages';
	// Dunno what it is, maybe plugins have their own post type?
	} else {
		$cap = apply_filters('pub_priv_sql_capability', $cap);

		if (empty($cap)) {
			// We don't know what it is, filters don't change anything,
			// so set the SQL up to return nothing.
			return '1 = 0';
		}
	}

	$sql = '(post_status = \'publish\'';

	if (current_user_can($cap)) {
		// Does the user have the capability to view private posts? Guess so.
		$sql .= ' OR post_status = \'private\'';
	} elseif (is_user_logged_in()) {
		// Users can view their own private posts.
		$sql .= ' OR post_status = \'private\' AND post_author = \'' . $user_ID . '\'';
	}

	$sql .= ')';

	return $sql;
}

function get_lastpostdate($timezone = 'server') {
	global $cache_lastpostdate, $pagenow, $wpdb, $blog_id;
	$add_seconds_blog = get_option('gmt_offset') * 3600;
	$add_seconds_server = date('Z');
	if ( !isset($cache_lastpostdate[$blog_id][$timezone]) ) {
		switch(strtolower($timezone)) {
			case 'gmt':
				$lastpostdate = $wpdb->get_var("SELECT post_date_gmt FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 1");
				break;
			case 'blog':
				$lastpostdate = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 1");
				break;
			case 'server':
				$lastpostdate = $wpdb->get_var("SELECT DATE_ADD(post_date_gmt, INTERVAL '$add_seconds_server' SECOND) FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date_gmt DESC LIMIT 1");
				break;
		}
		$cache_lastpostdate[$blog_id][$timezone] = $lastpostdate;
	} else {
		$lastpostdate = $cache_lastpostdate[$blog_id][$timezone];
	}
	return apply_filters( 'get_lastpostdate', $lastpostdate );
}

function get_lastpostmodified($timezone = 'server') {
	global $cache_lastpostmodified, $pagenow, $wpdb, $blog_id;
	$add_seconds_blog = get_option('gmt_offset') * 3600;
	$add_seconds_server = date('Z');
	if ( !isset($cache_lastpostmodified[$blog_id][$timezone]) ) {
		switch(strtolower($timezone)) {
			case 'gmt':
				$lastpostmodified = $wpdb->get_var("SELECT post_modified_gmt FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1");
				break;
			case 'blog':
				$lastpostmodified = $wpdb->get_var("SELECT post_modified FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1");
				break;
			case 'server':
				$lastpostmodified = $wpdb->get_var("SELECT DATE_ADD(post_modified_gmt, INTERVAL '$add_seconds_server' SECOND) FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_modified_gmt DESC LIMIT 1");
				break;
		}
		$lastpostdate = get_lastpostdate($timezone);
		if ( $lastpostdate > $lastpostmodified ) {
			$lastpostmodified = $lastpostdate;
		}
		$cache_lastpostmodified[$blog_id][$timezone] = $lastpostmodified;
	} else {
		$lastpostmodified = $cache_lastpostmodified[$blog_id][$timezone];
	}
	return apply_filters( 'get_lastpostmodified', $lastpostmodified );
}

//
// Cache
//

function update_post_cache(&$posts) {
	global $post_cache, $blog_id;

	if ( !$posts )
		return;

	for ($i = 0; $i < count($posts); $i++) {
		$post_cache[$blog_id][$posts[$i]->ID] = &$posts[$i];
	}
}

function clean_post_cache($id) {
	global $post_cache, $post_meta_cache, $post_term_cache, $blog_id;

	if ( isset( $post_cache[$blog_id][$id] ) )
		unset( $post_cache[$blog_id][$id] );

	if ( isset ($post_meta_cache[$blog_id][$id] ) )
		unset( $post_meta_cache[$blog_id][$id] );

	clean_object_term_cache($id, 'post');
}

function update_page_cache(&$pages) {
	global $page_cache, $blog_id;

	if ( !$pages )
		return;

	for ($i = 0; $i < count($pages); $i++) {
		$page_cache[$blog_id][$pages[$i]->ID] = &$pages[$i];
		wp_cache_add($pages[$i]->ID, $pages[$i], 'pages');
	}
}

function clean_page_cache($id) {
	global $page_cache, $blog_id;

	if ( isset( $page_cache[$blog_id][$id] ) )
		unset( $page_cache[$blog_id][$id] );

	wp_cache_delete($id, 'pages');
	wp_cache_delete( 'all_page_ids', 'pages' );
	wp_cache_delete( 'get_pages', 'page' );
}

function update_post_caches(&$posts) {
	global $post_cache;
	global $wpdb, $blog_id;

	// No point in doing all this work if we didn't match any posts.
	if ( !$posts )
		return;

	// Get the categories for all the posts
	for ($i = 0; $i < count($posts); $i++) {
		$post_id_array[] = $posts[$i]->ID;
		$post_cache[$blog_id][$posts[$i]->ID] = &$posts[$i];
	}

	$post_id_list = implode(',', $post_id_array);

	update_object_term_cache($post_id_list, 'post');

	update_postmeta_cache($post_id_list);
}

function update_postmeta_cache($post_id_list = '') {
	global $wpdb, $post_meta_cache, $blog_id;

	// We should validate this comma-separated list for the upcoming SQL query
	$post_id_list = preg_replace('|[^0-9,]|', '', $post_id_list);

	if ( empty( $post_id_list ) )
		return false;

	// we're marking each post as having its meta cached (with no keys... empty array), to prevent posts with no meta keys from being queried again
	// any posts that DO have keys will have this empty array overwritten with a proper array, down below
	$post_id_array = (array) explode(',', $post_id_list);
	$count = count( $post_id_array);
	for ( $i = 0; $i < $count; $i++ ) {
		$post_id = (int) $post_id_array[ $i ];
		if ( isset( $post_meta_cache[$blog_id][$post_id] ) ) { // If the meta is already cached
			unset( $post_id_array[ $i ] );
			continue;
		}
		$post_meta_cache[$blog_id][$post_id] = array();
	}
	if ( count( $post_id_array ) == 0 )
		return;
	$post_id_list = join( ',', $post_id_array ); // with already cached stuff removeds

	// Get post-meta info
	if ( $meta_list = $wpdb->get_results("SELECT post_id, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id IN($post_id_list) ORDER BY post_id, meta_key", ARRAY_A) ) {
		// Change from flat structure to hierarchical:
		if ( !isset($post_meta_cache) )
			$post_meta_cache[$blog_id] = array();

		foreach ($meta_list as $metarow) {
			$mpid = (int) $metarow['post_id'];
			$mkey = $metarow['meta_key'];
			$mval = $metarow['meta_value'];

			// Force subkeys to be array type:
			if ( !isset($post_meta_cache[$blog_id][$mpid]) || !is_array($post_meta_cache[$blog_id][$mpid]) )
				$post_meta_cache[$blog_id][$mpid] = array();
			if ( !isset($post_meta_cache[$blog_id][$mpid]["$mkey"]) || !is_array($post_meta_cache[$blog_id][$mpid]["$mkey"]) )
				$post_meta_cache[$blog_id][$mpid]["$mkey"] = array();

			// Add a value to the current pid/key:
			$post_meta_cache[$blog_id][$mpid][$mkey][] = $mval;
		}
	}
}

//
// Hooks
//

function _transition_post_status($new_status, $old_status, $post) {
	global $wpdb;

	if ( $old_status != 'publish' && $new_status == 'publish' ) {
			// Reset GUID if transitioning to publish.
			$wpdb->query("UPDATE $wpdb->posts SET guid = '" . get_permalink($post->ID) . "' WHERE ID = '$post->ID'");
			do_action('private_to_published', $post->ID);  // Deprecated, use private_to_publish
	}

	// Always clears the hook in case the post status bounced from future to draft.
	wp_clear_scheduled_hook('publish_future_post', $post->ID);
}

function _future_post_hook($post_id, $post) {
	// Schedule publication.
	wp_clear_scheduled_hook( 'publish_future_post', $post->ID );
	wp_schedule_single_event(strtotime($post->post_date_gmt. ' GMT'), 'publish_future_post', array($post->ID));
}

function _publish_post_hook($post_id) {
	global $wpdb;

	if ( defined('XMLRPC_REQUEST') )
		do_action('xmlrpc_publish_post', $post_id);
	if ( defined('APP_REQUEST') )
		do_action('app_publish_post', $post_id);

	if ( defined('WP_IMPORTING') )
		return;

	$post = get_post($post_id);

	if ( get_option('default_pingback_flag') )
		$result = $wpdb->query("
			INSERT INTO $wpdb->postmeta
			(post_id,meta_key,meta_value)
			VALUES ('$post_id','_pingme','1')
		");
	$result = $wpdb->query("
		INSERT INTO $wpdb->postmeta
		(post_id,meta_key,meta_value)
		VALUES ('$post_id','_encloseme','1')
	");
	wp_schedule_single_event(time(), 'do_pings');
}

function _save_post_hook($post_id, $post) {
	if ( $post->post_type == 'page' ) {
		if ( !empty($post->page_template) )
			if ( ! update_post_meta($post_id, '_wp_page_template',  $post->page_template))
				add_post_meta($post_id, '_wp_page_template',  $post->page_template, true);

		clean_page_cache($post_id);
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	} else {
		clean_post_cache($post_id);
	}
}

?>
