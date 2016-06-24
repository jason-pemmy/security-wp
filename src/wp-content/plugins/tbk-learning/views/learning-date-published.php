<div class="date-time">
	<span class="date">
		<?php global $post;?>
		<?php echo apply_filters('learning_'.get_post_type(get_the_ID()).'_date', $post->post_date);?>
	</span>
</div>