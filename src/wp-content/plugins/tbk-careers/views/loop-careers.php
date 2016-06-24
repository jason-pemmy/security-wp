<div class="career-items">
	<?php if( $careers->have_posts()) {?>
		<?php while ($careers->have_posts()) : $careers->the_post(); global $post;?>
			<div class="career-item career-item-<?php echo $post->post_name;?>">
				<?php $permalink = get_the_permalink();?>
				<a class="career-item-link" href="<?php echo $permalink;?>">
					<div class="career-item-image">
						<?php echo apply_filters('career_image', null);?>
					</div>
					<h3 class="career-item-heading">
						<?php the_title();?>
					</h3>
					<div class="career-item-info">
						<span class="career-location">
							<?php echo the_field('location', $post->ID);?>
						</span>
					</div>
				</a>
			</div>
		<?php endwhile; ?>
	<?php } else {?>
		<div class="no-results-found">
			<?php
			echo apply_filters('careers_not_found', wpautop('We\'re sorry! There are no career positions currently available. Check back with us soon.', CAREER_DOMAIN));
			?>
		</div>
	<?php } ?>
</div>
