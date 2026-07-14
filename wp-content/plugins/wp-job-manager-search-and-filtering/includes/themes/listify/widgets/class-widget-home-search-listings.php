<?php

namespace WPJMSF\Themes\Listify;

/**
 * Home: Search Listings
 *
 * @since Listify 1.0.0
 */
class Listify_Widget_Search_Listings {

  public $id_base = 'listify_widget_search_listings';

	public $widget_id = 'listify_widget_search_listings';

	/**
	 * @var \WPJMSF\Theme
	 */
	public $theme;

	/**
	 * Constructor
	 *
	 * @param $theme \WPJMSF\Themes\Listify
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
	}

	/**
	 * widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		global $listify_widget_search_listings_instance;
		$listify_widget_search_listings_instance = $instance;

		extract( $args );

		$title       = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$description = isset( $instance['description'] ) ? $instance['description'] : false;

		if ( $description && strpos( $after_title, '</div>' ) ) {
			$after_title = str_replace( '</div>', '', $after_title ) . '<p class="home-widget-description">' . $description . '</p></div>';
		}

		/**
		 * Listify homepage widget has widget_id defined as "search-12391" so if we find that, use that specific output location,
		 * otherwise consider it a normal widget to output.
		 *
		 * This will only be called when WordPress version is >= 5.3
		 *
		 * @see https://core.trac.wordpress.org/ticket/34226
		 */
		$section_output_location = isset( $args['widget_id'] ) && $args['widget_id'] === "search-12391" ? 'listify_widget_search_listings_homepage_hero' : 'listify_widget_search_listings';

		ob_start();

		echo $before_widget; // WPCS: XSS ok.

		if ( $title ) {
			echo $before_title . $title . $after_title; // WPCS: XSS ok.
		}
		do_action( 'job_manager_job_filters_before' );
		?>
		<div class="search-filters-home">
			<form class="job_search_form" action="<?php echo esc_url( listify_get_listings_page_url() ); ?>" method="GET">
<!--				<div class="search_jobs">-->
					<?php $this->theme->type->output->output_sections_by_location( $section_output_location ); ?>
<!--				</div>-->
			</form>
		</div>
		<?php
		echo $after_widget;

		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content ); // WPCS: XSS ok
		$listify_widget_search_listings_instance = null;
	}
}
