<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php bloginfo('name'); ?> <?php if ( is_single() ) { ?> &raquo; Blog Archive <?php } ?> <?php wp_title(); ?></title>
<meta name="template" content="YGoent Version 1.0" />
<meta name="author" content="Amitabh Shukla - www.ygosearch.com" />
<meta name="sponsor" content="Health Portal - www.ygoy.com" />
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>
<body>
<div id="container">
	<div id="header">
		<h1><a href="<?php echo get_settings('home'); ?>/"><?php bloginfo('name'); ?></a></h1>
		<p><?php bloginfo('description'); ?></p>
	</div>
	<div id="navigation1">
		<ul class="basictab">
			<li><a href="<?php echo get_settings('home'); ?>" title="Home of <?php bloginfo('name'); ?>">Home</a></li>
			<?php wp_list_pages('title_li=&depth=1'); ?>
			<li class="right_item"><a href="<?php bloginfo('rss2_url'); ?>">RSS 2.0 (Posts)</a></li>
			<li class="fav_item"><a href="http://technorati.com/faves?add=<?php echo get_settings('home'); ?>">Favorites</a></li>
		</ul>
	</div>

