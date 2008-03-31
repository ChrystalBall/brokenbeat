<?php get_header(); ?>
 <div id="wrapper">
	<div id="content" class="leftcol">
  		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        	<h3><a href="<?php echo get_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>"><?php the_title(); ?></a></h3>
			<div class="details">Posted in <?php the_category(', ') ?> by <?php the_author_posts_link() ?> on <?php the_time('F, j') ?>  at <?php the_time('g:i a') ?></div>
			<?php the_excerpt(__('Readmore »'));?><a href="<?php the_permalink() ?>" style="margin:0px;padding:0px;font-size:11px;color:#000;">Read More..&gt;&gt;</a>
			<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
			<?php edit_post_link('Edit', '', ''); ?>
			<?php comments_template(); ?>
		<?php endwhile; else: ?>
		<p>Sorry, no posts matched your criteria.</p>
		<?php endif; ?>
  </div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>

