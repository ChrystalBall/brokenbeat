<div id="sidebar" class="rightcol">
<ul>

  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
	<li>
		<h3><span>Pages</span></h3>
		<ul>
			<?php wp_list_pages('title_li='); ?>
		</ul>
	</li>
    <li>
    	<h3><?php _e('Categories'); ?></h3>
    	<ul>
    	    <?php wp_list_cats('sort_column=name&optioncount=1&hierarchical=0'); ?>
    	</ul>
    </li>
	<li>
		<h3>Recent Enteries</h3>
		<ul>
			<?php query_posts('showposts=5'); ?>
			<?php while (have_posts()) : the_post(); ?>
				<li><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent link to'); ?> <?php the_title(); ?>"><?php the_title(); ?></a> - <?php the_time('m-d-Y') ?></li>
			<?php endwhile;?>
		</ul>
	</li>
    <li>
    	<h3>Latest Comments</h3>
		<ul>
			<?php include (TEMPLATEPATH . '/simple_recent_comments.php');?>
			<?php if (function_exists('src_simple_recent_comments')) { src_simple_recent_comments(5, 60, '', ''); } ?>
		</ul>
	</li>
	 <li>
	     <h3><?php _e('Archives'); ?></h3>
	     <ul>
	       <?php wp_get_archives('type=monthly'); ?>
	     </ul>
    </li>
  <?php endif; ?>
 </ul>
<div class="rightcol_bottom"></div>
</div>
</div>
