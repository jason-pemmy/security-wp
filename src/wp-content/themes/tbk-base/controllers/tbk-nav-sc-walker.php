<?php
/*
 * This Nav Walker has the ability to have shortcodes in the title and url area
 */

class TBK_Nav_SC_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$item->classes[] = 'menu-'.sanitize_title($item->title);
		$title_has_sc = strpos($item->title, '[');
		$without_sc = str_replace('[', '', str_replace(']', '', $item->title));
		$item->url = str_replace($without_sc, '['.$without_sc.']', $item->url);
		//apply shortcodes..
		$item->title = do_shortcode($item->title);
		$item->url = do_shortcode($item->url);
		self::nav_start_el($output, $item, $depth, $args, $id, $title_has_sc);
	}

	public function nav_start_el( &$output, $item, $depth = 0, $args = array(), $id = 0, $has_sc = false) {
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();

		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;
		//check to see if url = # and title has shortcode, if so, rework output.
		if($item->url != '#' && $has_sc === false) {
			$item_output .= '<a'. $attributes .'>';
		}
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		if($item->url != '#' && $has_sc === false) {
			$item_output .= '</a>';
		}
		$item_output .= $args->after;
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
