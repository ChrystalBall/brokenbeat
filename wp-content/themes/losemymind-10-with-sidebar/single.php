<?php get_header(); ?>

	<div id="content">

	<?php if (function_exists(refer_thanks)) refer_thanks(); ?>

	<?php if (have_posts()) : the_post(); ?>

		<div class="post" id="post-<?php the_ID(); ?>">

			<div class="entry-title">
				<h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h2>
			</div> <!-- .entry-title -->
			<span class="entry-meta">Published by <a href="<?php the_author_url(); ?>"><?php the_author(); ?></a> on <?php the_time('M jS, 2007'); ?> in <?php the_category(','); ?> with<a href="<?php comments_link(); ?>"><?php comments_number(' No Comments', ' 1 Comment', ' % Comments'); ?></a><?php edit_post_link('*', ' ', ''); ?></span>

			<div class="entry">
				<?php the_content('Read the rest of this entry &raquo;'); ?>
			</div> <!-- .entry -->
			<p class="entry-tags">Tags: <?php if (function_exists(UTW_ShowTagsForCurrentPost)) UTW_ShowTagsForCurrentPost("commalist"); ?></p>
			<!-- need UTW -->
  		
  		<!-- need UTW -->
  		<?php if (function_exists(UTW_ShowRelatedPostsForCurrentPost)) { ?>
  		<div class="entry-related">
  			<h3>Related Posts</h3>
  			<ul><?php UTW_ShowRelatedPostsForCurrentPost("posthtmllist"); ?></ul>
  		</div> <!-- .entry-related -->
  		<?php } ?>
  		<div class="gg-ads-text">
  			<!-- you may put your own Adsense code here 234*60 -->
  		</div>
  	
  	</div> <!-- .post -->
		<div class="clear"></div>

		<div class="navigation">
			<div class="alignleft"><?php previous_post_link('&laquo; %link') ?></div>
			<div class="alignright"><?php next_post_link('%link &raquo;') ?></div>
		</div> <!-- .navigation -->
		<div class="clear"></div>

	<?php comments_template(); ?>
	<div class="clear"></div>

	<?php else: ?>

		<h2 class="center">Sorry, Not Found</h2>
		<p class="center">There isn't anything matched your criteria.</p>

	<?php endif; ?>

	</div> <!-- #content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
