<div class="learning-filter-bar">
	<?php if( ! empty($post_types)) { ?>
		<ul class="list-learning-categories">
			<li class="filter-all <?php echo apply_filters('learning_active_link', TBK_Learning::$post_page);?>">
				<a href="<?php echo get_permalink(TBK_Learning::$post_page);?>" >
					<span class="filter-text">
						<?php _e('All', LEARN_DOMAIN);?>
					</span>
				</a>
			</li>
			<?php foreach($post_types as $pt) { ?>
				<li class="filter-<?php echo $pt.' '.apply_filters('learning_active_link', $pt);?>">
					<a href="<?php echo get_post_type_archive_link($pt);?>">
						<span>
							<?php echo plural(TBK_Learning::humanize($pt));?>
						</span>
					</a>
				</li>
			<?php } ?>
		</ul>
	<?php } ?>
	<form class="learning-filter-bar-search" method="post" action="<?php echo get_permalink(TBK_Learning::$post_page);?>">
		<input class="input-search" type="text" name="filter-keyword" value="<?php echo ( ! empty($_REQUEST['filter-keyword'])?$_REQUEST['filter-keyword']:'');?>"/>
		<button class="btn btn-search" type="submit" role="button">
			<i class="icon-search"></i>
			<span class="btn-text sr-only"><?php _e('Submit', LEARN_DOMAIN);?></span>
		</button>
	</form>
</div>
