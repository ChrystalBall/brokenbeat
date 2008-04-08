</div>
	
	<div id="sidebar">
		<?php get_sidebar(); ?>
	</div>

	<div id="footer">
  <?php wp_footer(); ?>
  <!--
		<div id="footercontent">
		<div class="footbox">
				<h2>META</h2>
				<ul id="metafoot">
					<?php wp_register(); ?>
					<li><?php wp_loginout(); ?></li>
					<li><a href="http://validator.w3.org/check/referer" title="This page validates as XHTML 1.0 Transitional">Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr></a></li>
					<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>
					<li><a href="http://wordpress.org/" title="Powered by WordPress, state-of-the-art semantic personal publishing platform.">WordPress</a></li>
					<?php wp_meta(); ?>
				</ul>
			</div>
			
			<div class="footbox">
				<h2>RECENT POSTS</h2>
					<?php wp_get_archives('type=postbypost&limit=5'); ?>
					
				
				
			</div>
			
			<div class="footbox">
				<h2>CATEGORIES</h2>
				<ul id="metafoot2">
				<?php wp_list_categories('show_count=1&title_li='); ?>
			</ul></div>
			<div class="bringdown"></div>
		</div>
			-->
		<div id="footbar">
      <?php wp_loginout(); ?><br />
      &copy;<?php echo date('Y'); ?> BrokenBeat Network All Rights Reserved
    </div>
	</div>
	
 </div>
 </body>
 </html>
