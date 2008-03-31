<?php get_header(); ?>
<div id="main">
<div class="entry">
 
	<?php if (have_posts()) : ?>

		<?php while (have_posts()) : the_post(); ?>

			<div class="post" id="post-<?php the_ID(); ?>">
				<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h2>
				<small>Written by <?php the_author() ?> on <abbr title="<?php the_time('Y-m-d\TH:i:sO'); ?>"><?php unset($previousday); printf(__('%1$s &#8211; %2$s'), the_date('', '', '', false), get_the_time()) ?></abbr></small><br /><br />


					<div class="postbg"><?php the_content('Read more &raquo;'); ?></div>

<br />
<?php the_tags('Tags: ', ', ', '<br />'); ?>Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?><br />
<?php comments_template(); ?>
			</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

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
