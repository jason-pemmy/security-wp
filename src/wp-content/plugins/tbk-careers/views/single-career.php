<?php get_header(); ?>
<?php if (have_posts()) { ?>
	<?php while (have_posts()) : the_post(); ?>
		<header class="single-learning-header">
			<h1><?php echo apply_filters('careers_post_title', $post->post_title);?></h1>
		</header>
		<div class="career-item-info">
			<div class="container container-narrow">
				<span class="career-location">
					<?php echo the_field('location', $post->ID); ?>
				</span>
			</div>
		</div>
		<div class="container container-narrow job-content">
			<?php echo apply_filters('careers_back_to', null);?>
		</div>
		<div class="container container-narrow job-content">
			<?php the_content();?>
			<div class="single-career-apply">
				<h3>Interested? We'd love to hear from you.</h3>
				<?php echo do_shortcode('[apply-online]');?>
			</div>
		</div>
		<div class="job-content">
			<div class="single-career-share">
				<div class="container container-narrow">
					<h3>Know someone who would be a perfect fit? Let them know.</h3>
					<?php echo do_shortcode('[career-share]');?>
				</div>
			</div>
		</div>
	<?php endwhile; ?>
<?php } ?>
<?php get_footer(); ?>
