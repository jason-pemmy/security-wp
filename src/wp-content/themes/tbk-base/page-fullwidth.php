<?php
/*
 * Template Name: Full Width
 */
?>
<?php get_header(); ?>
<?php echo do_shortcode('[hero-banner parallax="true"]');?>
<?php if ( function_exists('yoast_breadcrumb') ) { ?>
	<?php yoast_breadcrumb('<div class="breadcrumb"><div class="container breadcrumb-container">','</div></div>'); ?>
<?php } ?>
<?php if (have_posts()) { ?>
	<?php while (have_posts()) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
<?php } ?>
<?php get_footer(); ?>
