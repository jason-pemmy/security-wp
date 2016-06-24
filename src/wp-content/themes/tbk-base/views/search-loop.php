<?php
$has_thumb = has_post_thumbnail(get_the_ID());
$post_type = get_post_type();
?>
<li>
	<div class="search-result search-result-<?php echo $post_type;?>">
		<a href="<?php echo get_the_permalink(get_the_ID());?>">
			<div class="search-result-inner">
				<?php if($has_thumb) {
					$image_size = ($post_type == 'venture-cover'?'vc-cover':'post-search');
					the_post_thumbnail($image_size, array( 'class' => 'search-result-image' ));
				} ?>
				<h4 class="search-result-heading">
					<?php the_title(); ?>
				</h4>
				<?php if( $post_type != 'venture-cover') { ?>
					<p class="search-result-summary">
						<?php the_excerpt(); ?>
					</p>
				<?php } ?>
			</div>
		</a>
	</div>
</li>
