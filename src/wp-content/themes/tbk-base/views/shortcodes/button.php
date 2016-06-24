<a href="<?php echo $link['url'];?>" target="<?php echo $link['target'];?>" class="btn btn-<?php echo $color;?>">
	<?php if( ! empty($leading_title)) { ?>
		<span class="btn-text">
			<?php echo $leading_title;?>
		</span>
		<span class="btn-emphasis">
			<?php echo $title;?>
		</span>
	<?php } else {?>
		<span class="btn-text">
			<?php echo $title;?>
		</span>
	<?php } ?>
	<i class="icon-chevron-right"></i>
</a>