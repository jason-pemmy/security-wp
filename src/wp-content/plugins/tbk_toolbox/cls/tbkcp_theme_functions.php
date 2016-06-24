<?php
/*
Description: An assortment of re-usable theme functions
Author: Paul MacLean
Usage: 
Most functions here will follow a similar format
global $tbk_toolbox;
if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
    $tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
	$tbktf->your_function(your_parameters)
}

*/

define('TBKCP_THEME_FUNCTIONS_PRE', 'tbkcp_theme_functions_');

class tbkcp_theme_functions {
    /*      Function: get_nested_archives
      Gets a year/month list of archives
     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
      		echo $tbktf->get_nested_archives();
		}
      (end)
     */

    function get_nested_archives() {

        global $wpdb;
        //global $post;
        /**/
        $years = $wpdb->get_col("SELECT DISTINCT YEAR(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC");
        foreach ($years as $year) {
            ?>
            <li class ="year <?php echo get_query_var('year') == $year ? 'current_page_item' : '' ?>"><a href="<?php echo get_year_link($year); ?> "><?php echo $year; ?></a>
            </li>
            <ul class ="children month" style ="<?php echo get_query_var('year') != $year ? 'display:none' : '' ?>">
                <?php
                $months = $wpdb->get_col("SELECT DISTINCT MONTH(post_date) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post' AND YEAR(post_date) = '" . $year . "' ORDER BY post_date DESC");
                foreach ($months as $month) {
                    ?>
                    <li>
                        <a href="<?php echo get_month_link($year, $month); ?>"><?php echo date('F', mktime(0, 0, 0, $month)); ?></a>
                    </li>
                <?php } ?>
            </ul>

            <?php
        }
    }

    /* Function: get_top_parent
      Gets the top most parent page's title and id

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$top_parent = $tbktf->get_top_parent();
			echo $top_parent['id'];
		}
      (end)
     */

    function get_top_parent() {
        global $post;
        $parent = array_reverse(get_post_ancestors($post->ID));
        $first_parent = get_page($parent[0]);
        $return = array(
            'title' => $first_parent->post_title,
            'id' => $first_parent->ID
        );

        return $return;
    }

    /* Function: get_tag_list
      Returns a comma seperated list of all the post tag names

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tag_list = $tbktf->get_tag_list();
			echo $tag_list;
		}
      (end)
     */

    function get_tag_list() {
        $posttags = get_the_tags();
        if ($posttags) {
            $i = 0;
            foreach ($posttags as $tag) {
                if ($i == 0) {
                    $tags .= $tag->name;
                } else {
                    $tags .= ', ' . $tag->name;
                }
                $i++;
            }
            return $tags;
        }
        return false;
    }

    /* Function: get_term_list
      Returns a linked list of terms

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tag_list = $tbktf->get_term_list(get_the_ID());
			echo $tag_list;
		}
      (end)
     */

