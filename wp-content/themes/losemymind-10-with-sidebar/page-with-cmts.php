<?php /*
Template Name: Page with Comments
*/ ?>

<?php get_header(); ?>

	<div id="content">
		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="post" id="post-<?php the_ID(); ?>">
    	<div class="entry-title">
    		<h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h2>
    	</div>
    	<span class="entry-meta">Published by <a href="<?php the_author_url(); ?>"><?php the_author(); ?></a> on <?php the_time('M jS, 2007'); ?> in <?php the_category(','); ?> with<a href="<?php comments_link(); ?>"><?php comments_number(' No Comments', ' 1 Comment', ' % Comments'); ?></a><?php edit_post_link('*', ' ', ''); ?></span>
    
    	<div class="entry">
    		<?php the_content('Read the rest of this entry &raquo;'); ?>
    	</div> <!-- .entry -->
    	<p class="entry-tags">Tags: <?php if (function_exists(UTW_ShowTagsForCurrentPost)) UTW_ShowTagsForCurrentPost("commalist"); ?></p>
    </div> <!-- .post -->
    <div style="clear:both;"></div>
		<?php comments_template(); ?>
		<?php endwhile; endif; ?>
	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
