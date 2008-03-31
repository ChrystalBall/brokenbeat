<?php get_header(); ?>
<div id="main">
<div class="entry">
 
	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">
				<h2><?php the_title(); ?></h2>
				
					<div class="postbg"><?php the_content('Read more &raquo;'); ?></div>

<?php edit_post_link('Edit', '', ''); ?>
			</div>

		<?php endwhile; ?>

	<?php else : ?>
		<div class="post">
		<h2 class="center search">Search could not find anything!</h2>
		<p class="center">Sorry, but you are looking for something that isn't here.</p>
        </div>
	<?php endif; ?>

	</div>

<?php include (TEMPLATEPATH . '/sidebar.php'); ?>
<br clear="all" />
<?php get_footer(); ?>
</div>
</body>
</html>
