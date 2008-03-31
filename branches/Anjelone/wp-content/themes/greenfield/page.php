<?php get_header(); ?>
 <div id="wrapper">
	<div id="content" class="leftcol">
  		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        	<h3><?php the_title(); ?></h3>
			<?php the_content('<p>Read the rest of this page &raquo;</p>'); ?>
			<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
		<?php endwhile; endif; ?>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>