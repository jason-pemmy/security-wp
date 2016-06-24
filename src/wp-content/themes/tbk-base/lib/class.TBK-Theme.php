<?php

class TBK_Theme {

	/**
	 * Static property to hold our singleton instance
	 */
	static $instance = false;

	/**
	 * Static property to hold registered image sizes
	 */
	static $image_sizes = array();

	/**
	 * This is our constructor, which is private to force the use of
	 * getInstance() to make this a Singleton
	 *
	 * @return void
	 */
	private function __construct() {
		$this->init();
	}

	/**
	 * Simply instantiates the singleton
	 *
	 * @return void
	 */
	public function instantiate() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return void;
	}

	/**
	 * If an instance exists, this returns it.	If not, it creates one and
	 * retuns it.
	 *
	 * @return TBK_Theme
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function init() {
		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'wp_print_scripts', array( & $this, 'enqueue_scripts' ), 5 ); // Earlier in the chain than the child theme
		add_filter( 'wp_print_styles', array( & $this, 'enqueue_styles' ), 5 );
		add_filter( 'page_template', array( & $this, 'page_template' ) ); // Will allow sub pages to use a parent's template
		add_filter( 'body_class', array( & $this, 'add_body_class' ) );
		add_theme_support( 'menus' );
		add_theme_support( 'post-thumbnails' );
		remove_filter( 'get_the_excerpt', 'wp_trim_excerpt' );
		add_filter( 'get_the_excerpt', array( & $this, 'trim_excerpt' ) );
		add_action( 'admin_bar_menu', array( &$this, 'view_site_blank_target' ), 999 );

		do_action_ref_array( 'TBK_Theme_init', array( & $this ) );
	}

	public static function component( $component, $view ) {
		$templates = array( 'components/$component/views/$view.php' );
		$file = locate_template( $templates );
		if ( $file == '' ) {
			$file = reset( $templates ); // The first element
		}
		include($file);
	}

	public static function is_callable( $what ) {
		return is_callable( $what );
	}

	public static function call( $what ) {

		if ( self::is_callable( $what ) ) {
			// We're going to call the function, which is expected to echo the results
			$args = func_get_args();
			array_shift( $args ); // first argument is the callback
			// It is expected that $callback will either echo or return its rendering
			echo call_user_func_array( $what, $args );
		} else {
			// Fail quasi-silently
			echo "<!-- TBK_Theme: Could not call $what -->";
		}
	}

	public static function module_exists( $module ) {
		global $tbk_toolbox;
		return $tbk_toolbox->module_enabled( $module );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	}

	public function enqueue_styles() {
		global $wp_styles;
		/*
		 * Loads our main stylesheet.
		 */
		//		wp_enqueue_style( 'tbk-theme-style', get_stylesheet_uri() );
		//
		//		wp_enqueue_style( 'bootstrap', get_bloginfo( 'template_url' ) . '/bootstrap/css/bootstrap.css' );
	}

	public function page_template( $template, $post = null ) {
		if ( ! isset( $post ) ) {
			global $post;
		}
		if ( $post->post_parent ) {
			// See if a template exists for the parent
			$parent = get_post( $post->post_parent );
			$test = locate_template( array( 'page-' . $parent->post_name . '.php' ) );
			if ( ! empty( $test ) ) {
				$template = $test;
			} elseif ( ! empty( $parent->post_parent ) ) {
				return $this->page_template( $template, $parent ); // go up the chain
			}
		}
		return $template;
	}

	//Puts a slug as a body class
	function add_body_class( $classes ) {
		global $post;

		if ( isset( $post ) ) {
			$classes[] = $post->post_type . '-' . $post->post_name;
		}
		return $classes;
	}

	public static function sibling_or_subpages_menu( $post = null, $current = null, $order = 'menu_order') {
		// outputs a nav menu for all sibling pages (if on a sub page) or subpages (if on a top level page)
		if ( ! isset( $post ) ) {
			global $post;
		} elseif ( !is_object( $post ) ) {
			// we can pass in a slug or an id
			if ( is_numeric( $post ) ) {
				$post = get_post( $post );
			} else {
				$post = get_page_by_path( $post );
			}
		}
		global $wpdb;
		$query = "SELECT * FROM $wpdb->posts WHERE `post_parent` = %s AND `post_status` = 'publish' AND `post_type` = %s ORDER BY `$order` ASC";
		if ( empty( $post->post_parent ) ) {
			// nav will be all children
			$query = $wpdb->prepare( $query, $post->ID, $post->post_type );
		} else {
			// nav will be all siblings
			$query = $wpdb->prepare( $query, $post->post_parent, $post->post_type );
		}

		$items = $wpdb->get_results( $query );
		$items = array_map( 'wp_setup_nav_menu_item', $items );
		_wp_menu_item_classes_by_context( $items );
		foreach ( $items as $item ) {
			$item->classes[] = 'menu-item-' . $item->post_name;
			if ( isset( $current ) and ($item->ID == $current or $item->post_name == $current) ) {
				$item->classes[] = 'current-menu-item';
			}
		}

		$walker = new Walker_Nav_Menu;
		$menu = $walker->walk( $items, 0 );
		if($menu != '') {
			echo '<ul class="menu subpage-menu">' . $menu . '</ul>' . "\n";
		}

	}

	public static function get_post_custom($post_id = 0) {
		$meta = get_post_custom($post_id);

		foreach ( $meta as $key => $value_array ) {
			if ( substr( $key, 0, 1 ) == '_' ) {
				unset( $meta[$key] ); // never interested in these.
			} else {
				$meta[$key] = maybe_unserialize( reset( $value_array ) );
			}
		}
		return $meta;
	}

	public static function query_posts( $args ) {
		// Note, this resets the main query.  Be sure to call TBK_Theme::have_posts() instead of just have_posts()
		// because it will reset the main query automatically when the loop is done
		query_posts( $args );
	}

	public static function have_posts() {
		$have_posts = have_posts();
		if ( ! $have_posts ) {
			// No more posts, reset the main loop query
			wp_reset_query();
		}
		return $have_posts;
	}

	// A replacement function to the wordpress add_image_size()
	// This registers image sizes with the theme but doesn't
	// call add_image_size().  The benefit is that specific
	// sized images can be requested ad hoc and there is no need
	// to pollute the uploads directory with unneeded resized images
	public static function add_image_size( $name, $width = 0, $height = 0, $crop = false ) {
		self::$image_sizes[$name] = array(
				'width' => $width,
				'height' => $height,
				'crop' => $crop,
		);

		static $filter_added;
		if ( ! isset( $filter_added ) ) {
			add_filter( 'wp_prepare_attachment_for_js', array( 'TBK_Theme', 'wp_prepare_attachment_for_js' ), 10, 3 );
			add_filter( 'image_size_names_choose', array( 'TBK_Theme', 'image_size_names_choose' ) );
			add_filter( 'media_send_to_editor', array( 'TBK_Theme', 'media_send_to_editor' ), 10, 3 );
			$filter_added = true;
		}
	}

	/**
	 * This function allows us to embed any of our ad-hoc image sizes into a post, regardless of
	 * whether the particular size has been created yet.
	 */
	public static function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		// only for image
		if ( $response['type'] != 'image' ) {
			return $response;
		}

		// make sure sizes exist. Perhaps they dont?
		if ( !isset( $meta['sizes'] ) ) {
			return $response;
		}

		$attachment_url = $response['url'];
		$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

		if ( isset( $meta['sizes'] ) && is_array( $meta['sizes'] ) ) {
			foreach ( self::$image_sizes as $k => $v ) {
				if ( ! isset( $response['sizes'][$k] ) ) {
					$response['sizes'][$k] = array(
							'height' => $v['height'],
							'width' => $v['width'],
							'url' => '',
							'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
					);
				}
			}
		}

		return $response;
	}

	public static function image_size_names_choose( $names ) {
		foreach ( array_keys( self::$image_sizes ) as $name ) {
			$names[$name] = ucwords( preg_replace( '/[^A-Za-z0-9]/', ' ', $name ) );
		}

		return $names;
	}

	/**
	 * If they've chosen one of our ad-hoc image sizes and the image hasn't been
	 * created yet, we'll create it here and then adjust the HTML that gets embedded
	 * within the post
	 */
	public static function media_send_to_editor( $html, $id, $attachment ) {
		$size = $attachment['image-size'];
		if ( isset( $size ) and in_array( $size, array_keys( self::$image_sizes ) ) ) {
			self::maybe_manufacture_image( $attachment['id'], $size );
			$existing = get_post_meta( $attachment['id'], '_wp_attachment_metadata', true );
			if ( isset( $existing['sizes'][$size] ) ) {
				$url = WP_CONTENT_URL . '/uploads/' . dirname( $existing['file'] ) . '/' . $existing['sizes'][$size]['file'];

				$pattern = get_shortcode_regex();
				if ( preg_match( "/$pattern/s", $html, $shortcode ) ) {
					// The html might contain a shortcode. if it does, we'll match it and then massage later
					$has_shortcode = true;
					$xml = new SimpleXMLElement( '<div>' . $shortcode[5] . '</div>' ); // wrapped in <div> to allow for trailing content/text
					$xpath = '/div/a/img';
				} else {
					$xml = new SimpleXMLElement( $html );
					$xpath = 'img';
				}
				$img = reset( $xml->xpath( $xpath ) );
				if ( $img['src'] != $url ) {
					$img['src'] = $url;
					$img['width'] = $existing['sizes'][$size]['width'];
					$img['height'] = $existing['sizes'][$size]['height'];
					$html = trim( str_replace( '<?xml version="1.0"?>', '', $xml->asXML() ) );

					if ( $has_shortcode ) {
						// Basically, this is replacing the <img> html from the origin HTML with the new HTML with the new image (if that makes anysense)
						/*
						  $shortcode == array(
						  0 - the original full text
						  1 - An extra ] to allow for escaping shortcodes with double [[]]
						  2 - The shortcode name
						  3 - The shortcode argument list
						  4 - The self closing /
						  5 - The content of a shortcode when it wraps some content.
						  6 - An extra ] to allow for escaping shortcodes with double [[]]
						  )
						 */
						$shortcode[3] = preg_replace( '/width="[^"]+"/', 'width="' . $img['width'] . '"', $shortcode[3] );
						$s = & $shortcode; // just for shorthand
						$html = preg_replace( '/^<div>(.*)<\/div>$/', '$1', $html );
						$html = "[{$s[2]} {$s[3]}]" . trim( $html ) . "[/{$s[2]}]";
					}
				}
			}
		}
		return $html;
	}

	// This is a wrapper function to the_post_thumbnail
	// The main purpose that it serves is if there isn't
	// a $size image already created, it uses the WordPress 3.5+
	// Image API to create one on the fly.	It does this without
	// required add_image_size to be called, which unnecessarily
	// adds unneeded image sizes for every image in the media
	// library
	public static function the_post_thumbnail( $size = 'thumb', $attributes = null ) {
		echo self::get_the_post_thumbnail( null, $size, $attributes );
	}

	public static function get_the_post_thumbnail( $post_id = null, $size = 'thumb', $attributes = null ) {
		if ( ! isset( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$id = get_post_thumbnail_id( $post_id );

		if ( ! $id ) {
			return false;
		}

		self::maybe_manufacture_image( $id, $size );
		return get_the_post_thumbnail( $post_id, $size, $attributes );
	}

	public static function get_attachment_image_src( $attachment_id = null, $size = 'thumb' ) {
		self::maybe_manufacture_image( $attachment_id, $size );
		return wp_get_attachment_image_src( $attachment_id, $size );
	}

	public static function get_attachment_image_url( $attachment_id = null, $size = 'thumb' ) {
		$src = self::get_attachment_image_src( $attachment_id, $size );
		return $src[0];
	}

	public static function maybe_manufacture_image( & $id, $size ) {

		if ( !isset( $id ) ) {
			// Note that $id is passed by reference so it may be set here.
			$id = get_post_thumbnail_id( get_the_ID() );
		}

		$meta = wp_get_attachment_metadata( $id );

		if ( empty( $meta ) ) {
			// the $id doesn't exist in this database, we can't continue
			return false;
		}

		if ( isset( $meta['sizes'][$size] ) ) {
			// It already exists, no need to do anything
			return;
		}

		if ( !isset( self::$image_sizes[$size] ) ) {
			wp_die( sprintf( __( 'You must first call TBK_Theme::add_image_size("%s",$your_width,$your_height,$your_crop) to register an ad-hoc image size', 'tbk-theme' ), $size ) );
		}

		// If we get here, we need to ad hoc create an image size
		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['basedir'] . '/' . $meta['file'];
		$size_meta = self::$image_sizes[$size];

		if ( !file_exists( $filename ) ) {
			// Oops, the file doesn't exist.  Can't call wp_get_image_editor
			return false;
		}

		// WP3.5 image manipulation magic
		$image = wp_get_image_editor( $filename ); // Return an implementation that extends <tt>WP_Image_Editor</tt>

		if ( is_wp_error( $image ) ) {
			wp_die( $image );
		}

		$image->resize( $size_meta['width'], $size_meta['height'], $size_meta['crop'] );
		$actual_size = $image->get_size();
		$target = $image->generate_filename( $actual_size['width'] . 'x' . $actual_size['height'] );
		$saved = $image->save( $target );
		if ( is_wp_error( $saved ) ) {
			wp_die( $saved );
		}

		unset( $saved['path'] ); // do this so that image_get_intermediate_size will autogenerate a 'url'
		$meta['sizes'][$size] = $saved;
		update_post_meta( $id, '_wp_attachment_metadata', $meta );

		// We're also going to save in post meta _wp_attachment_backup_sizes so that
		// any ad hoc images that we generate will get deleted if the attachment gets deleted
		$backup_sizes = get_post_meta( $id, '_wp_attachment_backup_sizes', true );
		if ( !is_array( $backup_sizes ) ) {
			$backup_sizes = array();
		}
		$backup_sizes["$size"] = $saved;
		update_post_meta( $id, '_wp_attachment_backup_sizes', $backup_sizes );
	}

	/*	 * ****************************************************************************
	 * @Author: Boutros AbiChedid
	 * @Date:	June 20, 2011
	 * @Websites: http://bacsoftwareconsulting.com/ ; http://blueoliveonline.com/
	 * @Description: Preserves HTML formating to the automatically generated Excerpt.
	 * Also Code modifies the default excerpt_length and excerpt_more filters.
	 * @Tested: Up to WordPress version 3.1.3
	 * ***************************************************************************** */

	public static function trim_excerpt( $text = '', $excerpt_length = null, $excerpt_more = null, $highlight = null ) {
		$raw_excerpt = $text;
		if ( '' == $text ) {
			//Retrieve the post content.
			$text = get_the_content( '' );
			//Delete all shortcode tags from the content.
			$text = strip_shortcodes( $text );

			$text = apply_filters( 'the_content', $text );
			$text = str_replace( ']]>', ']]&gt;', $text );
		}


		$allowed_tags = '<br>,<a>,<p>,<ul>,<li>'; /*		 * * MODIFY THIS. Add the allowed HTML tags separated by a comma.** */
		$text = strip_tags( $text, $allowed_tags );

		if ( !isset( $excerpt_length ) ) {
			$excerpt_length = apply_filters( 'excerpt_length', 40 ); // 40 in this case is the number of words
		}

		if ( !isset( $excerpt_more ) ) {
			$excerpt_more = apply_filters( 'excerpt_more', '&hellip; [...]' );
		}

		if ( !empty( $highlight ) ) {
			$words = preg_split( "/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY );
			preg_match_all( '/".*?("|$)|((?<=[\r\n\t ",+])|^)[^\r\n\t ",+]+/', str_replace( '"', '', $highlight ), $matches );
			$highlight_terms = array_map( '_search_terms_tidy', $matches[0] );
			// find the indices of each word in the text
			$indices = array();
			foreach ( $highlight_terms as $term ) {
				$offset = 0;
				$dummy = 0;
				while ( $dummy < 100 and ($index = array_search( $term, array_slice( $words, $offset, null, true ) )) !== false ) {
					$words[$index] = "<strong>{$words[$index]}</strong>";
					$indices[] = $index;
					$offset = $index + 1;
					$dummy++;
				}
			}
		}
		if ( !empty( $indices ) ) {
			sort( $indices );
			$highlighted_words = array();
			$word_index = 0;
			foreach ( $indices as $index ) {
				if ( $index > $word_index ) {
					if ( !empty( $highlighted_words ) or $index > 3 ) {
						$highlighted_words[] = '&hellip;';
					}
					$highlighted_words = array_merge( $highlighted_words, array_slice( $words, max( 0, $index - 3 ), 8 ) );
					$word_index = $index + 5;
				}
			}
			$highlighted_words = array_slice( array_merge( $highlighted_words, array_slice( $words, $word_index ) ), 0, $excerpt_length );
			// It's possible a "word" might be '\>' (the end of a tag).	 Don't want those.	Filter them out
			$highlighted_words = array_filter( $highlighted_words, create_function( '$a', 'return preg_match("/[a-zA-Z0-9]/",$a);' ) );
			if ( count( $highlighted_words ) > $excerpt_length - 1 ) {
				$highlighted_words[] = '&hellip;';
			}
			$text = implode( ' ', $highlighted_words );
			$text = $text . ' ' . $excerpt_more;
		} else {
			$words = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );
			if ( count( $words ) > $excerpt_length ) {
				array_pop( $words );
				$text = implode( ' ', $words );
				$text = $text . ' ' . $excerpt_more;
			} else {
				$text = apply_filters( 'excerpt_equals_content', implode( ' ', $words ) );
			}
		}
		return force_balance_tags( apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt ) );
	}

	/**
	 * Return the count of comments based on the post id and the status of the comment.
	 *
	 * @param integer $post_id The post ID
	 * @param string $status The status: moderated, approved, spam, trash
	 */
	public static function get_comment_count( $post_id, $status = 'approved' ) {
		return wp_count_comments( $post_id )->$status;
	}

	/**
	 * Return the markup for total number of post comments. This will likely evolve as needed to allow configurable
	 * text values for 0,1 and many comments
	 *
	 * @param integer $post_id The post id
	 * @return String Returns the markup, grammar ready, for the number of posts.
	 */
	public static function tbk_comments_number( $post_id ) {
		if ( !isset( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$num_comments = get_comments_number( $post_id ); // get_comments_number returns only a numeric value

		if ( comments_open( $post_id ) ) {
			if ( $num_comments == 0 ) {
				$comments = __( 'No Comments' );
			} elseif ( $num_comments > 1 ) {
				$comments = $num_comments . __( ' Comments' );
			} else {
				$comments = __( '1 Comment' );
			}
			$write_comments = '<a href="' . get_comments_link( $post_id ) . '">' . $comments . '</a>';
		} else {
			$write_comments = __( 'Comments are off for this post.' );
		}

		return $write_comments;
	}

	/**
	 * A helper method to return the array for the post's featured image
	 *
	 * @param integer $post_id The post id
	 * @return Array An Array containing the url, width, height of the image requested
	 */
	public static function get_featured_image_url( $post_id = false, $size = 'thumb' ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}

		$image = self::get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
		return $image;
	}

	/**
	 * Returns an array of social sharing urls to be used on single post pages
	 *
	 * @param int $post_id
	 * @return Array
	 */
	public static function get_post_sharing_urls( $post_id = false ) {
		if ( !$post_id ) {
			global $post;
			$post_id = $post->ID;
		}

		$summary = urlencode( get_the_excerpt() );

		if ( $summary == null || $summary == '' ) {
			//if summary is null attempt to get post data
			$postdata = get_post( $post_id );
			$summary = urlencode( TBK_Theme::trim_excerpt( '', 40, '' ) );
		}

		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );

		if ( $thumb !== false ) {
			$thumb_src = urlencode( $thumb['0'] );
		} else {
			$thumb_src = '';
		}

		$post_sharing_data = array(
				'title' => get_the_title( $post_id ),
				'message' => get_the_excerpt( $post_id ),
				'url' => get_permalink( $post_id ),
				'email_subject' => get_the_title( $post_id ),
				'email_title' => get_the_title( $post_id )
		);

		$post_sharing_data = apply_filters( 'post_sharing_data', $post_sharing_data );

		$post_sharing_data['message'] = str_replace( '&#038;', 'and', $post_sharing_data['message'] );
		$post_sharing_data['title'] = str_replace( '&#038;', 'and', $post_sharing_data['title'] );


		$title = urlencode( $post_sharing_data['title'] );
		$url = urlencode( $post_sharing_data['url'] );
		$post_sharing_data['message'] = str_replace( '[permalink]', $url, $post_sharing_data['message'] );
		$post_sharing_data['message'] = str_replace( '[title]', $post_sharing_data['title'], $post_sharing_data['message'] );
		$post_sharing_data['message'] = str_replace( '[message]', get_the_excerpt( $post_id ), $post_sharing_data['message'] );


		$msg = urlencode( strip_tags( $post_sharing_data['message'] ) );
		$email_body = str_replace( '<br/>', "%0D%0A %0D%0A", $post_sharing_data['message'] );
		$email_subject = rawurlencode( urldecode( $post_sharing_data['email_subject'] ) );



		$fb_share = 'http://www.facebook.com/sharer.php?s=100&amp;p[title]=' . $title;
		$fb_share .= '&amp;p[summary]=' . $msg;
		$fb_share .= '&amp;p[url]=' . $url;
		$fb_share .= '&amp;p[images][0]=' . $thumb_src;

		$fb = "window.open('" . $fb_share . "' , 'sharer' , 'toolbar=0,status=0,width=570,height=525' );return false; ";

		$twitter = 'http://twitter.com/share?text=' . $title;
		$twitter .= '&amp;url=' . $url;

		$email = 'mailto:?subject=' . $email_subject;
		$email .= '&amp;body=' . strip_tags( $email_body ) . '%0D%0A %0D%0A';

		$google = 'https://plus.google.com/share?url=' . $url;

		$linkedin = 'http://www.linkedin.com/shareArticle?mini=true&url=' . $url;
		$linkedin .= '&title=' . $title;
		$linkedin .= '&summary=' . $msg;
		$linkedin .= '&source=' . urlencode( get_bloginfo( 'url' ) );


		$urls = array(
				'facebook' => $fb,
				'twitter' => $twitter,
				'email' => $email,
				'google' => $google,
				'linkedin' => $linkedin
		);

		return $urls;
	}

	public static function iterate_mvc( $dir = 'controllers', $ext = '.php' ) {
		if ( !is_array( $dir ) ) {
			$dir = array( $dir );
		}
		foreach ( $dir as $d ) {
			$assets = glob( THEME_PATH . '/' . $d . '/*' . $ext );
			foreach ( $assets as $asset ) {
				//Remove extension
				$asset_name = basename( substr( $asset, 0, strrpos( $asset, '.' ) ) );
				get_template_part( $d . '/' . $asset_name );
			}
		}
	}

	public static function view_site_blank_target( $wp_admin_bar ) {
		$all_toolbar_nodes = $wp_admin_bar->get_nodes();
		foreach ( $all_toolbar_nodes as $node ) {
			if($node->id == 'site-name' || $node->id == 'view-site') {
				$args = $node;
				$args->meta = array( 'target' => '_blank', );
				$wp_admin_bar->add_node( $args );
			}
		}
	}

	public static function responsive_img( $img_id = null, $size, $attr = null, $bg = false ) {
		if( ! isset($img_id) || $img_id == '' || $img_id === false) {
			return;
		}
		extract( shortcode_atts( array(
				'alt' => get_the_title(),
				'class' => 'img-responsive',
		), $attr ) );
		$full = wp_get_attachment_image_src( $img_id, 'full' );
		list($src, $width, $height) = $full;

		$src = wp_get_attachment_image_src( $img_id, $size );
		$src = $src[0];

		$sizes = array();

		if ( $width > 380 ) {
			$image = wp_get_attachment_image_src( $img_id, 'mobile-sm' );
			$sizes[] = '<380:' . $image[0];
		}

		if ( $width > 500 ) {
			$image = wp_get_attachment_image_src( $img_id, 'mobile-lg' );
			$sizes[] = '<500:' . $image[0];
		}
		if ( $width > 768 ) {
			$image = wp_get_attachment_image_src( $img_id, 'tablet' );
			$sizes[] = '<768:' . $image[0];
		}
		if ( $width > 992 ) {
			$image = wp_get_attachment_image_src( $img_id, 'desktop-sm' );
			$sizes[] = '<992:' . $image[0];
		}
		if ( $width > 1200 ) {
			$image = wp_get_attachment_image_src( $img_id, 'desktop-lg' );
			$sizes[] = '<1200:' . $image[0];
		}
		$sizes[] = '>1200:' . $src;
		if ( count( $sizes ) > 1 ) {
			$class .= ' has-resp-img';
		} else {
			$class .= ' no-resp-img';
		}

		if ( count( $sizes ) > 1 ) {
			$class .= ' has-resp-img';
		} else {
			$class .= ' no-resp-img';
		}
		if ( $bg == true ) {
			return ' style="background-image: url(' . $src . ')" data-src="' . implode( ',', $sizes ) . '"';
		}
		return '<img src="' . $src . '" data-src="' . implode( ',', $sizes ) . '" alt="' . $alt . '" class="' . $class . '"/>';
	}

	public static function responsive_bg( $img_id = null, $size = null ) {
		return self::responsive_img( $img_id, $size, null, true );
	}

	public static function humanize($str) {
		$str = ucwords(preg_replace('/[-]+/', ' ', strtolower(trim($str))));
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

	public static function get_post_thumbnail_id($post_id = null) {
		if(empty($post_id)) {
			$post_id = get_the_ID();
		}
		$thumb_id = get_post_thumbnail_id($post_id);
		if( empty($thumb_id)) {
			//let's look for a default
			$thumb_id = get_field('default_image', 'options');
		}
		return $thumb_id;
	}

	public static function get_attachment_alt($att_id) {
		$att = get_post($att_id);
		if( ! empty($att)) {
			return $att->post_content;
		}
		return false;
	}

	public static function create_image_sizes($name, $w, $h, $crop, $add_dynamic = true){
		add_image_size( $name, $w, $h, $crop );
		if($add_dynamic === true) {
			self::add_image_size( $name, $w, $h, $crop );
		}
	}

	public static function get_attachment_src($attach_id, $size = 'thumbnail') {
		$image = wp_get_attachment_image_src($attach_id, $size);
		if( ! empty($image)) {
			return $image[key($image)];
		}
		return false;
	}

	public static function get_current_post_type() {
		global $post, $typenow, $current_screen;

		//we have a post so we can just get the post type from that
		if ( $post && $post->post_type ) {
			return $post->post_type;
		}

		//check the global $typenow - set in admin.php
		elseif( $typenow ) {
			return $typenow;
		}

		//check the global $current_screen object - set in sceen.php
		elseif( $current_screen && $current_screen->post_type ) {
			return $current_screen->post_type;
		}

		//lastly check the post_type querystring
		elseif( isset( $_REQUEST['post_type'] ) ) {
			return sanitize_key( $_REQUEST['post_type'] );
		}

		//we do not know the post type!
		return null;
	}

}

if ( ! function_exists('humanize')) {
	function humanize($str) {
		return TBK_Theme::humanize($str);
	}
}

//todo why is this not in its own class file?
abstract class Base_Factory {

	private static $_instances = array();
	private static $post_type = null;

	public function __construct($post_type = null) {
		if( ! empty($post_type)) {
			self::$post_type = $post_type;
		}
	}

	public static function instantiate() {
		self::get_instance();
		return null;
	}

	public static function get_instance() {
		$class = get_called_class();
		if ( ! isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class();
		}
		return self::$_instances[$class];
	}

	public static function default_query_args(){
		return array(
			'post_type' => self::$post_type,
			'post_status' => 'publish',
			'orderby' => 'menu_order',
			'order' => 'asc',
		);
	}
}
