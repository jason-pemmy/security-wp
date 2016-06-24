<div class="modal fade" id="modal-search" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<div class="container">
					<button type="button" class="btn btn-close" data-dismiss="modal">
						<i class="icon-x"></i>
						<span class="sr-only"><?php _e('Close', 'the-theme'); ?></span>
					</button>
					<form role="search" method="get" class="form-search form-inline" action="<?php echo trailingslashit(home_url()); ?>">
						<label for="search-field" class="sr-only"><?php _e('Search:', 'the-theme'); ?></label><br/>
						<input type="search" id="search-field" value="<?php echo get_search_query(); ?>" name="s"
						       class="search-field form-control" placeholder="<?php _e('What are you looking for?', 'the-theme'); ?>">
						<button type="submit" class="search-submit btn btn-search sr-only"><?php _e('Search', 'the-theme'); ?></button>
					</form>
				</div>
			</div>
			<div class="modal-body">
				<div class="container">
					<div class="loader-container">
						<div class="loader"></div>
					</div>
					<ul class="search-results"></ul>
				</div>
			</div>
			<div class="modal-footer">
				<div class="container">
					<button type="button" class="btn btn-close" data-dismiss="modal">
						<i class="icon-x"></i>
						<span class="sr-only"><?php _e('Close', 'the-theme'); ?></span>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>