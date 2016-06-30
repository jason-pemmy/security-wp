<?php

/**
 * @package better-lorem
 * @subpackage admin
 * @since 0.9
 */
class BetterLoremAdmin extends BetterLorem {

	/**
	 * Plugin config version
	 *
	 * @var string
	 * @since 0.9
	 */
	private $version = '0.9';

	/**
	 * A LorelIpsum object
	 *
	 * @since 0.9
	 * @var string
	 */
	public $Lorem;

	/**
	 * Constructor, set up the admin interface
	 *
	 * @since 0.9
	 */
	public function __construct() {
		BetterLorem::__construct();
		load_plugin_textdomain(
			'better-lorem',
			false,
			basename( $this->plugin_dir ) . '/translations'
		);
		add_action(
			'admin_menu',
			array( $this, 'add_page' )
		);
		add_action(
			'admin_init',
			array( $this, 'register_settings' )
		);
	}

	/**
	 * Return default plugin configuration
	 *
	 * @return array config
	 * @since 0.9
	 */
	private function defaults() {
		$config = array(
			'version'		=> $this->version,
		);
		return $config;
	}

	/**
	 * Reset the stored configuration, not the active one. This is only for
	 * internal use at the moment but should probably accessible through the
	 * admin interface.
	 *
	 * @return none
	 * @since 0.9
	 */
	public function activate() {
		update_option( 'better-lorem', $this->defaults() );
	}

	/**
	 * Set up the options page
	 *
	 * @return none
	 * @since 0.9
	 */
	public function add_page() {
		if ( current_user_can ( 'manage_options' ) ) {
			$options_page = add_management_page (
				__( 'Better Lorem Ipsum' , 'better-lorem' ),
				__( 'Better Lorem Ipsum' , 'better-lorem' ),
				'manage_options',
				'better-lorem',
				array ( $this , 'admin_page' )
			);
			add_action(
				'admin_print_styles-' . $options_page,
				array( $this, 'css' )
			);
		}
	}

	/**
	 * Load admin CSS style
	 *
	 * @since 0.9
	 */
	public function css() {
		wp_register_style(
			'better-lorem',
			plugins_url( basename( $this->plugin_dir ) . '/css/admin.css' ),
			null,
			'0.0.1'
		);
		wp_enqueue_style( 'better-lorem' );
	}

	/**
	 * Register the plugin option with the setting API
	 *
	 * @return none
	 * @since 0.9
	 */
	public function register_settings () {
		register_setting(
			'better-lorem_options',
			'better-lorem',
			array( $this, 'lorem' )
		);
	}

	/**
	 * Form input helper that produces the correct HTML markup
	 *
	 * @param string $label Input label
	 * @param string $name Input name
	 * @param string $comment Input comment
	 * @return none
	 * @since 0.9
	 */
	private function input( $label, $name, $comment = false ) { ?>
		<tr valign="top">
			<th scope="row"> <?php
				echo $label; ?>
			</th>
			<td> <?php
				echo '<input type="text" name="better-lorem[' . $name . ']" value="' . $this->get_option( $name ) . '"/>';
				if ( $comment )
					echo ' ' . $comment;
				?>
			</td>
		</tr> <?php
	}

	/**
	 * Form checkbox helper
	 *
	 * @param string $label Input label
	 * @param mixed $name String option name or array of name => [ subnames ]
	 * @param string $comment Input comment
	 * @return none
	 * @since 0.9
	 */
	private function checkbox( $label, $name, $comment = false ) { ?>
		<tr valign="top">
			<th scope="row"> <?php
				echo $label; ?>
			</th>
			<td> <?php
				if ( is_array( $name ) ) {
					foreach( $name[1] as $value ) {
						$this->single_checkbox( $name[0], $value );
					}
				}
				else {
					$checked = '';
					if ( $this->get_option( $name ) )
						$checked = ' checked="checked" ';
					echo '<input ' . $checked . 'name="better-lorem[' . $name . ']" type="checkbox" />';
				}
				if ( $comment )
					echo " $comment";
				?>
			</td>
		</tr> <?php
	}

