<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

<title><?php wp_title(''); if (function_exists('is_tag') and is_tag()) { ?>Tag Archive for <?php echo get_query_var('tag'); } if (is_archive()) { ?> archive<?php } elseif (is_search()) { ?> Search for <?php echo get_query_var('s'); } if ( !(is_404()) and (is_search()) or (is_single()) or (is_page()) or (function_exists('is_tag') and is_tag()) or (is_archive()) ) { ?> at <?php } ?> <?php bloginfo('name'); ?></title>

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />
<link rel="shortcut icon" href="<?php bloginfo('home'); ?>/favicon.ico" type="image/x-icon" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<?php wp_head(); ?>

</head>

<body>

<div id="page">

  <div id="header">
		<ul id="nav">
			<li class="page_item"><a href="<?php echo get_settings('home'); ?>/" title="Home">Home</a></li>
			<?php wp_list_pages('sort_column=menu_order&depth=1&title_li='); ?>
		</ul>
		<div class="main_title"><a href="<?php echo get_option('home'); ?>/ class="main_title"><?php bloginfo('name'); ?></a></div>
		<div class="description"><?php bloginfo('description'); ?></div>

  </div> <!-- #header -->
  <hr />
