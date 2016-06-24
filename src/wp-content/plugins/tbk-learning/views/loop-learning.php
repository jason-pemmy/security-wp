<div class="learning-items">
	<?php if( $learning->have_posts()) {?>
		<?php while ($learning->have_posts()) : $learning->the_post(); global $post;?>
			<div class="learning-item learning-item-<?php echo $post->post_name;?>">
				<?php $permalink = get_the_permalink();?>
				<a class="learning-item-link" href="<?php echo $permalink;?>">
					<div class="learning-item-image">
						<?php echo apply_filters('learning_blog_image', null);?>
					</div>
					<h3 class="learning-item-heading">
						<?php the_title();?>
					</h3>
					<div class="learning-item-info">
						<span class="learning-item-category">
							<?php echo TBK_Learning::humanize($post->post_type);?>
						</span>
						<span class="learning-item-info-divider"> - </span>
						<span class="learning-item-date">
							<?php echo apply_filters('learning_'.$post->post_type.'_date', $post->post_date);?>
						</span>
					</div>
					<div class="learning-item-excerpt">
						<?php if(method_exists('TBK_THEME', 'trim_excerpt')) {
							$content = do_shortcode($post->post_content);
							if(empty($content)) {
								$content = $post->post_excerpt;
							}
							//if content is still empty, we need to fake it.
							if(empty($content)) {
								$content = '  ';
							}
							echo wpautop(TBK_Theme::trim_excerpt(strip_tags($content), 25, '...'));
						} else {
							echo wpautop(strip_tags($post->post_excerpt));
						}?>
					</div>
					<span class="btn btn-learning-item">
						<span class="btn-text">
							<?php echo apply_filters('learning_learn_more', __('Learn More', LEARN_DOMAIN));?>
						</span>
						<i class="icon-chevron-right"></i>
					</span>
				</a>
			</div>
		<?php endwhile; ?>
	<?php } else {?>
		<div class="no-results-found">
			<?php echo apply_filters('learning_not_found',
					wpautop('We\'re sorry, no results were found.', LEARN_DOMAIN));?>
		</div>
	<?php } ?>
</div>
