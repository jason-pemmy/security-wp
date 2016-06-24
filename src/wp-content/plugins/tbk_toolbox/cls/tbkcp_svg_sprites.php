<?php
/*
Description: A way to automitically create sprites out of SVG images (experimental at this point)
Author: Trevor Mills
Usage:
1. Right now, only a set of form icons is available
2. Click Settings for SVG Sprite.  Add a Form Icon State.  Fill in values.
3. Profit
*/


class tbkcp_svg_sprites {

	function __construct() {
		tbk_toolbox::add_redirect('wp-content/plugins/tbk_toolbox/assets/css/svg/([^/]*)\.svg',array(&$this,'generate_sprite'));
		tbk_toolbox::add_redirect('wp-content/plugins/tbk_toolbox/assets/css/svg/([^/]*)\.css',array(&$this,'generate_css'));
		tbk_toolbox::add_redirect('wp-content/plugins/tbk_toolbox/assets/css/svg/([^/]*)\.png',array(&$this,'generate_sprite_fallback'));
		if (!is_admin()){
			add_action('wp_print_styles',array(&$this,'print_styles'));
		}
	}

	public static function basename($icon){
		return basename($icon,'.svg');
	}

	public function get_available_icons($group){
		if (!is_dir(TBKCP_PLUGIN_DIR.'assets/css/svg/'.$group)){
			return false;
		}
		else{
			return glob(TBKCP_PLUGIN_DIR."assets/css/svg/$group/*.svg");
		}
	}

	function generate_sprite($group,$return = false){
		/* User Agent - test to see if the SVG is being requested
		 * Note: because of the way the PNG is generated (calling $this->generate_sprite),
		 * the SVG will look like it's being served as well, but it's not.
		$fh = fopen(dirname(__FILE__).'/tester.txt','a');
		fwrite($fh,"SVG - ".$_SERVER['HTTP_USER_AGENT']."\n");
		fclose($fh);
		*/
		$available_icons = $this->get_available_icons($group);
		if (!$available_icons){
			return;
		}

		$svg_string = <<<XML
<?xml version='1.0' standalone='yes'?>
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve">
	<defs>
	</defs>
</svg>
XML;

		$svg = new SimpleXMLElement($svg_string);
		$svg_icons = array();

		// Define the icons
		foreach ($available_icons as $icon){
			$xml = simplexml_load_file($icon);
			$g = $svg->defs->addChild('g');
			$g->addAttribute('id',self::basename($icon));

			foreach ($xml as $element){
				$element['fill'] = 'inherit';
				$child = $g->addChild($element->getName());

				foreach($element->attributes() as $key => $value){
					$child->addAttribute($key,$value);
				}
			}

			$svg_icons[self::basename($icon)] = $xml;

		}

		$states = tbk_toolbox::get_settings(get_class($this),"$group-states");
		$x = 0;
		$y = 0;
		$viewport = (object)array(
			'width' => 0,
			'height' => 0
		);
		$y_enough = 2000;
		foreach ($states as $state){
			$viewport->width = max($viewport->width,$state['width']);
			foreach ((array)$state['icons'] as $icon){
				$viewport->height = $y + $state['height'];
				$use = $svg->addChild('use');
				$use['xlink:href'] = '#'.$icon;
				$use->addAttribute('fill',$state['fill']);

				$position = array('x' => 0, 'y' => 0);

				$scale = (empty($state['scale']) ? 1 : floatval($state['scale']));
				$w = intval($svg_icons[$icon]['width'])*$scale;
				$h = intval($svg_icons[$icon]['height'])*$scale;

				switch($state['align']){
				case 'top left':
					// Already good to go
					break;
				case 'top right':
					$position['x'] = ($state['width'] - $w);
					break;
				case 'bottom right':
					$position['x'] = ($state['width'] - $w);
					$position['y'] = ($state['height'] - $h);
					break;
				case 'bottom left':
					$position['y'] = ($state['height'] - $h);
					break;
				case 'top center':
					$position['x'] = ($state['width'] - $w) / 2;
					break;
				case 'center right':
					$position['x'] = ($state['width'] - $w);
					$position['y'] = ($state['height'] - $h) / 2;
					break;
				case 'bottom center':
					$position['x'] = ($state['width'] - $w) / 2;
					$position['y'] = ($state['height'] - $h);
					break;
				case 'center left':
					$position['y'] = ($state['height'] - $h) / 2;
					break;
				case 'center':
				default:
					$position['x'] = ($state['width'] - $w) / 2;
					$position['y'] = ($state['height'] - $h) / 2;
					break;
				}
				$use->addAttribute('transform',"translate({$position['x']},".($y+$position['y']).")");

				if (!empty($state['scale'])){
					$use['transform'] .= " scale({$state['scale']})";
				}

				if (in_array('height-varies',(array)$state['options'])){
					$y+=$y_enough;
				}
				else{
					$y+=$state['height'];
				}
			}

		}
		$svg->addAttribute('viewBox',"0 0 $viewport->width $viewport->height.583");
		$svg->addAttribute('width',$viewport->width.'px');
		$svg->addAttribute('height',$viewport->height.'px');

		if ($return){
			return $svg->asXML();
		}
		else{
			header('Cache-control: max-age='.(60*60*24*365));
			header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
			header("Content-type: image/svg+xml");
			echo $svg->asXML();
			die();
		}
	}

