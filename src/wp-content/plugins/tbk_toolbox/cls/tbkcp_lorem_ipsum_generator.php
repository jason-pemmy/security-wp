<?php
/*
Description: Includes the better lorem ipsum generator to auto populate posts/pages with lorem ipsum content
Author: Paul MacLean.  Original plugin by Nicolas Kuttler.
Usage: See the Better Lorem Ipsum Settings in the tools menu.  Or, click Settings to add Images or Taxonomy terms to existing posts.
*/
class tbkcp_lorem_ipsum_generator {
    function __construct() {
		$this->location = 'http://wordpress.org/extend/plugins/better-lorem/';
		
		add_action('wp_ajax_tbkcp_do_lorem_extras',array(&$this,'do_lorem_extras'));
		add_action('wp_ajax_tbkcp_do_lorem_extras_count',array(&$this,'do_lorem_extras_count'));
    }

	function form_options(){
		$post_types = get_post_types(array(
			'public' => true
		));
		
		$taxonomies = get_taxonomies(array(
			'public' => true
		));
		
		$options = array(
			'post_types' => array(
				'type' => 'checkbox',
				'options' => array_keys($post_types),
				'description' => 'Select which Post Types you would like to work with'
			),
			'add_image' => array(
				'type' => 'checkbox',
				'options' => array(
					'&nbsp;'
				),
				'description' => 'Select this to add featured image to each post according to the Post Types chosen above.  The image will be a random image found at photo.net.'
			),
			'overwrite_image' => array(
				'type' => 'checkbox',
				'options' => array(
					'&nbsp;'
				),
				'description' => 'Select this to always add an image.  If left unselected, we will only add an image to those posts without an existing featured image.'
			),
			'taxonomies' => array(
				'type' => 'checkbox',
				'options' => array_keys($taxonomies),
				'description' => 'Select which Taxonomies you would like to set for each post.  A word of caution - not all taxonomies go with each post type'
			),
			'taxonomy_min' => array(
				'type' => 'text',
				'description' => 'The minimum number of terms in each taxonomy to set for each post. (default 1)'
			),
			'taxonomy_max' => array(
				'type' => 'text',
				'description' => 'The maximum number of terms in each taxonomy to set for each post. (default 3)'
			),
		);
		
		?>
<p class="description">Use this to attach a random featured image (pulled from <a href="http://photo.net/gallery/photocritique/filter?db_status=busy&category=NoNudes&period=365" target="_blank">photo.net</a>) to posts with the specified post types.  You can also add terms from any given taxonomies to the posts.  </p>
		<?php

		tbk_toolbox::options_form(get_class($this),$options);
		
		$this->settings_scripts();
	}
	
	function settings_scripts(){
		?>
<script type="text/javascript">
jQuery(function($){
	var $form = $('input[name="<?php echo get_class($this); ?>_update"]').val('Do the extras!').parents('form');
	if ($form.data('initialized')){
		return;
	}
	$form.data('initialized',true);
	$form.submit(function(){
		var serialized = $(this).serialize();
		$form.find('input,select,textarea').prop('disabled',true);

		function doIt(offset){
			$.post(ajaxurl,{
				action: 'tbkcp_do_lorem_extras',
				form_data: serialized,
				offset: offset
			},function(data){
				try{
					var result = $.parseJSON(data);
					offset+= parseInt(result.processed);
					$('#lorem-current').text(offset);
					if (result.continue){
						doIt(offset);
					}
					else{
						$('#lorem-feedback').text('Done!');
						setTimeout(function(){
							$form.find('.spinner, #lorem-feedback').remove();
							$form.find('input,select,textarea').prop('disabled',false);
						},2000);
					}
				}
				catch(e){
					console.log(data);
					$('#lorem-feedback').text('Error.  Check the console.');
					$form.find('input,select,textarea').prop('disabled',false);
				}
			});
		}
		$form.find('.spinner, #lorem-feedback').remove();
		$form.append($('<span/>').addClass('spinner').show().css({float:'left',opacity:1}));
		$form.append($('<span/>').addClass('description').attr('id','lorem-feedback'));
		$.post(ajaxurl,{
			action: 'tbkcp_do_lorem_extras_count',
			form_data: serialized,
		},function(data){
			if (data == '0'){
				$('#lorem-feedback').text('No posts found.  Nothing to do.  Try again.');
				$form.find('.spinner').remove();
				$form.find('input,select,textarea').prop('disabled',false);
			}
			else{
				$('#lorem-feedback').text('Processed ').append($('<span/>').attr('id','lorem-current').text('0')).append($('<span/>').attr('id','lorem-total').text(' of '+data+' posts'));
				doIt(0);
			}
		});
		return false;
	});
});
</script>
		<?php
	}
	
	function do_lorem_extras_count(){
		$form_data = wp_parse_args($_POST['form_data']);
		$module = get_class($this);
		$form_data = $form_data[$module];
		extract($form_data);

		$total = 0;
		foreach ((array)$post_types as $post_type){
			$total+=wp_count_posts($post_type)->publish;
		}
		
		echo $total;
		die();
	}
	
