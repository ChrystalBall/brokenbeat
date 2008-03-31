	<ul>
	<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar(3) ) : else : ?>
           
            <?php wp_list_pages('title_li=<h2>Pages</h2>' ); ?>
         <li>
        <h2><?php _e('Meta'); ?></h2>
            <ul>
            <?php wp_register(); ?>
            <li><?php wp_loginout(); ?></li>
            <?php wp_meta(); ?>
            </ul>
        </li>
	<?php endif; ?>
	</ul>