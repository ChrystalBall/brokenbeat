	<div id="sidebar">
		<div class="sbcontent">
  		<ul>
  			<?php 	/* Widgetized sidebar, if you have the plugin installed. */
  					if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar() ) : ?>
  
  			<!-- Author information is disabled per default. Uncomment and fill in your details if you want to use it.
  			<li><h2>Author</h2>
  			<p>A little something about you, the author. Nothing lengthy, just an overview.</p>
  			</li>
  			-->

		    <?php if ( is_404() || is_category() || is_day() || is_month() ||
  						is_year() || is_search() || is_paged() ) {
  			?>
        <li>
  
  			<?php /* If this is a 404 page */ if (is_404()) { ?>
  			<?php /* If this is a category archive */ } elseif (is_category()) { ?>
  			<p>You are currently browsing the archives for the <?php single_cat_title(''); ?> category.</p>
  
  			<?php /* If this is a yearly archive */ } elseif (is_day()) { ?>
  			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
  			for the day <?php the_time('l, F jS, Y'); ?>.</p>
  
  			<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
  			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
  			for <?php the_time('F, Y'); ?>.</p>
  
  			<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
  			<p>You are currently browsing the <a href="<?php bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
  			for the year <?php the_time('Y'); ?>.</p>
  
  			<?php /* If this is a monthly archive */ } elseif (is_search()) { ?>
  			<p>You have searched the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives
  			for <strong>'<?php the_search_query(); ?>'</strong>. If you are unable to find anything in these search results, you can try one of these links.</p>
  
  			<?php /* If this is a monthly archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
  			<p>You are currently browsing the <a href="<?php echo bloginfo('url'); ?>/"><?php echo bloginfo('name'); ?></a> weblog archives.</p>
  
  			<?php } ?>
  				
  			</li> <?php }?>
       
  			<?php /*wp_list_pages('title_li=<h2>Pages</h2>' );*/ ?>
  			
  			<?php if(function_exists(get_recent_comments)) { ?>
  			<!-- This function is provided by the Chinese WordPress Kit, you can get it here. http://yanfeng.org/blog/wordpress/kit -->
  			<li><h2>Comments</h2>
  				<ul><?php get_recent_comments(); ?></ul>
  			</li>
  			<?php } ?>

  			<?php /* If this is the archives page */ if (!is_page('k2archives')) : ?>
  			<li><h2>Archives</h2>
  				<ul>
  				<?php wp_get_archives('type=monthly'); ?>
  				</ul>
  			</li>
  
  			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>
  			<?php endif; ?>
  
  			<?php /* If this is the frontpage */ if (is_home()) { ?>
  				<?php wp_list_bookmarks(); ?>
  
  				<li><h2>Meta</h2>
  				<ul>
  					<?php wp_register(); ?>
  					<li><?php wp_loginout(); ?></li>
  					<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Strict">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
  					<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
  					<?php wp_meta(); ?>
  				</ul>
  				</li>
  			<?php } ?>
  			
  			<?php endif; ?>

  		</ul>
		</div> <!-- .sbcontent -->
		<?php include (TEMPLATEPATH . '/cse.php'); ?> <!-- you may change this Google Custom Search to your own. -->
	</div> <!-- #sidebar -->
	<div class="clear"></div>
