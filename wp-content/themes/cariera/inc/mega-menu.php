<?php

namespace Cariera;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extending Walker_Nav_Menu class for Cariera custom menu
 */
class Mega_Menu extends \Walker_Nav_Menu {

	/**
	 * Save current item so it can be used in start level.
	 *
	 * @var WP_Post
	 */
	private $cur_item;

	/**
	 * Current level of the menu
	 *
	 * @var int
	 */
	private $cur_lvl;

	/**
	 * Mega Menu
	 *
	 * @var boolean
	 */
	protected $megamenu = false;

	/**
	 * Cache for post meta to avoid redundant queries
	 *
	 * @var array
	 */
	private static $meta_cache = [];

	/**
	 * Cache for privacy policy URL (prevents 120+ redundant queries)
	 * Following WordPress 6.8.0 pattern
	 *
	 * @since 6.8.0
	 * @var string
	 */
	private $privacy_policy_url;

	/**
	 * Constructor function.
	 */
	public function __construct() {
		// Frontend.
		if ( ! is_admin() ) {
			$this->privacy_policy_url = get_privacy_policy_url();

			add_filter( 'nav_menu_css_class', [ $this, 'menu_classes' ], 10, 4 );
			add_filter( 'nav_menu_link_attributes', [ $this, 'format_menu_link' ], 10, 4 );
			add_filter( 'walker_nav_menu_start_el', [ $this, 'dashboard_menu_title' ], 10, 4 );
		}

		// Backend.
		add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'custom_menu_fields' ], 10, 3 );
		add_action( 'wp_update_nav_menu_item', [ $this, 'update_menu' ], 100, 3 );
		add_filter( 'wp_edit_nav_menu_walker', [ $this, 'navmenu_role_nmr' ], 999999 );
	}

	/**
	 * Get cached post meta value
	 *
	 * @since   1.9.9
	 * @version 2.0.0
	 *
	 * @param int    $post_id      The post ID.
	 * @param string $key          The meta key.
	 * @param mixed  $default_value Default value if meta doesn't exist.
	 * @return mixed
	 */
	private function get_cached_meta( $post_id, $key, $default_value = '' ) {
		$cache_key = $post_id . '_' . $key;

		if ( ! isset( self::$meta_cache[ $cache_key ] ) ) {
			self::$meta_cache[ $cache_key ] = get_post_meta( $post_id, $key, true );
		}

		$value = self::$meta_cache[ $cache_key ];

		return ( '' === $value ) ? $default_value : $value;
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 *
	 * @since 3.0.0
	 * @since 4.8.0 Added nav_menu_submenu_css_class filter
	 * @since 6.3.0 Added nav_menu_submenu_attributes filter
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

		// Get megamenu width for styling.
		$megamenu_width = $this->get_cached_meta( $this->cur_item->ID, '_menu-item-megamenuwidth' );
		$style_attr     = '';

		if ( $megamenu_width ) {
			$style_attr = 'style="width:' . esc_attr( $megamenu_width ) . '"';
		}

		if ( ! $this->megamenu ) {
			// Standard dropdown menu - apply WordPress filters.
			$submenu = ( $depth > 0 ) ? 'sub-menu' : '';

			// Build default classes.
			$classes = [ 'dropdown-menu' ];
			if ( $submenu ) {
				$classes[] = $submenu;
			}
			$classes[] = 'depth_' . $depth;

			/**
			 * Filters the CSS class(es) applied to a menu list element.
			 *
			 * @since 4.8.0
			 *
			 * @param string[] $classes Array of the CSS classes that are applied to the menu `<ul>` element.
			 * @param stdClass $args    An object of `wp_nav_menu()` arguments.
			 * @param int      $depth   Depth of menu item. Used for padding.
			 */
			$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

			$atts          = [];
			$atts['class'] = ! empty( $class_names ) ? $class_names : '';

			/**
			 * Filters the HTML attributes applied to a menu list element.
			 *
			 * @since 6.3.0
			 *
			 * @param array $atts {
			 *     The HTML attributes applied to the `<ul>` element, empty strings are ignored.
			 *
			 *     @type string $class HTML CSS class attribute.
			 * }
			 * @param stdClass $args An object of `wp_nav_menu()` arguments.
			 * @param int      $depth Depth of menu item. Used for padding.
			 */
			$atts = apply_filters( 'nav_menu_submenu_attributes', $atts, $args, $depth );

			$attributes = $this->build_atts( $atts );

			$output .= "{$n}{$indent}<ul{$attributes}>{$n}";

		} elseif ( 0 === $depth ) {
			// Mega menu top level.
			$classes = [ 'dropdown-menu' ];

			/**
			 * Filters the CSS class(es) applied to a mega menu list element.
			 *
			 * @since 4.8.0
			 */
			$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

			$atts          = [];
			$atts['class'] = ! empty( $class_names ) ? $class_names : '';

			/**
			 * Filters the HTML attributes applied to a mega menu list element.
			 *
			 * @since 6.3.0
			 */
			$atts = apply_filters( 'nav_menu_submenu_attributes', $atts, $args, $depth );

			$attributes = $this->build_atts( $atts );

			$output .= "{$n}{$indent}<ul{$attributes} $style_attr>{$n}{$indent}<li>{$n}{$indent}<div class=\"mega-menu-inner\">{$n}{$indent}<div class=\"row\">{$n}";
		} elseif ( 1 === $depth ) {
			// Mega menu second level.
			$classes     = [ 'sub-menu', 'check' ];
			$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

			$atts          = [];
			$atts['class'] = ! empty( $class_names ) ? $class_names : '';
			$atts          = apply_filters( 'nav_menu_submenu_attributes', $atts, $args, $depth );
			$attributes    = $this->build_atts( $atts );

			$output .= "{$n}{$indent}<div class=\"mega-menu-submenu\"><ul{$attributes}>{$n}";
		} else {
			// Mega menu deeper levels.
			$classes     = [ 'sub-menu', 'check' ];
			$class_names = implode( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );

			$atts          = [];
			$atts['class'] = ! empty( $class_names ) ? $class_names : '';
			$atts          = apply_filters( 'nav_menu_submenu_attributes', $atts, $args, $depth );
			$attributes    = $this->build_atts( $atts );

			$output .= "{$n}{$indent}<ul{$attributes}>{$n}";
		}
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu().
	 */
	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

		if ( ! $this->megamenu ) {
			$output .= "{$n}{$indent}</ul>{$n}";
		} elseif ( 0 === $depth ) {
			$output .= "{$n}{$indent}</div>{$n}{$indent}</div>{$n}{$indent}</li>{$n}{$indent}</ul>{$n}";
		} elseif ( 1 === $depth ) {
			$output .= "{$n}{$indent}</ul>{$n}{$indent}</div>";
		} else {
			$output .= "{$n}{$indent}</ul>{$n}";
		}
	}

	/**
	 * Starts the element output.
	 *
	 * @since 3.0.0
	 * @since 4.4.0 The {@see 'nav_menu_item_args'} filter was added.
	 * @since 5.9.0 Renamed `$item` to `$data_object` and `$id` to `$current_object_id`
	 *              to match parent class for PHP 8 named parameter support.
	 * @since 6.7.0 Removed redundant title attributes.
	 *
	 * @see Walker::start_el()
	 *
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              An object of wp_nav_menu() arguments.
	 * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		// Restores the more descriptive, specific name for use within this method.
		$menu_item = $data_object;

		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		$classes   = empty( $menu_item->classes ) ? [] : (array) $menu_item->classes;
		$classes[] = 'menu-item-' . $menu_item->ID;

		// Custom: Save current item to private cur_item to use it in start_lvl.
		$this->cur_item = $menu_item;

		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @since 4.4.0
		 *
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param WP_Post  $menu_item Menu item data object.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $menu_item, $depth );

		/**
		 * Filters the CSS classes applied to a menu item's list item element.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string[] $classes   Array of the CSS classes that are applied to the menu item's `<li>` element.
		 * @param WP_Post  $menu_item The current menu item object.
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $menu_item, $args, $depth ) );

		/**
		 * Filters the ID attribute applied to a menu item's list item element.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param string   $menu_item_id The ID attribute applied to the menu item's `<li>` element.
		 * @param WP_Post  $menu_item    The current menu item.
		 * @param stdClass $args         An object of wp_nav_menu() arguments.
		 * @param int      $depth        Depth of menu item. Used for padding.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );

		$li_atts          = [];
		$li_atts['id']    = ! empty( $id ) ? $id : '';
		$li_atts['class'] = ! empty( $class_names ) ? $class_names : '';

		/**
		 * Filters the HTML attributes applied to a menu's list item element.
		 *
		 * @since 6.3.0
		 *
		 * @param array $li_atts {
		 *     The HTML attributes applied to the menu item's `<li>` element, empty strings are ignored.
		 *
		 *     @type string $class        HTML CSS class attribute.
		 *     @type string $id           HTML id attribute.
		 * }
		 * @param WP_Post  $menu_item The current menu item object.
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$li_atts       = apply_filters( 'nav_menu_item_attributes', $li_atts, $menu_item, $args, $depth );
		$li_attributes = $this->build_atts( $li_atts );

		// Custom: Start element.
		$parent     = $this->get_cached_meta( $menu_item->ID, '_menu_item_menu_item_parent' );
		$widthclass = $this->get_cached_meta( $parent, '_menu-item-columns' );

		if ( 1 === $depth && $this->megamenu ) {
			$output .= $indent . '<div id="' . esc_attr( $id ) . '" class="col-md-' . esc_attr( $widthclass ) . '">' . "{$n}";
			$output .= $indent . '<div class="menu-item-mega">';
		} else {
			$output .= $indent . '<li' . $li_attributes . '>';
		}
		// End of custom.

		$atts           = [];
		$atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
		$atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
		if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $menu_item->xfn;
		}
		if ( ! empty( $menu_item->url ) ) {
			// PERFORMANCE FIX: Use cached privacy policy URL instead of calling get_privacy_policy_url() 120+ times.
			if ( $this->privacy_policy_url === $menu_item->url ) {
				$atts['rel'] = empty( $atts['rel'] ) ? 'privacy-policy' : $atts['rel'] . ' privacy-policy';
			}

			$atts['href'] = $menu_item->url;
		} else {
			$atts['href'] = '';
		}

		$atts['aria-current'] = $menu_item->current ? 'page' : '';

		// Custom atts.
		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			$atts['class']         = 'dropdown-toggle';
			$atts['role']          = 'button';
			$atts['data-toggle']   = 'dropdown';
			$atts['aria-haspopup'] = 'true';
			$atts['aria-expanded'] = 'false';
		}

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 *     @type string $title        Title attribute.
		 *     @type string $target       Target attribute.
		 *     @type string $rel          The rel attribute.
		 *     @type string $href         The href attribute.
		 *     @type string $aria-current The aria-current attribute.
		 * }
		 * @param WP_Post  $menu_item The current menu item object.
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $menu_item->title, $menu_item->ID );

		/**
		 * Filters a menu item's title.
		 *
		 * @since 4.4.0
		 *
		 * @param string   $title     The menu item's title.
		 * @param WP_Post  $menu_item The current menu item object.
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 */
		$title = apply_filters( 'nav_menu_item_title', $title, $menu_item, $args, $depth );

		// WordPress 6.7.0: Add title attribute only if it doesn't match the link text (accessibility improvement).
		$the_title_filtered = $title;
		if ( ! empty( $menu_item->attr_title )
			&& trim( strtolower( $menu_item->attr_title ) ) !== trim( strtolower( $menu_item->title ) )
			&& trim( strtolower( $menu_item->attr_title ) ) !== trim( strtolower( $the_title_filtered ) )
			&& trim( strtolower( $menu_item->attr_title ) ) !== trim( strtolower( $title ) ) ) {
			$atts['title'] = $menu_item->attr_title;
		} else {
			$atts['title'] = '';
		}

		$attributes = $this->build_atts( $atts );

		// Custom: Assign badges to the menu item if selected.
		$badge = $this->get_cached_meta( $menu_item->ID, '_menu-item-badge' );

		if ( 'no-badge' === $badge ) {
			$badge = '';
		} elseif ( 'new-badge' === $badge ) {
			$badge = '<span class="items-badge"><span class="new-badge">' . esc_html__( 'New', 'cariera' ) . '</span></span>';
		} elseif ( 'hot-badge' === $badge ) {
			$badge = '<span class="items-badge"><span class="hot-badge">' . esc_html__( 'Hot', 'cariera' ) . '</span></span>';
		} elseif ( 'trending-badge' === $badge ) {
			$badge = '<span class="items-badge"><span class="trending-badge">' . esc_html__( 'Trending', 'cariera' ) . '</span></span>';
		} else {
			$badge = '';
		}

		// Custom: Menu Icons.
		$icons = $this->get_cached_meta( $menu_item->ID, '_menu-item-icons' );
		$icon  = $this->get_cached_meta( $menu_item->ID, '_menu-item-icon' );

		if ( $icons ) {
			$icon = '<i class="' . esc_attr( $icon ) . '"></i>';
		} else {
			$icon = '';
		}

		if ( 1 === $depth && $this->megamenu ) {
			$item_output = '<a ' . $attributes . '>' . $icon . '<span>' . $title . '</span>' . $badge . '</a>';
		} else {
			$item_output  = $args->before;
			$item_output .= '<a' . $attributes . '>';
			$item_output .= $args->link_before . $icon . '<span>' . $title . '</span>' . $badge . $args->link_after;
			$item_output .= '</a>';
			$item_output .= $args->after;
		}

		/**
		 * Filters a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @param string   $item_output The menu item's starting HTML output.
		 * @param WP_Post  $menu_item   Menu item data object.
		 * @param int      $depth       Depth of menu item. Used for padding.
		 * @param stdClass $args        An object of wp_nav_menu() arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @since 3.0.0
	 * @since 5.9.0 Renamed `$item` to `$data_object` to match parent class for PHP 8 named parameter support.
	 *
	 * @see Walker::end_el()
	 *
	 * @param string   $output      Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object Menu item data object. Not used.
	 * @param int      $depth       Depth of page. Not Used.
	 * @param stdClass $args        An object of wp_nav_menu() arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		if ( 1 === $depth && $this->megamenu ) {
			$output .= "</div>{$n}";
			$output .= "</div>{$n}";
		} else {
			$output .= "</li>{$n}";
		}
	}

	/**
	 * Add classes to the menu items in the front-end.
	 *
	 * @since   1.7.5
	 * @version 2.0.0
	 *
	 * @param array    $classes   An array of the CSS classes that are applied to the menu item's `<li>` element.
	 * @param WP_Post  $menu_item The current menu item object.
	 * @param stdClass $args      An object of wp_nav_menu() arguments.
	 * @param int      $depth     Depth of menu item. Used for padding.
	 */
	public function menu_classes( $classes, $menu_item, $args, $depth ) {
		// $megamenu     = '';
		$classes[]    = 'parentid_' . $this->get_cached_meta( $menu_item->ID, '_menu_item_menu_item_parent' );
		$item_is_mega = apply_filters( 'cariera_menu_item_mega', $this->get_cached_meta( $menu_item->ID, '_menu-item-megamenu' ), $menu_item->ID );

		// Cariera Mega Menu.
		$hidden_status = $this->get_cached_meta( $menu_item->ID, '_menu-item-hiddenonmobile' );

		if ( 'hide' === $hidden_status ) {
			$classes[] = 'hide-on-mobile';
		}

		// Check if this is top level and is mega menu.
		if ( ! $depth ) {
			$this->megamenu = $item_is_mega;
		}

		// Add active class for current menu item.
		$active_classes = [
			'current-menu-item',
			'current-menu-parent',
			'current-menu-ancestor',
		];

		$is_active = array_intersect( $classes, $active_classes );
		if ( ! empty( $is_active ) ) {
			$classes[] = 'active';
		}

		if ( in_array( 'menu-item-has-children', $classes, true ) ) {
			if ( ! $depth ) {
				$classes[] = 'dropdown';
			}
			if ( ! $depth && $this->megamenu ) {
				$classes[] = 'mega-menu';
			}
			if ( $depth && ! $this->megamenu ) {
				$classes[] = 'dropdown-submenu';
			}
		}

		if ( strpos( $menu_item->url, '/__dashboard_title' ) !== false ) {
			$classes[] = 'dashboard-menu-title';
		}

		return $classes;
	}

	/**
	 * Adding custom menu fields to the menu editing backend
	 *
	 * @since   1.5.4
	 * @version 2.0.0
	 *
	 * @param int    $item_id The ID of the menu item.
	 * @param object $item    The menu item object.
	 * @param int    $depth   The depth of the menu item in the menu structure.
	 */
	public function custom_menu_fields( $item_id, $item, $depth ) {
		$icon_checkbox   = '';
		$icons           = get_post_meta( $item_id, '_menu-item-icons', true );
		$icon            = get_post_meta( $item_id, '_menu-item-icon', true );
		$badge           = get_post_meta( $item_id, '_menu-item-badge', true );
		$mobile_checkbox = '';
		$mobilestatus    = get_post_meta( $item_id, '_menu-item-hiddenonmobile', true );

		if ( '' !== $icons ) {
			$icon_checkbox = "checked='checked'";
		}

		if ( 'hide' === $mobilestatus ) {
			$mobile_checkbox = "checked='checked'";
		}
		?>

		<!-- Custom Menu Icon Enabler -->
		<p class="field-menu-columns description description-wide">
			<label for="edit-menu-item-icons-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-icons-<?php echo esc_attr( $item_id ); ?>" value="_blank" name="menu-item-icons[<?php echo esc_attr( $item_id ); ?>]"<?php echo esc_attr( $icon_checkbox ); ?> />
				<?php esc_html_e( 'Enable Icons', 'cariera' ); ?>
			</label>
		</p>

		<!-- Custom Menu Icon Picker -->
		<p class="field-menu-columns description description-wide">
			<label for="edit-menu-item-icon-<?php echo esc_attr( $item_id ); ?>" style="display: block;"><?php esc_html_e( 'Icon', 'cariera' ); ?></label>
			<button class="button load-icons"><?php esc_html_e( 'Select Icons', 'cariera' ); ?></button>
			<select id="edit-menu-item-icon-<?php echo esc_attr( $item_id ); ?>" class="cariera-icon-select widefat edit-menu-item-icon" name="menu-item-icon[<?php echo esc_attr( $item_id ); ?>]" data-selected-icon="<?php echo esc_attr( $icon ); ?>"  style="display: none"></select>
		</p>

		<!-- Mega Menu Elements -->
		<?php
		if ( 0 === $depth ) {
			$mega_checkbox  = '';
			$megamenu       = get_post_meta( $item_id, '_menu-item-megamenu', true );
			$megamenu_width = get_post_meta( $item_id, '_menu-item-megamenuwidth', true );
			$col            = get_post_meta( $item_id, '_menu-item-columns', true );

			if ( '' !== $megamenu ) {
				$mega_checkbox = "checked='checked'";
			}
			?>

			<p class="field-megamenu description description-wide">
				<label for="edit-menu-item-megamenu-<?php echo esc_attr( $item_id ); ?>">
					<input type="checkbox" id="edit-menu-item-megamenu-<?php echo esc_attr( $item_id ); ?>" value="_blank" name="menu-item-megamenu[<?php echo esc_attr( $item_id ); ?>]"<?php echo esc_attr( $mega_checkbox ); ?> />
					<?php esc_html_e( 'Enable megamenu', 'cariera' ); ?>
				</label>
			</p>

			<p class="field-megamenu-width description description-wide">
				<label for="edit-menu-item-megamenuwidth-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Mega Menu Width. For example "55%"', 'cariera' ); ?><br />
					<input type="text" id="edit-menu-item-megamenuwidth-<?php echo esc_attr( $item_id ); ?>" class="widefat code edit-menu-item-megamenuwidth" name="menu-item-megamenuwidth[<?php echo esc_attr( $item_id ); ?>]" value="<?php echo esc_attr( $megamenu_width ); ?>" />
				</label>
			</p>

			<p class="field-menu-columns description description-wide">
				<label for="edit-menu-item-columns-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Number of columns', 'cariera' ); ?>
					<select id="edit-menu-item-columns-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-columns" name="menu-item-columns[<?php echo esc_attr( $item_id ); ?>]">
						<option value="6" 
						<?php
						if ( '6' === $col ) {
							echo 'selected'; }
						?>
						><?php esc_html_e( '2 columns', 'cariera' ); ?></option>
						<option value="4" 
						<?php
						if ( '4' === $col ) {
							echo 'selected'; }
						?>
						><?php esc_html_e( '3 columns', 'cariera' ); ?></option>
						<option value="3" 
						<?php
						if ( '3' === $col ) {
							echo 'selected'; }
						?>
						><?php esc_html_e( '4 columns', 'cariera' ); ?></option>
					</select>
				</label>
			</p>
		<?php } ?>

		<!-- Custom Menu Item Badges -->
		<p class="field-menu-columns description description-wide">
			<label for="edit-menu-item-badge-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Set a badge for your menu item', 'cariera' ); ?>
				<select id="edit-menu-item-badge-<?php echo esc_attr( $item_id ); ?>" class="widefat edit-menu-item-badge" name="menu-item-badge[<?php echo esc_attr( $item_id ); ?>]">
					<option value="no-badge" <?php echo esc_attr( 'no-badge' === $badge ? 'selected' : '' ); ?>><?php esc_html_e( 'No Badge', 'cariera' ); ?></option>
					<option value="new-badge" <?php echo esc_attr( 'new-badge' === $badge ? 'selected' : '' ); ?>><?php esc_html_e( 'New Badge', 'cariera' ); ?></option>
					<option value="hot-badge" <?php echo esc_attr( 'hot-badge' === $badge ? 'selected' : '' ); ?>><?php esc_html_e( 'Hot Badge', 'cariera' ); ?></option>
					<option value="trending-badge" <?php echo esc_attr( 'trending-badge' === $badge ? 'selected' : '' ); ?>><?php esc_html_e( 'Trending Badge', 'cariera' ); ?></option>
				</select>
			</label>
		</p>

		<!-- Hide on mobile -->
		<p class="field-hiddenonmobile description description-wide">
			<label for="edit-menu-item-hiddenonmobile-<?php echo esc_attr( $item_id ); ?>">
				<input type="checkbox" id="edit-menu-item-hiddenonmobile-<?php echo esc_attr( $item_id ); ?>" value="hide" name="menu-item-hiddenonmobile[<?php echo esc_attr( $item_id ); ?>]" <?php echo esc_attr( $mobile_checkbox ); ?> />
				<?php esc_html_e( 'Hide on mobile navigation', 'cariera' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Save and update the custom fields for the menu
	 *
	 * @since   1.3.5
	 * @version 2.0.0
	 *
	 * @param int $menu_id The ID of the menu item being saved.
	 * @param int $menu_item_db The ID of the menu item in the database.
	 */
	public function update_menu( $menu_id, $menu_item_db ) {
		$check = [ 'icons', 'icon', 'megamenu', 'megamenuwidth', 'columns', 'badge', 'hiddenonmobile' ];

		foreach ( $check as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is handled by WordPress core in the save_post action.
			if ( ! isset( $_POST[ 'menu-item-' . $key ][ $menu_item_db ] ) ) {
				$_POST[ 'menu-item-' . $key ][ $menu_item_db ] = '';
			}

			$value = isset( $_POST[ 'menu-item-' . $key ][ $menu_item_db ] ) ? wp_unslash( $_POST[ 'menu-item-' . $key ][ $menu_item_db ] ) : ''; // phpcs:ignore
			update_post_meta( $menu_item_db, '_menu-item-' . $key, $value );
		}

		// Clear the meta cache for this menu item.
		$cache_keys_to_clear = [
			'_menu-item-icons',
			'_menu-item-icon',
			'_menu-item-megamenu',
			'_menu-item-megamenuwidth',
			'_menu-item-columns',
			'_menu-item-badge',
			'_menu-item-hiddenonmobile',
			'_menu_item_menu_item_parent',
		];

		foreach ( $cache_keys_to_clear as $meta_key ) {
			$cache_key = $menu_item_db . '_' . $meta_key;
			if ( isset( self::$meta_cache[ $cache_key ] ) ) {
				unset( self::$meta_cache[ $cache_key ] );
			}
		}
	}

	/**
	 * Replace menu item url for handling demo pages
	 *
	 * @since   1.5.5
	 * @version 2.0.0
	 *
	 * @param array    $atts  The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
	 * @param WP_Post  $item   The current menu item data object.
	 * @param stdClass $args   An object of `wp_nav_menu()` arguments.
	 * @param int      $depth  Depth of menu item. Used for padding.
	 */
	public function format_menu_link( $atts, $item, $args, $depth ) {
		$atts['href'] = str_replace( '/__site_url', get_site_url(), $atts['href'] );
		$atts['href'] = str_replace( '/__dashboard_logout', wp_logout_url( home_url() ), $atts['href'] );

		return $atts;
	}

	/**
	 * Nav Menu Role workaround.
	 *
	 * @since   1.2.3
	 * @version 2.0.0
	 *
	 * @param string $walker The name of the walker class to use. Default is 'Walker_Nav_Menu_Edit'.
	 */
	public function navmenu_role_nmr( $walker ) {
		if ( function_exists( 'Nav_Menu_Roles' ) ) {
			$walker = 'Walker_Nav_Menu_Edit_Roles';
		}

		return $walker;
	}

	/**
	 * Modifies the markup of a menu item if its URL contains a specific placeholder.
	 *
	 * @since 1.8.4
	 *
	 * @param string   $item_output The menu item's starting HTML output (usually an <a> tag).
	 * @param WP_Post  $item        The current menu item data object.
	 * @param int      $depth       The depth of the menu item (zero for top-level, increasing with each submenu).
	 * @param stdClass $args        The arguments passed to `wp_nav_menu()`, such as theme_location and menu class.
	 */
	public function dashboard_menu_title( $item_output, $item, $depth, $args ) {
		// Check if the menu item contains the placeholder '/__dashboard_title' in its URL.
		if ( strpos( $item->url, '/__dashboard_title' ) !== false ) {
			// Replace the default menu item output with a <span> element for menu section titles.
			$item_output = '<span class="dashboard-menu-title">' . esc_html( $item->title ) . '</span>';
		}

		return $item_output;
	}
}