	function generate_sprite_fallback($group){
		/* User Agent - test to see if the PNG is being requested
		$fh = fopen(dirname(__FILE__).'/tester.txt','a');
		fwrite($fh,"PNG - ".$_SERVER['HTTP_USER_AGENT']."\n");
		fclose($fh);
		*/
		$svg = $this->generate_sprite($group,true);
		$hash = md5($svg);
		// cash the results so we don't constantly hit the svg2raster server.
		if (get_transient("$group.svg-hash") != $hash){
			$url = 'http://svg2raster.aws.af.cm/convert.jsp'; // Thanks to http://www.fileformat.info/ for having this online tool that I can POST to

			//Save string into temp file
			$file = tempnam(sys_get_temp_dir(), 'POST');
			file_put_contents($file, $svg);

			$post = array(
				"stdin"=>'@'.$file,
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result=curl_exec ($ch);
			curl_close ($ch);
			unlink($file);
			$result = new SimpleXMLElement($result);
			$image = $result->xpath('/html/body/p/img');
			if (count($image)){
				$data = str_replace('data:image/png;base64,','',(string)$image[0]['src']);
				set_transient("$group.svg-hash",$hash);
				set_transient("$group.svg-png",$data);
			}
		}
		else{
			$data = get_transient("$group.svg-png");
		}
		header('Cache-control: max-age='.(60*60*24*365));
		header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
		header('Content-Type: image/png');
		echo base64_decode($data);
		die();
	}

	function generate_css($group){
		$available_icons = $this->get_available_icons($group);
		if (!$available_icons){
			return;
		}
		$states = tbk_toolbox::get_settings(get_class($this),"$group-states");

		$viewport = (object)array(
			'width' => 0,
			'height' => 0
		);
		$y_enough = 2000;
		foreach ($states as $state){
			$viewport->width = max($viewport->width,$state['width']);
			foreach ((array)$state['icons'] as $icon){
				$viewport->height = $y + $state['height'];
			}
		}

		$css = '';
		$x = 0;
		$y = 0;
		$y_enough = 2000;
		foreach ($states as $state){
			switch($state['position']){
			case 'right':
				$padding = $x = 'right';
				break;
			case 'left':
			default:
				$padding = $x = 'left';
				break;
			}
			if (!empty($state['sprite-selector'])){
				if( stristr( $state['name'], 'NO SVG' ) ){
					// Note the \9 version of the background-image is the fallback for IE < 9.  Those browsers do not support SVG images, so setting the background-image this way
					// forces it to load the PNG version.
					$css.= $state['sprite-selector']."{background-image:url($group.png)}\n";
				}else{
					// Note the \9 version of the background-image is the fallback for IE < 9.  Those browsers do not support SVG images, so setting the background-image this way
					// forces it to load the PNG version.
					$css.= $state['sprite-selector']."{background-image:url($group.svg);background-image:url($group.png)\9;background-position:{$x} top;background-repeat:no-repeat;background-size:{$state['width']}px auto;padding-{$padding}:{$state['width']}px}\n";
				}
			}

			if (!empty($state['element-selector'])){
				foreach ($state['icons'] as $icon){
					// Note the \9 version of the background-position is the fallback for IE < 9.  Those browsers do not support background-size, so setting the position this way puts the image there
					// even if the image is not exactly the right size, it's at least there.
					$css.= str_replace('$icon',$icon,$state['element-selector'])."{background-position:{$x} -".($y*$state['width']/$viewport->width)."px;background-position:{$x} -{$y}px \9}\n";
					if (in_array('height-varies',(array)$state['options'])){
						$y+=$y_enough;
					}
					else{
						$y+=$state['height'];
					}
				}
			}
		}
		header('Cache-control: max-age='.(60*60*24*365));
		header('Expires: '.gmdate(DATE_RFC1123,time()+60*60*24*365));
		header("Content-type: text/css");
		echo $css;
		die();
	}

	function form_options(){

		$module = get_class($this);


		$sprite_directories = array('form-icons');

		$options = array();
		foreach ($sprite_directories as $group){
			$elements = array();
			$icons = self::get_available_icons($group);
			if ($icons !== false){
				foreach ($icons as $icon){
					$name = self::basename($icon);
					$elements[$name] = $name;
				}

				$options["$group-states"] = array(
					'label' => tbk_toolbox::labelize("$group State"),
					'hasMany' => array(
						'name' => array(
							'type' => 'text',
							'description' => 'Not used anywhere.  Just a way to ID this state'
						),
						'fill' => array(
							'type' => 'text',
							'description' => 'Name, hex code or rgba() definition of the fill colour'
						),
						'width' => 'text',
						'height' => array(
							'type' => 'text',
							'description' => 'For the Width and Height, enter the pixels that the sprite image needs to occupy, including padding.  Think of it as the ViewPort for the sprite.'
						),
						'align' => array(
							'type' => 'select',
							'options' => array(
								'center',
								'top left',
								'top right',
								'bottom right',
								'bottom left',
								'top center',
								'center right',
								'bottom center',
								'center left',
							),
							'description' => 'How the icon will be aligned within the viewport'
						),
						'position' => array(
							'type' => 'select',
							'options' => array(
								'left',
								'right'
							),
							'description' => 'Where the icon is placed within the element'
						),
						'scale' => array(
							'type' => 'text',
							'description' => 'Use this to make the icons bigger or smaller.  Default is 1.  Greater than 1 makes the icons bigger.  Less than one shrinks them.'
						),
						'options' => array(
							'type' => 'checkbox',
							'options' => array(
								'width-varies' => 'Width Varies',
								'height-varies' => 'Height Varies'
							),
							'description' => 'If the height or width of the element receiving this sprite, indicate it here.  For example, a text input has a varying width, and a textarea has both varying width & height'
						),
						'sprite-selector' => array(
							'type' => 'textarea',
							'description' => "This selector will receive a background:url(sprite-$group.svg)"
						),
						'element-selector' => array(
							'type' => 'textarea',
							'description' => "You should include the token \$icon in the appropriate place.  It will get translated to the name of the icon.  Prefix it by . or # depending of whether you're working with a class or an id."
						),
						'icons' => array(
							'type' => 'checkbox',
							'options' => $elements,
							'description' => "Which elements does this state apply to?"
						),
					),
				);
			}
		}

		tbk_toolbox::options_form($module,$options);
	}

	function print_styles(){
		wp_enqueue_style('form-icons',plugins_url('tbk_toolbox/assets/css/svg/form-icons.css'));
	}

}