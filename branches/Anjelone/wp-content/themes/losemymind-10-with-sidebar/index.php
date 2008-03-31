<?php get_header(); ?>

	<div id="content">

	<?php if (function_exists(refer_thanks)) refer_thanks(); ?> <!-- you can get RThanks Plus on my site, http://hellobmw.com -->

	<?php if (have_posts()) { ?>

		<?php while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">

				<div class="entry-title">
  				<h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h2>
  			</div> <!-- .entry-title -->
  			<span class="entry-meta">Published by <a href="<?php the_author_url(); ?>"><?php the_author(); ?></a> on <?php the_time('M jS, 2007'); ?> in <?php the_category(','); ?> with<a href="<?php comments_link(); ?>"><?php comments_number(' No Comments', ' 1 Comment', ' % Comments'); ?></a><?php edit_post_link('*', ' ', ''); ?></span>

				<div class="entry">
					<?php if (is_single()) { ?>
						<?php the_content('Read the rest of this entry &raquo;'); ?>
					<?php } else { ?>
						<?php the_excerpt(); ?>
					<?php } ?>
				</div> <!-- .entry -->
				<p class="entry-tags">Tags: <?php if (function_exists(UTW_ShowTagsForCurrentPost)) UTW_ShowTagsForCurrentPost("commalist"); ?></p>
				<!-- need UTW -->

			</div> <!-- .post -->
			<div style="clear:both;"></div>

		<?php endwhile; ?>

		<div class="navigation"> <!-- need WP-PageNavi -->
			<?php if(function_exists('wp_pagenavi')) { wp_pagenavi(); } else { ?>
			<div class="alignleft"><?php next_posts_link('&laquo; Previous Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Next Entries &raquo;') ?></div>
			<?php } ?>
		</div> <!-- .navigation -->
		<div style="clear:both;"></div>

	<?php } else { ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

	<?php } ?>

	</div> <!-- #content -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
