<?php get_header(); ?>
<?php if (have_posts()) { ?>
	<?php while (have_posts()) : the_post(); ?>
		<div class="container container-narrow">
			<?php echo apply_filters('learning_back_to', null);?>
		</div>
		<header class="single-learning-header">
			<?php echo apply_filters('learning_post_title', $post->post_title);?>
		</header>
		<div class="container container-narrow">
			<?php the_content();?>
		</div>
	<?php endwhile; ?>
<?php } ?>
<?php get_footer(); ?>