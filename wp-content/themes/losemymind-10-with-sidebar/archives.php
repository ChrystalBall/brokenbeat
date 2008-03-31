<?php
/*
Template Name: Archives Page Template
*/
?>

<?php get_header(); ?>

<div id="content">

	<h2 class="pagetitle">Archives by Month:</h2>
	<ul class="archive-list">
		<?php wp_get_archives('show_post_count=1'); ?>
	</ul>
	<div class="clear"></div>

	<h2 class="pagetitle">Archives by Subject:</h2>
	<ul class="archive-list">
		 <?php wp_list_cats('hierarchical=0&optioncount=1'); ?>
	</ul>
	<div class="clear"></div>
	
	<h2 class="pagetitle">Tag Cloud:</h2> <!-- need UTW -->
	<?php if (function_exists(UTW_ShowWeightedTagSetAlphabetical)) : ?>
	<?php UTW_ShowWeightedTagSetAlphabetical("coloredsizedtagcloud","",0); ?>
	<?php endif; ?>

</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
