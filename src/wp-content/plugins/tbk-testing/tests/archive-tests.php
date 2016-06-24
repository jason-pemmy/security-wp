<?php
new Archive_Tests();

class Archive_Tests {

	function __construct() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
	}

	public function admin_menu(){
		add_theme_page('Post Types/Archives', 'Post Types/Archives', 'manage_options', 'post-types-archives', array( &$this, 'archive_view' ) );
	}

	public function archive_view(){
		global $wpdb;
		$query = 'SELECT *, '.$wpdb->terms.'.term_id, '.$wpdb->terms.'.slug, '.$wpdb->terms.'.name FROM '.$wpdb->terms.'
		        left join '.$wpdb->term_taxonomy.' on '.$wpdb->terms.'.term_id = '.$wpdb->term_taxonomy.'.term_id
		        where '.$wpdb->term_taxonomy.'.taxonomy != "nav_menu"';
		$terms = $wpdb->get_results($query);
		?>
		<div class="wrap">
			<h2>Post Types/Archives</h2>
			<p class="description">
				This is used to view all registered taxonomies and post types.
			</p>
			<h3>Taxonomies</h3>
			<ul>
				<?php foreach($terms as $t) { ?>
					<li>
						<a href="<?php echo get_term_link($t);?>" target="_blank">
							<?php echo $t->name;?>
						</a>
					</li>
				<?php } ?>
			</ul>
			<h3>Post Type Archives/Index Pages</h3>
			<?php
			$post_types = get_post_types();?>
			<ul>
			<?php foreach($post_types as $type) { ?>
				<?php $typeobj = get_post_type_object($type);?>
				<?php if($typeobj->has_archive && $typeobj->public) { ?>
					<li>
						<?php
						if($typeobj->name == 'post') {
							$posts_page = get_option('page_for_posts');
							$url = get_permalink($posts_page);
						} else {
							$url = trailingslashit(site_url($typeobj->rewrite['slug']));
						}
						?>
						<a href="<?php echo $url; ?>" target="_blank">
							<?php echo $typeobj->labels->name;?>
						</a>
					</li>
				<?php } ?>
			<?php } ?>
			</ul>
		</div>
	<?php
	}
}
