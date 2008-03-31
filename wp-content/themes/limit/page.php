<?php get_header(); ?>
	<div class="entry">

	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">
				<h2><?php the_title(); ?></h2>
				<br />


					<?php the_content('Read more &raquo;'); ?>

<?php edit_post_link('Edit', '', ' | '); ?> 
			</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

	</div>
    <?php include (TEMPLATEPATH . '/lsidebar.php'); ?>
    <?php include (TEMPLATEPATH . '/rsidebar.php'); ?>
    <br clear="all" />
</div>
<?php get_footer(); ?>