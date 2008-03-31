<?php get_header(); ?>
<div id="wrapper">
	<div id="content" class="leftcol">
		<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>
				<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h3>
				<div class="details"><span style="float:left;">Posted in <?php the_category(', ') ?> by <?php the_author_posts_link() ?> on <?php the_time('F, j') ?>  at <?php the_time('g:i a') ?></span><span style="float:right;margin:0px;padding:0px;"><?php comments_popup_link(__('Comments (0)'), __('Comments (1)'), __('Comments (%)')); ?></span> </div>
				<?php the_content('Read the rest of this entry &raquo;'); ?>
			<?php endwhile; ?>
			<div class="navigation">
				<span class="previous-entries"><?php next_posts_link('Previous Entries') ?></span> <span class="next-entries"><?php previous_posts_link('Next Entries') ?></span>
			</div>
			<?php else : ?>
			<h2 class="center">Not Found</h2>
			<p class="center">Sorry, but you are looking for something that isn't here.</p>
		<?php endif; ?>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
