<div class="social-share">
	<?php if(class_exists('TBK_Theme')) { ?>
		<?php $sharing_urls = TBK_Theme::get_post_sharing_urls(); ?>
		<ul class="list-social-media">
			<li>
				<a href="<?php echo $sharing_urls['pinterest']; ?>" target="_blank">
					<i class="icon-pinterest"></i>
					<span class="sr-only"><?php _e('Pinterest', CAREER_DOMAIN);?></span>
				</a>
			</li>
			<li>
				<a href="#" onclick ="<?php echo $sharing_urls['facebook']; ?>" target="_blank">
					<i class="icon-facebook"></i>
					<span class="sr-only"><?php _e('Facebook', CAREER_DOMAIN);?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $sharing_urls['twitter']; ?>" target="_blank">
					<i class="icon-twitter"></i>
					<span class="sr-only"><?php _e('Twitter', CAREER_DOMAIN);?></span>
				</a>
			</li>
			<li>
				<a href="<?php echo $sharing_urls['google']; ?>" target="_blank">
					<i class="icon-google-plus"></i>
					<span class="sr-only"><?php _e('Google+', CAREER_DOMAIN);?></span>
				</a>
			</li>
		</ul>
	<?php } else {
		_e('You\'ll need to set up sharing urls with in your own theme.', CAREER_DOMAIN);
	} ?>
</div>