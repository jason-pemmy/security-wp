<?php if ( ! defined('ABSPATH')) { die('No direct script access allowed'); }?>
<?php get_header(); ?>
	<?php if (have_posts()) { ?>
		<?php while (have_posts()) : the_post(); ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php the_content(); ?>
		<?php endwhile; ?>
	<?php } ?>
<?php get_footer(); ?>
