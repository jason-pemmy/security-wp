<div class="images-bar">
	<ul class="images-bar-list">
		<?php foreach($images as $i) { ?>
			<li class="image-item">
				<?php list($src) = wp_get_attachment_image_src(intval($i), 'full');?>
				<img src="<?php echo $src;?>" alt="<?php echo The_Theme::get_attachment_alt(intval($i));?>"/>
			</li>
		<?php } ?>
	</ul>
</div>