	function do_lorem_extras(){
		$form_data = wp_parse_args($_POST['form_data']);
		$module = get_class($this);
		$form_data = $form_data[$module];
		
		extract($form_data);

		if (!empty($taxonomies)){
			$wp_taxonomies = get_taxonomies(array(
				'public' => true
			),'objects');

			$tag_like_taxonomies = array();
			foreach ($wp_taxonomies as $tax){
				if (!$tax->hierarchical){
					$tag_like_taxonomies[] = $tax->name;
				}
			}

			$taxonomy_min = empty($taxonomy_min) ? 1 : intval($taxonomy_min);
			$taxonomy_max = empty($taxonomy_max) ? 3 : intval($taxonomy_max);

			// Gather Terms
			$terms = array();
			foreach ($taxonomies as $taxonomy){
				$terms[$taxonomy] = get_terms($taxonomy,array('fields' => (in_array($taxonomy,$tag_like_taxonomies)  ? 'names' : 'ids'),'hide_empty' => false));
			}
		}
		
		$add_attachment = !empty($add_image);
		$overwrite = !empty($overwrite_image);
		
		include_once( ABSPATH . 'wp-admin/includes/image.php' );
		$result = array(
			'continue' => false,
			'processed' => 0
		);
		$limit = ($add_attachment ? 5 : -1);
		$offset = ($add_attachment ? (!empty($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0) : 0);
		foreach ((array)$post_types as $post_type){
			$posts = get_posts( "post_type=$post_type&posts_per_page=$limit&offset=$offset" );

			foreach ($posts as $post){
				// going to add a random term to the post
				if (!empty($terms)){
					foreach ($terms as $taxonomy => $specific_terms){
						$count = rand($taxonomy_min,$taxonomy_max);
						$term_offset = rand(0,count($specific_terms) - $count);
						$terms_to_add = array_slice($specific_terms,$term_offset,$count);

						//print("Adding $taxonomy",false);
						//print($terms_to_add,false);
						wp_set_post_terms($post->ID,$terms_to_add,$taxonomy,false);
						shuffle($terms[$taxonomy]);
					}
				}

				if ($add_attachment and ($overwrite or !has_post_thumbnail($post->ID))){
					$id = $this->attach_random_image($post->ID,$overwrite);
					//print("attached $id to $post->ID",false);
				}
				$result['processed']++;
			}
			if ($add_attachment and count($posts)){
				$result['continue'] = true;
			}
		}
		
		echo json_encode($result);
		die();
	}
	
	function attach_random_image($post_id,$overwrite=true){
		static $offset,$iteration,$markup,$matches;
		if (!isset($offset)){
			$offset = rand(0,1000); //(!empty($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0);
			$iteration = 0;
		}
		$iteration++;
		if ($iteration % 12 == 0){
			$offset++;
			$iteration = 0;
		}
		if ($overwrite){
			// Note, this removes all existing attachments, and then replaces the current $product with the new one (which won't have attachments)
			$attachments = get_children( 'post_type=attachment&post_parent='.$post_id );
			if ($attachments){
				foreach ($attachments as $attachment){
					wp_delete_attachment($attachment->ID,true);
				}
			}
		}
		if (empty($markup) or $iteration == 0){
			$ch = curl_init('http://photo.net/gallery/photocritique/filter?db_status=busy&category=NoNudes&period=365&start_index='.$offset);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			$markup = curl_exec($ch);
			curl_close($ch);
			preg_match_all('/<img src=\"(http:\/\/thumbs.photo.net\/photo\/[^\"]+)/',$markup,$matches);
			shuffle($matches[1]);
		}

		if ($matches){
			$image = str_replace('thumbs.photo.net','gallery.photo.net',$matches[1][$iteration % 12]);
			$image = str_replace('-sm','-lg',$image);
			//print($image,false);
			return $this->curl_image($image,$post_id);
		}
		return false;
	}

	function curl_image($img_url,$postID) {
		$ch = curl_init(str_replace(array(' '),array('%20'),$img_url));
		$uploads = wp_upload_dir();
		$filename = wp_unique_filename( $uploads['path'], basename($img_url));
		$path =  trailingslashit($uploads['path']); // dirname(__FILE__) . '/screenshots/';
		if (false and file_exists($path . basename($img_url))){
			// Image already exists
			return false;
		}
		$fp = fopen($path . $filename, 'wb');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$result = curl_exec($ch);
		$mime = curl_getinfo($ch,CURLINFO_CONTENT_TYPE);
		fclose($fp);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($result and $code != 404){
			//$mime = mime_content_type($path . $filename);
			if ('image/' == substr($mime, 0, 6)){
				return $this->attach_existing_image($filename,$postID,$mime);
			}
		}
		@unlink($path . $filename);
		return false;
	}

	function attach_existing_image($filename,$postID,$mime=null){
		$uploads = wp_upload_dir();
		$file = $uploads['path'] . "/$filename";
		$url = $uploads['url'] . "/$filename";
		$type = ($mime !== null ? $mime : mime_content_type($file));

		$name_parts = pathinfo($file);
		$name = trim( substr( $name_parts['basename'], 0, -(1 + strlen($name_parts['extension'])) ) );

		$file = array( 'file' => $file, 'url' => $url, 'type' => $type );
		$url = $file['url'];
		$type = $file['type'];
		$file = $file['file'];
		$title = $name;
		$content = '';

		// Construct the attachment array
		$attachment = array(
			'post_mime_type' => $type,
			'guid' => $url,
			'post_parent' => $postID,
			'post_title' => $title,
			'post_content' => $content,
		);

		// Save the data
		$id = wp_insert_attachment($attachment, $file, $postID);
		if (true or !has_post_thumbnail($postID)){
			set_post_thumbnail($postID,$id);
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );

		return $id;
	}	
}

?>