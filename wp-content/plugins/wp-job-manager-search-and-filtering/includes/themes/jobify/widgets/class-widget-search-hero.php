<?php

namespace WPJMSF\Themes\Jobify;

/**
 * Home: Search Hero
 *
 * @package Jobify
 * @category Widget
 * @since 3.0.0
 */
class Jobify_Widget_Search_Hero {

	public $widget_id = 'jobify_widget_search_hero';
	/**
	 * @var \WPJMSF\Themes\Jobify
	 */
	public $theme;

	/**
	 * Jobify_Widget_Search_Hero constructor.
	 *
	 * @param $theme \WPJMSF\Themes\Jobify
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
	}

	function widget( $args, $instance ) {
		extract( $args );

		wp_enqueue_script( 'select2' );
		wp_enqueue_style( 'select2' );

		$text_align = isset( $instance['text_align'] ) ? esc_attr( $instance['text_align'] ) : 'left';
		$background_position = isset( $instance['background_position'] ) ? esc_attr( $instance['background_position'] ) : 'center center';
		$overlay = isset( $instance['cover_overlay'] ) && 1 == $instance['cover_overlay'] ? 'has-overlay' : 'no-overlay';
		$margin = isset( $instance['margin'] ) && 1 == $instance['margin'] ? true : false;
		$height = isset( $instance['height'] ) ? esc_attr( $instance['height'] ) : 'medium';

		if ( ! $margin ) {
			$before_widget = str_replace( 'widget--home ', 'widget--home widget--home--no-margin ', $before_widget );
		}

		$image = isset( $instance['image'] ) ? esc_url( $instance['image'] ) : null;
		$content = $this->assemble_content( $instance );

		$what = isset( $instance['what'] ) ? esc_attr( $instance['what'] ) : 'job';

		global $is_flat;
		$is_flat = true;

		ob_start();
		?>

		<?php echo $before_widget; ?>

			<div class="hero-search hero-search--<?php echo esc_attr( $overlay ); ?> hero-search--height-<?php echo esc_attr( $height ); ?>" style="background-image:url(<?php echo $image; ?>); background-position: <?php echo $background_position; ?>">

			<div class="container">
				<?php echo $content; ?>
				<?php get_job_manager_template( 'job-filters-flat.php' ); ?>
			</div>

		</div>

		<?php
		echo $after_widget;
		$content = ob_get_clean();

		echo apply_filters( $this->widget_id, $content );
	}

	private function assemble_content( $instance ) {
		$text_color = isset( $instance['text_color'] ) ? esc_attr( $instance['text_color'] ) : '#fff';

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$content = isset( $instance['description'] ) ? $instance['description'] : '';

		$output  = '<div class="hero-search__content" style="color:' . $text_color . '">';
		$output .= '<h2 class="hero-search__title" style="color:' . $text_color . '">' . $title . '</h2>';
		$output .= wpautop( $content );
		$output .= '</div>';

		return $output;
	}
}
