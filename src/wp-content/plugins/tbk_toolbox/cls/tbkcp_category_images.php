<?php
/*
Description: Allows you to add an image to category or any custom term
Author: Forked from Categories Images Plugin by Muhammad Said El Zahlan
See: http://zahlan.net/blog/2012/06/categories-images/ for more info
Usage: 
<?php foreach (get_categories() as $cat) : ?>
    <li>
        <img src="<?php echo z_taxonomy_image_url($cat->term_id); ?>" />
        <a href="<?php echo get_category_link($cat->term_id); ?>"><?php echo $cat->cat_name; ?></a>
    </li>
<?php endforeach; ?>;
*/

class tbkcp_category_images {

    function __construct() {
        $taxonomies = get_taxonomies();
        if (is_array($taxonomies)) {
            foreach ($taxonomies as $taxonomy) {
                add_action($taxonomy . '_add_form_fields', array(&$this, 'add_taxonomy_field'));
                add_action($taxonomy . '_edit_form_fields', array(&$this, 'edit_taxonomy_field'));
            }
        }
        add_action('edit_term', array(&$this, 'save_taxonomy_image'));
        add_action('create_term', array(&$this, 'save_taxonomy_image'));
    }

    function add_taxonomy_field() {
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        echo '<div class="form-field">
		<label for="taxonomy_image">Image</label>
		<input type="text" name="taxonomy_image" id="taxonomy_image" value="" />
	</div>' . $this->z_script();
    }

// inti the plugin
// add image field in add form
// add image field in edit form
    function edit_taxonomy_field($taxonomy) {
        wp_enqueue_style('thickbox');
        wp_enqueue_script('thickbox');
        echo '<tr class="form-field">
		<th scope="row" valign="top"><label for="taxonomy_image">Image</label></th>
		<td><input type="text" name="taxonomy_image" id="taxonomy_image" value="' . get_option('taxonomy_image' . $taxonomy->term_id) . '" /><br /></td>
	</tr>' . $this->z_script();
    }

// upload using wordpress upload
    function z_script() {
        return '<script type="text/javascript">
	    jQuery(document).ready(function() {
			jQuery("#taxonomy_image").click(function() {
				tb_show("", "media-upload.php?type=image&amp;TB_iframe=true");
				return false;
			});
			window.send_to_editor = function(html) {
				imgurl = jQuery("img",html).attr("src");
				jQuery("#taxonomy_image").val(imgurl);
				tb_remove();
			}
	    });
	</script>';
    }

    function save_taxonomy_image($term_id) {
        if (isset($_POST['taxonomy_image']))
            update_option('taxonomy_image' . $term_id, $_POST['taxonomy_image']);
    }

}

function the_taxonomy_image_url($term_id = NULL) {
    if (!$term_id) {

        if (is_category())
            $term_id = get_query_var('cat');
        elseif (is_tax()) {
            $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
            $term_id = $current_term->term_id;
        } else {
            global $post;
            $category = get_the_category($post->ID);
            $term_id = $category[0]->cat_ID;
        }
    }
    if ($term_id) {
        return get_option('taxonomy_image' . $term_id);
    }
    return false;
}
?>