	/**
	 * Form checkbox helper, for a single checkbox when an array of boxes
	 * was passed to checkbox()
	 *
	 * @param string $name Checkbox name
	 * @param string $value Checkbox value
	 * @return none
	 * @since 0.9
	 */
	private function single_checkbox( $name, $value ) {
		$option = $this->get_option( $name );
		$checked = '';
		if ( isset( $option[$value] ) && $option[$value] )
			$checked = ' checked="checked" ';
		echo "<span><input $checked type=\"checkbox\" name=\"better-lorem[" . $name . '][' . $value . "]\" /> $value</span>" . "\n";
	}

	/**
	 * Form select helper
	 *
	 * @param string $label Label
	 * @param string $name Select name
	 * @param array $choices Possible choices (strings)
	 * @return none
	 * @since 0.9
	 */
	private function select( $label, $name, $choices, $comment = false ) {
		$current = $this->get_option( $name ); ?>
		<tr valign="top">
			<th scope="row"> <?php
				echo $label; ?>
			</th>
			<td>
				<select name="better-lorem[<?php echo $name ?>]"> <?php
					foreach( $choices as $choice ) {
						if ( $choice == $current )
							$select = ' selected="selected" ';
						else
							$select = '';
						echo "<option $select>$choice</option>\n";
					} ?>
				</select> <?php
				if ( $comment )
					echo ' ' . $comment;
				?>
			</td>
		</tr> <?php
	}

	/**
	 * Output the options page
	 *
	 * @return none
	 * @since 0.9
	 */
	public function admin_page () { ?>
		<div id="nkuttler" class="wrap" >
			<div id="nkcontent">
				<h2><?php _e( 'Better Lorem Ipsum generator', 'better-lorem' ) ?></h2>
					<form method="post" action="options.php"> <?php
					settings_fields( 'better-lorem_options' ); ?>
					<input type="hidden" name="better-lorem[version]" value="<?php echo $this->get_option( 'version' ) ?>" />

					<h3><?php _e( 'Main Options', 'better-lorem' ) ?></h3>
					<table class="form-table form-table-clearnone" > <?php
						// get relevant taxonomies
						$taxonomies	= get_taxonomies( array(
							'public'	=> true,
						) );
						$remove = array_search( 'nav_menu', $taxonomies );
						if ( $remove )
							unset( $taxonomies[$remove] );

						// get relevant post types
						$post_types	= get_post_types( array(
							'public'	=> true,
						) );
						$remove = array_search( 'attachment', $post_types );
						if ( $remove )
							unset( $post_types[$remove] );

						$this->checkbox(
							__( "Create terms for and use these taxonomies for auto-tagging", 'better-lorem' ),
							array( 'taxonomies', $taxonomies )
						);
						$this->input(
							__( 'Create how many terms for each taxonomy', 'better-lorem' ),
							'termnumber'
						);
						$this->checkbox(
							__( "Create posts for", 'better-lorem' ),
							array( 'posttypes', $post_types )
						);
						$this->input(
							__( 'Create how many of each post type', 'better-lorem' ),
							'postnumber',
							__( 'Keep it reasonable, you might hit the PHP execution time limit if you don\'t', 'better-lorem' )
						);
						$this->input(
							__( 'Create how many comments for each post (min)', 'better-lorem' ),
							'mincomments',
							__( 'Zero recommended', 'better-lorem' )
						);
						$this->input(
							__( 'Create how many comments for each post (max)', 'better-lorem' ),
							'maxcomments'
						);
						$this->input(
							__( 'Add how many terms of each taxonomy to every post (min)', 'better-lorem' ),
							'minterms',
							__( 'Zero recommended', 'better-lorem' )
						);
						$this->input(
							__( 'Add how many terms of each taxonomy to every post (max)', 'better-lorem' ),
							'maxterms'
						);
						$this->Checkbox(
							__( 'Apply settings', 'better-lorem' ),
							'letsdothis',
							__( 'Check and submit the form to build your lorem content. Applies the <strong>visible</strong> settings, not the saved ones.', 'better-lorem' )
						);
						?>
					</table>

					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
					</p>
				</form>
			</div> <?php
			require_once( 'nkuttler.php' );
			nkuttler0_2_3_links(
				'better-lorem',
				'http://www.nkuttler.de/'
			); ?>
		</div> <?php
	}

