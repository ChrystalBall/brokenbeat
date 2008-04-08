<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>

<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" /> <!-- leave this for stats -->

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php wp_head(); ?>
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="<?php bloginfo('template_directory'); ?>/ie7.css" />
<![endif]-->
</head>
<body>
<div id="mainbox">
 
	<div id="header">
		<div id="logo"><h1><a href="<?php echo get_option('home'); ?>/"><?php bloginfo('name'); ?></a></h1></div>
		<div id="headerdec"></div>
		<div id="search">
		  <form action="<?php bloginfo('url'); ?>/" method="post">
				<div style="height: 20px;">
					<!--<input type="text" value="<?php the_search_query(); ?>" name="s"  />
					<input id="submit" type="image" src="<?php bloginfo('template_directory'); ?>/images/search.gif"  value="Search" />-->
					
		    </div>
			</form>
		</div><!---->
		<div id="iemenu">
			<ul id="menu">
				<!--<li><a href="<?php bloginfo('url'); ?>" title="Home">Home</a></li>-->
				<?php wp_list_pages('sort_column=menu_order&depth=1&title_li='); ?>
			</ul>
		</div>
	</div>
 
	<div>
