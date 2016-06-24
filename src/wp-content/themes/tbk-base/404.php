<?php if ( ! defined('ABSPATH')) { die('No direct script access allowed'); }?>
<?php get_header(); ?>
	<div class="container">
		<h1 class="">
			<?php _e('Oh no! 404 Error. Page not found.', 'the-theme');?>
		</h1>
		<h2 class="">
			<?php _e('The page you are looking for might have been removed,
		had its name changed, or is temporarily unavailable.', 'the-theme');?>
		</h2>
		<p class="text-left">
			<?php _e('Please make sure that you\'ve typed the url correctly or
		return to the ', 'the-theme');?>
			<a href="<?php echo home_url() ;?>">
				<?php _e('homepage', 'the-theme');?>
			</a>.
		</p>
	</div>
<?php get_footer(); ?>