	// nesting ftw!
	// yes, abuse the sanitize callback. please do.
	// @todo fire different hooks for content and taxonomies
	public function lorem( $data ) {
		if ( $data['letsdothis'] ) {
			unset( $data['letsdothis'] );
			if ( $data['taxonomies'] ) {
				foreach( $data['taxonomies'] as $taxonomy => $value ) {
					for ( $i = 0; $i < $data['termnumber']; $i++ ) {
						$this->insert_term( $taxonomy );
					}
				}
			}
			if( $data['posttypes'] ) {
				foreach( $data['posttypes'] as $posttype => $value ) {
					for ( $i = 0; $i < $data['postnumber']; $i++ ) {
						$post_id = $this->insert_post( $posttype );
						if ( $data['maxcomments'] > 1 ) {
							for ( $j = 0; $j < rand( $data['mincomments'], $data['maxcomments'] ); $j++ ) {
								$this->add_comment_to_post( $post_id );
							}
						}
						if ( $data['taxonomies'] ) {
							foreach( $data['taxonomies'] as $taxonomy => $value ) {
								if ( $data['maxterms'] > 1 ) {
									for ( $k = 0; $k < rand( $data['minterms'], $data['maxterms'] ) ; $k++ ) {
										$this->add_terms_to_post( $post_id, $taxonomy );
									}
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	private function insert_term( $taxonomy ) {
		wp_insert_term(
			$this->get_words(),
			$taxonomy
		);
	}

	private function insert_post( $posttype ) {
		global $current_user;
		get_currentuserinfo();
		$post_id = wp_insert_post( array(
			'post_title'	=> $this->get_words( 3, 7 ),
			'post_content'	=> $this->get_words( 40, 120, '' ),
			'post_status'	=> 'publish',
			'post_author'	=> $current_user->ID,
			'post_type'		=> $posttype,
			'post_date'		=> $this->mysql_timestamp(),
			'post_date_gmt'	=> $this->mysql_timestamp()
		) );
		return $post_id;
	}


	// @todo test if post type supports comments
	// @todo threaded comments
	private function add_comment_to_post( $id ) {
		global $current_user;
		get_currentuserinfo();
		wp_insert_comment( array(
			'comment_post_ID'		=> $id,
			'comment_author'		=> $this->get_words( 2, 4 ),
			'comment_author_email'	=> $current_user->user_email,
			'comment_auhor_url'		=> '',
			'comment_content'		=> $this->get_words( 8, 40, '' ),
			'comment_type'			=> '',
			'user_ID'				=> $current_user->ID,
			'comment_date'			=> $this->mysql_timestamp(),
			'comment_date_gmt'		=> $this->mysql_timestamp(),
			'comment_approved'		=> '1'
		) );
	}

	private function mysql_timestamp() {
		$time		= rand( strtotime( "Jan 01 1998" ), time() );
		$timestamp	= date( 'Y-m-d H-i-s', $time );
		return $timestamp;
	}

	private function add_terms_to_post( $post_id, $taxonomy ) {
		if ( $parent = wp_is_post_revision( $post_id ) )
			$post_id = $parent;
		// @todo pass terms as parameter (performance ?)
		$all_terms	= get_terms(
			$taxonomy,
			array(
				'hide_empty'	=> false,
			)
		);
		shuffle( $all_terms );
		wp_set_post_terms(
			$post_id,
			$all_terms[0]->name,
			$taxonomy,
			true
		);
	}

	private function get_words( $min = 1, $max = 3, $format = 'plain' ) {
		$string = $this->Lorem->getContent( rand( $min, $max ), $format, false );
		$string = substr( rtrim( $string ), 0, -1 );
		return $string;
	}

}
