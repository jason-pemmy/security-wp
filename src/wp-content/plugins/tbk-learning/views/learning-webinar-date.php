<div class="webinar-date-time">
	<span class="webinar-date">
		<?php echo apply_filters('learning_post_date', $date);?>
	</span>
	<span class="webinar-time">
		<?php echo $time; ?>
	</span>
</div>
<a class="btn btn-webinar-register" href="<?php echo $url;?>" target="_blank">
	<?php echo apply_filters('learning_webinar_btn', __('Register Today', LEARN_DOMAIN));?>
</a>