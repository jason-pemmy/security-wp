<?php

class TBK_Post_Type extends Base_Factory {  //TODO rename to custom post type (singular)

	static $post_type = '';

	public function __construct() {

	}
}

TBK_Post_Type::instantiate();