    function get_terms_list($post_id=false, $tax=false, $return='linked') {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }
        $terms = wp_get_post_terms($post_id, $tax);
        $terms_array = array();
        foreach ($terms as $term) {
            if ($return == 'linked') {
                array_push($terms_array, '<a href ="' . get_term_link($term) . '">' . $term->name . '</a>');
            } else {
                array_push($terms_array, $term->term_id);
            }
        }
        return $terms_array;
    }

    /* Function: get_category_list
      Returns an array of linked post categories

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tag_list = $tbktf->get_category_list();
			echo implode(',',$categories);
		}
      (end)
     */

    function get_category_list() {
        $category = get_the_category();
        $categories = array();
        foreach ($category as $cat) {
            array_push($categories, '<a href ="' . get_category_link($cat->term_id) . '">' . $cat->name . '</a>');
        }
        return $categories;
    }

    /* Function: get_child_or_sibling_pages
      Get the child pages if on parent or sibling pages if in parent. Can accept custom post types

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			echo '<ul>';
			$args = array(
				'link_before' => '<span class="icon"></span>'
			);
			$tbktf->get_child_or_sibling_pages(get_the_ID());
			echo '</ul>';
		}
      ?>
      </ul>

     * Parameters*
      @$new_args: a set of wp_list_pages (wordpress) args
      @post : a specific post id. Defaults to global $post
      (end)
     */

    function get_child_or_sibling_pages($new_args=array(), $post = null, $exclude_parent_id=false) {
        if (!isset($post)) {
            global $post;
        }

        $defaults = array(
            'title_li' => '',
            'child_of' => $post->ID,
            'sort_column' => 'menu_order',
            'post_type' => 'page'
        );
        $args = wp_parse_args($new_args, $defaults);


        if ($post->post_parent && $post->post_parent != $exclude_parent_id) {
            $args['child_of'] = $post->post_parent;
        }
        if ($args['post_type'] != 'page') {
            //$args['child_of'] = 0;
            $this->wp_list_post_types($args);
        } else {
            wp_reset_query();
            wp_list_pages($args);
        }
    }

    /* Function: get_category_list
      Return page/post/custom-post data by slug

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			echo $tbktf->get_page_content('home');
		}
      (end)
     */

    function get_page_content($slug, $arr=false, $new_args=array()) {
        $page = get_page_by_path($slug);

        $defaults = array(
            'p' => $page->ID,
            'post_type' => 'page',
            'posts_per_page' => 1
        );

        $args = wp_parse_args($new_args, $defaults);

        $the_query = new WP_Query($args);
        $return = array();
        while ($the_query->have_posts()) : $the_query->the_post();
            if ($arr) {
                $return['url'] = get_permalink();
                $return['title'] = get_the_title();
                $return['content'] = get_the_content();
                $return['image'] = get_the_post_thumbnail();
            } else {
                the_content();
            }
        endwhile;
        wp_reset_postdata();
        if ($return)
            return $return;
    }

    /* Function: get_breadcrumbs
      Returns a linked list of the breadcrumb trail
     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tag_list = $tbktf->get_term_list(get_the_ID());
			echo $tbktf->get_breadcrumbs();
		}
      (end)
     */

    function get_breadcrumbs() {

        $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
        $delimiter = ''; // delimiter between crumbs
        $home = 'Home'; // text for the 'Home' link
        $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show
        $before = '<span class="current">'; // tag before the current crumb
        $after = '</span>'; // tag after the current crumb

        global $post;
        $homeLink = get_bloginfo('url');

        if (is_home() || is_front_page()) {

            if ($showOnHome == 1)
                echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a></div>';
        } else {

            echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

            if (is_category()) {
                $thisCat = get_category(get_query_var('cat'), false);
                if ($thisCat->parent != 0)
                    echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
                echo $before . 'Archive by category "' . single_cat_title('', false) . '"' . $after;
            } elseif (is_search()) {
                echo $before . 'Search results for "' . get_search_query() . '"' . $after;
            } elseif (is_day()) {
                echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
                echo '<a href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
                echo $before . get_the_time('d') . $after;
            } elseif (is_month()) {
                echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
                echo $before . get_the_time('F') . $after;
            } elseif (is_year()) {
                echo $before . get_the_time('Y') . $after;
            } elseif (is_single() && !is_attachment()) {
                if (get_post_type() != 'post') {
                    $post_type = get_post_type_object(get_post_type());
                    $slug = $post_type->rewrite;
                    echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->name . '</a>';
                    $parent_id = $post->post_parent;
                    if ($parent_id) {
                        $breadcrumbs = array();
                        while ($parent_id) {
                            $page = get_page($parent_id);
                            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                            $parent_id = $page->post_parent;
                        }

                        $breadcrumbs = array_reverse($breadcrumbs);
                        for ($i = 0; $i < count($breadcrumbs); $i++) {
                            echo $breadcrumbs[$i];
                            if ($i != count($breadcrumbs) - 1)
                                echo ' ' . $delimiter . ' ';
                        }
                    }

                    if ($showCurrent == 1)
                        echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
                } else {
                    $cat = get_the_category();
                    $cat = $cat[0];
                    $cats = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                    if ($showCurrent == 0)
                        $cats = preg_replace("#^(.+)\s$delimiter\s$#", "$1", $cats);
                    echo $cats;
                    if ($showCurrent == 1)
                        echo $before . get_the_title() . $after;
                }
            } elseif (!is_single() && !is_page() && get_post_type() != 'post' && !is_404()) {
                $post_type = get_post_type_object(get_post_type());

                echo $before . $post_type->labels->name . $after;
            } elseif (is_attachment()) {
                $parent = get_post($post->post_parent);
                $cat = get_the_category($parent->ID);
                $cat = $cat[0];
                echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
                echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
                if ($showCurrent == 1)
                    echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            } elseif (is_page() && !$post->post_parent) {
                if ($showCurrent == 1)
                    echo $before . get_the_title() . $after;
            } elseif (is_page() && $post->post_parent) {
                $parent_id = $post->post_parent;
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);
                    $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
                    $parent_id = $page->post_parent;
                }

                $breadcrumbs = array_reverse($breadcrumbs);
                for ($i = 0; $i < count($breadcrumbs); $i++) {
                    echo $breadcrumbs[$i];
                    if ($i != count($breadcrumbs) - 1)
                        echo ' ' . $delimiter . ' ';
                }
                if ($showCurrent == 1)
                    echo ' ' . $delimiter . ' ' . $before . get_the_title() . $after;
            } elseif (is_tag()) {
                echo $before . 'Posts tagged "' . single_tag_title('', false) . '"' . $after;
            } elseif (is_author()) {
                global $author;
                $userdata = get_userdata($author);
                echo $before . 'Articles posted by ' . $userdata->display_name . $after;
            } elseif (is_404()) {
                echo $before . 'Error 404' . $after;
            }

            if (get_query_var('paged')) {
                if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
                    echo ' (';
                echo __('Page') . ' ' . get_query_var('paged');
                if (is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author())
                    echo ')';
            }

            echo '</div>';
        }
    }

    /* Function: has_children
      Checks if a certian page/post/custom-post has children

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$args = array(
				'post_type' => 'product'
			);
			$children = $tbktf->has_children($args);
		}
      (end)
     */

    function has_children($new_args=array()) {
        global $post;
        $defaults = array(
            'post_parent' => $post->ID
        );
        $args = wp_parse_args($new_args, $defaults);

        if ($args['post_type'] == 'page') {
            unset($args['post_type']);
            $children = get_pages($args);
        } else {
            $children = get_posts($args);
        }
        if (count($children) != 0) {
            return true;
        }
        return false;
    }

    /* Function: truncate_string
      Will truncate the string given a string and max length

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			//Suffix default is ...
			$string = $tbktf->truncate_string('your-string', '25' 'your-suffix');
		}
      (end)
     */

    function truncate_string($string, $max_length, $suffix='...') {
        $str_length = strlen($string);
        if ($str_length > $max_length) {
            return substr($string, 0, $max_length) . $suffix;
        } else {
            return $string;
        }
    }

    /* Function: wp_list_post_types
      Lists out post types in the wp_list_pages style (called by get_child_or_sibling_pages if custom post type detected)

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tbktf->wp_list_post_types($args);
		}

     * Parameters*
      @$args: a set of wp_list_pages (wordpress) args

      (end)
     */

    function wp_list_post_types($args) {

        $defaults = array(
            'numberposts' => -1,
            'offset' => 0,
            'orderby' => 'menu_order, post_title',
            'order' => 'ASC',
            'post_type' => 'page',
            'depth' => 0,
            'show_date' => '',
            'date_format' => get_option('date_format'),
            'child_of' => 0,
            'exclude' => '',
            'include' => '',
            'title_li' => __('Pages'),
            'echo' => 1,
            'link_before' => '',
            'link_after' => '',
            'exclude_tree' => '');

        $r = wp_parse_args($args, $defaults);

        extract($r, EXTR_SKIP);

        $output = '';
        $current_page = 0;

        // sanitize, mostly to keep spaces out
        $r['exclude'] = preg_replace('/[^0-9,]/', '', $r['exclude']);

        // Allow plugins to filter an array of excluded pages (but don't put a nullstring into the array)
        $exclude_array = ( $r['exclude'] ) ? explode(',', $r['exclude']) : array();
        $r['exclude'] = implode(',', apply_filters('wp_list_post_types_excludes', $exclude_array));

        // Query pages.
        $r['hierarchical'] = 0;
        $pages = get_pages($r);

        if (!empty($pages)) {
            if ($r['title_li'])
                $output .= '<li class="pagenav">' . $r['title_li'] . '<ul>';

            global $wp_query;
            if (($r['post_type'] == get_query_var('post_type')) || is_attachment())
                $current_page = $wp_query->get_queried_object_id();
            $output .= walk_page_tree($pages, $r['depth'], $current_page, $r);

            if ($r['title_li'])
                $output .= '</ul></li>';
        }

        $output = apply_filters('wp_list_pages', $output, $r);

        if ($r['echo'])
            echo $output;
        else
            return $output;
    }

    /* Function: is_child
      Detects if the global post is a child by page id or slug

     * Usage Examples:*
      (code)
		global $tbk_toolbox;
		if ($tbk_toolbox->module_enabled('tbkcp_theme_functions')) {
	    	$tbktf = & $tbk_toolbox->get_module('tbkcp_theme_functions');
			$tbktf->is_child('home');
		}
      (end)
     */

    function is_child($page_id_or_slug) {
        global $post;
        if (!is_int($page_id_or_slug)) {
            $page = get_page_by_path($page_id_or_slug);
            $page_id_or_slug = $page->ID;
        }
        if (is_page() && $post->post_parent == $page_id_or_slug) {
            return true;
        } else {
            return false;
        }
    }

}
?>
