<?php

/*
  Description: Registers custom taxonomies given an array of names. Handles all labeling and rewrites by default.
  Author: Paul MacLean
  Usage:
  //If in a theme you need to access the global $tbk_toolbox which stores all of the tbkcp internal class references
  global $tbk_toolbox;
  if ($tbk_toolbox->module_enabled('tbkcp_register_taxonomy')) {
  $tbkrt = & $tbk_toolbox->get_module('tbkcp_register_taxonomy');
  $custom_taxonomies = array(
  'Genre' => array('post_types' => array(book) ), //You can send override args to the wordpress function register_taxonomy_type here if needed
  );
  //Send an array of titles and any args you wish to overwrite. The slug generation uses santize_title()
  $tbkrt->add_custom_taxonomies($custom_taxonomies);
  }
 */
define( 'TBKCP_REGISTER_TAX_PRE', 'tbkcp_register_taxonomy_' );

class tbkcp_register_taxonomy
{

	protected $custom_taxonomies = array( );
	protected $custom_taxonomy_slugs = array( );

	/* Function: add_custom_taxonomies
	  adds the actions to register the taxonomies
	 */

	function add_custom_taxonomies( $custom_taxonomies )
	{
		$this->custom_taxonomies = $custom_taxonomies;
		add_action( 'init', array( $this, 'add_args' ) );
		add_action( 'register_taxonomy_custom_init_hook', array( $this, 'register_taxonomy_custom_init' ) );
	}

	/* Function: add_custom_taxonomies
	  calls the do_action for register_taxonomy_custom_init_hook
	 */

	function add_args()
	{
		$args = $this->custom_taxonomies;
		do_action( 'register_taxonomy_custom_init_hook', $args );
	}

	/* Function: register_taxonomy_custom_init
	  Sets the registered taxonomies labels and args.
	 */

	function register_taxonomy_custom_init( $custom_taxonomies )
	{


		foreach ( $custom_taxonomies as $custom_taxonomy_name => $custom_taxonomy_args ) {
			$custom_taxonomy_slug = sanitize_title( $custom_taxonomy_name );
			$last_char = $custom_taxonomy_name[strlen( $custom_taxonomy_name ) - 1];
			if ( $last_char == 'y' ) {
				$plural = 'ies';
				$custom_taxonomy_name_plural = substr_replace( $custom_taxonomy_name, "", -1 ) . 'ies';
				$custom_taxonomy_slug_plural = substr_replace( $custom_taxonomy_slug, "", -1 ) . 'ies';
			} else {
				$custom_taxonomy_name_plural = $custom_taxonomy_name . 's';
				$custom_taxonomy_slug_plural = $custom_taxonomy_slug . 's';
			}
			$default_labels = array(
				'name' => _x( $custom_taxonomy_name_plural, 'taxonomy type general name' ),
				'singular_name' => _x( $custom_taxonomy_name, 'taxonomy type singular name' ),
				'add_new' => _x( 'Add New', $custom_taxonomy_name ),
				'add_new_item' => __( 'Add New ' . $custom_taxonomy_name ),
				'edit_item' => __( 'Edit ' . $custom_taxonomy_name ),
				'new_item' => __( 'New ' . $custom_taxonomy_name ),
				'update_item' => __( 'Update' . $custom_taxonomy_name ),
				'all_items' => __( 'All ' . $custom_taxonomy_name_plural ),
				'parent_item' => __( 'Parent' . $custom_taxonomy_name ),
				'view_item' => __( 'View ' . $custom_taxonomy_name ),
				'search_items' => __( 'Search ' . $custom_taxonomy_name_plural ),
				'not_found' => __( 'No ' . $custom_taxonomy_name_plural . ' found' ),
				'not_found_in_trash' => __( 'No ' . $custom_taxonomy_name_plural . ' found in Trash' ),
				'parent_item_colon' => '',
				'menu_name' => __( $custom_taxonomy_name_plural )
			);

			$labels = wp_parse_args( $custom_taxonomy_args['labels'], $default_labels );

			$default_args = array(
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => $custom_taxonomy_slug_plural ),
				'hierarchical' => true,
			);

			$args = wp_parse_args( $custom_taxonomy_args, $default_args );

			//var_dump($args);

			array_push( $this->custom_taxonomy_slugs, $custom_taxonomy_slug );
			register_taxonomy( $custom_taxonomy_slug, $args['post_types'], $args );
		}
		update_option( TBKCP_REGISTER_TAX_PRE . 'slugs', $this->custom_taxonomy_slugs );
	}

	/* Function: register_taxonomy_custom_init
	  Shows the current custom taxonomy slugs
	 */

	function form_options()
	{
		$slugs = get_option( TBKCP_REGISTER_TAX_PRE . 'slugs' );
		if ( $slugs ) {
			echo '<strong>Current Custom Post Slugs: </strong>' . implode( ',', $slugs );
		}
	}

}

?>
