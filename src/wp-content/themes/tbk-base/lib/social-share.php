<?php
/*
Version:     1.0.0
Author:      Jonelle Carroll-Berube | tbk Creative
//https://github.com/prodeveloper/social-share
*/
add_filter('social_share_links', array( 'Share', 'social_share' ));
add_filter('social_share_email_body', array( 'Share', 'social_share_email_body' ), 1, 2);
add_filter('social_share_email_subject', array( 'Share', 'social_share_email_subject' ), 1, 1);
// @codingStandardsIgnoreStart
/* Example:
$social = apply_filters('social_share_links', array(
		'post_id' => 'test',
		'url' => 'http://google.com',
		'image' => '',
));
*/
// @codingStandardsIgnoreEnd
class Share {

	static $post_id;
	static $link;
	static $text;
	static $media;
	static $instance = false;

	public static function social_share() {
		$services = apply_filters('default_social_sharing_services', array(
				'pinterest',
				'email',
				'facebook',
				'twitter',
				'gplus',
		));
		$defaults = array(
				'post_id' => get_the_ID(),
				'description' => get_the_title(),
				'url' => get_permalink(),
				'image' => null,
				'services' => $services,
		);
		$args = func_get_args();
		$args = wp_parse_args(array_pop($args),
				$defaults
		);
		if(empty($args['image'])) {
			$args['image'] = wp_get_attachment_url($args['post_id']);
		}
		$links = Share::load($args['url'], $args['description'], $args['image'], $args['post_id'])->services($args['services']);
		return apply_filters('customize_social_share_links', $links);
	}

	public static function load( $link, $text = '', $media = '', $post_id = null) {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		self::$link = urlencode( $link );
		self::$text = urlencode( $text );
		self::$media = urlencode( $media );
		self::$post_id = $post_id;
		return self::$instance;
	}

	public function services() {
		$services = func_get_args();
		$object = false;
		if( ! empty($services) && is_array($services)) {
			$services = array_pop($services);
			$object = true;
		}
		$return = array();

		if ( $services ) {
			foreach ( $services as $service ) {
				if ( method_exists( 'Share', $service ) ) {
					$return[ $service ] = self::$service();
				}
			}
		}
		if ( $object ) {
			return (object) $return;
		}
		return $return;
	}

	public function delicious() {
		return 'https://delicious.com/post?url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function digg() {
		return 'http://www.digg.com/submit?url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function evernote() {
		return 'http://www.evernote.com/clip.action?url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function facebook() {
		return 'https://www.facebook.com/sharer/sharer.php?u=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' ).
		(self::$media?'&p[images][0]='.self::$media:'');
	}

	public function gmail() {
		return 'https://mail.google.com/mail/?view=cm&fs=1&to&ui=2&tf=1&su=' . self::$link . ( ( self::$text ) ? '&body=' . self::$text : '' );
	}

	public function gplus() {
		return 'https://plus.google.com/share?url=' . self::$link;
	}

	public function linkedin() {
		return 'http://www.linkedin.com/shareArticle?mini=true&url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function pinterest() {
		return 'http://pinterest.com/pin/create/button/?url=' . self::$link . ( ( self::$media ) ? '&media=' . self::$media : '' ) . ( ( self::$text ) ? '&description=' . self::$text : '' );
	}

	public function reddit() {
		return 'http://www.reddit.com/submit?url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function scoopit() {
		return 'http://www.scoop.it/oexchange/share?url=' . self::$link . ( ( self::$text ) ? '&title=' . self::$text : '' );
	}

	public function springpad() {
		return 'https://springpadit.com/s?type=lifemanagr.Bookmark&url=' . self::$link . ( ( self::$text ) ? '&name=' . self::$text : '' );
	}

	public function tumblr() {
		return 'http://www.tumblr.com/share?v=3&u=' . self::$link . ( ( self::$text ) ? '&t=' . self::$text : '' );
	}

	public function twitter() {
		return 'https://twitter.com/intent/tweet?url=' . self::$link . ( ( self::$text ) ? '&text=' . self::$text : '' );
	}

	public function email() {
		$subject = apply_filters('social_share_email_subject', null);
		$body = apply_filters('social_share_email_body', self::$text, self::$link);
		$subject = strip_tags($subject);
		$body = strip_tags(str_replace( '<br/>', '%0D%0A %0D%0A', $body));
		return 'mailto:?subject='.$subject.'&body=' . $body;
	}

	public static function social_share_email_subject($subject = null){
		return get_the_title(self::$post_id);
	}

	public static function social_share_email_body($text, $link){
		$body = $text.'<br/><a href="'.$link.'">'.$link.'</a>';
		return $body;
	}
}