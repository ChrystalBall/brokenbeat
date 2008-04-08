<?php
require( dirname(__FILE__) . '/wp-config.php' );

$date = isset($_REQUEST['date']) ? $_REQUEST['date'] : getdate();
$content = isset($_REQUEST['content']) ? $_REQUEST['content'] : 'No information found';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title>Event information for <?php echo $date;?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<?php
	wp_admin_css( 'css/colors-fresh' );
	?>
</head>
<body class="eventPopup">

<div class="eventDetails">
<?php
echo $content;
?>
</div>

<p id="backtoblog"><a href="" onclick="javascript:window.close();"><?php printf(__('&laquo; Back to %s'), get_bloginfo('title', 'display' )); ?></a></p>

</body>
</html>
