<div id="rsidebar">
<ul>
	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar(1) ) : else : ?>	
    
    <li><h2>Recent Comments</h2>
		
				     <?php
    global $wpdb;

    $sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID,
    comment_post_ID, comment_author, comment_date_gmt, comment_approved,
    comment_type,comment_author_url,
    SUBSTRING(comment_content,1,30) AS com_excerpt
    FROM $wpdb->comments
    LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID =
    $wpdb->posts.ID)
    WHERE comment_approved = '1' AND comment_type = '' AND
    post_password = ''
    ORDER BY comment_date_gmt DESC
    LIMIT 10";
    $comments = $wpdb->get_results($sql);

    $output = $pre_HTML;
    $output .= "\n<ul>";
    foreach ($comments as $comment) {

    $output .= "\n<li>".strip_tags($comment->comment_author)
    .":" . "<a href=\"" . get_permalink($comment->ID) .
    "#comment-" . $comment->comment_ID . "\" title=\"on " .
    $comment->post_title . "\">" . strip_tags($comment->com_excerpt)
    ."</a></li>";

    }
    $output .= "\n</ul>";
    $output .= $post_HTML;

    echo $output;?>

	
			</li>
    <li><h2>Recent Post</h2>
				<ul>

<?php
$myposts = get_posts('numberposts=10&offset=1');
foreach($myposts as $post) :
?>
<li><a href="<?php the_permalink(); ?>"><?php the_title();
?></a></li>
<?php endforeach; ?>
	
		</ul>
		</li>
   <?php wp_list_pages('title_li=<h2>Pages</h2>' ); ?>

			<li><h2>Archives</h2>
				<ul>
				<?php wp_get_archives('type=monthly'); ?>
				</ul>
			</li>

			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>

			<?php /* If this is the frontpage */ if ( is_home() || is_page() ) { ?>
				<?php wp_list_bookmarks(); ?>

				<li><h2>Meta</h2>
				<ul>
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
					<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
					<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
					<?php wp_meta(); ?>
				</ul>
				</li>
			<?php } ?> 
		<?php endif; ?>
		</ul>
</div>