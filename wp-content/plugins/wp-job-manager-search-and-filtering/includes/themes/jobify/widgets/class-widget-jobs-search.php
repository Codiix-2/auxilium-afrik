<?php

namespace WPJMSF\Themes\Jobify;

class Jobify_Widget_Jobs_Search {

	public $widget_id = 'jobify_widget_jobs_search';
	/**
	 * @var \WPJMSF\Themes\Jobify\Job
	 */
	public $theme;

	/**
	 * Jobify_Widget_Jobs_Search constructor.
	 *
	 * @param $theme \WPJMSF\Themes\Jobify
	 */
	public function __construct( $theme ) {
		$this->theme = $theme;
	}

	function widget( $args, $instance ) {

		ob_start();

		extract( $args );

		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->widget_id );

		echo $before_widget;
		?>

		<div class="container">

			<?php if ( $title ) {
				echo $before_title . $title . $after_title;
			} ?>

			<div class="row">
				<?php $this->theme->type->output->output_sections_by_location( 'jobify_widget_jobs_search' ); ?>
			</div>

		</div>

		<?php
		echo $after_widget;

		$content = ob_get_clean();
		echo $content;
	}
}
