<?php
/*
Description: Registers custom posts given an array of names. Handles all labeling and rewrites by default.
Author: Paul MacLean
Usage: 
<?php
//If in a theme you need to access the global $tbk_toolbox which stores all of the tbkcp internal class references
global $tbk_toolbox;
if ($tbk_toolbox->module_enabled('tbkcp_register_post')) {
    $tbkrp = & $tbk_toolbox->get_module('tbkcp_register_post');
    $custom_posts = array(
        'Legal Resource' => $args, //You can send override args to the wordpress function register_post_type here if needed
        'eGuide' => array('menu_icon' => get_stylesheet_directory_uri() . '/images/eguide-icon.png'),
        'Community Resource' => array()
    );
    //Send an array of titles and any args you wish to overwrite. The slug generation uses santize_title()
    $tbkrp->add_custom_posts($custom_posts);
}
?>
*/
define('TBKCP_REGISTER_POST_PRE', 'tbkcp_register_post_');

class tbkcp_register_post {

    protected $custom_posts = array();
    protected $custom_post_slugs = array();

    /* Function: add_custom_posts
      adds the actions to register the posts
     */

    function add_custom_posts($custom_posts) {
        $this->custom_posts = $custom_posts;
        add_action('init', array($this, 'add_args'));
        add_action('register_post_custom_init_hook', array($this, 'register_post_custom_init'));
    }

    /* Function: add_custom_posts
      calls the do_action for register_post_custom_init_hook
     */

    function add_args() {
        $args = $this->custom_posts;
        do_action('register_post_custom_init_hook', $args);
    }

    /* Function: register_post_custom_init
      Sets the registered posts labels and args.
     */

    function register_post_custom_init($custom_posts) {

        foreach ($custom_posts as $custom_post_name => $custom_post_args) {
            $custom_post_slug = sanitize_title($custom_post_name);
            //Check if we have specified a plural
            if ($custom_post_args['plural']) {
                $custom_post_slug_plural = sanitize_title($custom_post_args['plural']);
                $custom_post_name_plural = $custom_post_args['plural'];
                unset($custom_post_args['plural']);
                //If we havent specified and the title ends in Y , handle it
            } else {
                $last_char = $custom_post_name[strlen($custom_post_name) - 1];
                if ($last_char == 'y') {
                    $custom_post_name_plural = substr_replace($custom_post_name, "", -1) . 'ies';
                    $custom_post_slug_plural = substr_replace($custom_post_slug, "", -1) . 'ies';
                } else {
                    $custom_post_name_plural = $custom_post_name . 's';
                    $custom_post_slug_plural = $custom_post_slug . 's';
                }
            }


            $default_labels = array(
                'name' => _x($custom_post_name_plural, 'post type general name'),
                'singular_name' => _x($custom_post_name, 'post type singular name'),
                'add_new' => _x('Add New', $custom_post_name),
                'add_new_item' => __('Add New ' . $custom_post_name),
                'edit_item' => __('Edit ' . $custom_post_name),
                'new_item' => __('New ' . $custom_post_name),
                'all_items' => __('All ' . $custom_post_name_plural),
                'view_item' => __('View ' . $custom_post_name),
                'search_items' => __('Search ' . $custom_post_name_plural),
                'not_found' => __('No ' . $custom_post_name_plural . ' found'),
                'not_found_in_trash' => __('No ' . $custom_post_name_plural . ' found in Trash'),
                'parent_item_colon' => '',
                'menu_name' => __($custom_post_name_plural, 'your_text_domain')
            );

            $labels = wp_parse_args($custom_post_args['labels'], $default_labels);

            $default_args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'query_var' => true,
                'rewrite' => array('slug' => _x($custom_post_slug_plural, 'URL slug')),
                'capability_type' => 'page',
	            'capabilities' => array(),
                'has_archive' => true,
                'hierarchical' => true,
                'menu_position' => null,
                'menu_icon' => null,
                'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'page-attributes')
            );

            $args = wp_parse_args($custom_post_args, $default_args);

            //var_dump($args);

            array_push($this->custom_post_slugs, $custom_post_slug);
            register_post_type($custom_post_slug, $args);
        }
        update_option(TBKCP_REGISTER_POST_PRE . 'slugs', $this->custom_post_slugs);
    }

    /* Function: register_post_custom_init
      Shows the current custom post slugs
     */

    function get_custom_post_slugs() {
        return $this->custom_post_slugs;
    }

    function form_options() {
        $slugs = get_option(TBKCP_REGISTER_POST_PRE . 'slugs');
        ?>
        <b>List of current custom post slugs</b>
        <?php
        if ($slugs) {
            echo '<strong>Current Custom Post Slugs: </strong>' . implode(',', $slugs);
        }
    }

}
?>
