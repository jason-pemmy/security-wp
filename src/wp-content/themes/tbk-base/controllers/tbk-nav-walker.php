<?php
class TBK_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item->classes[] = 'menu-'.sanitize_title($item->title);
		parent::start_el($output, $item, $depth, $args, $id);
	}
}