<?php

namespace WPJMSF;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Widget
 *
 * @package WPJMSF
 *
 * @since   0.1.1
 *
 */
class Widget extends \WP_Widget {

	/**
	 * @var \WPJMSF\Job|\WPJMSF\Resume
	 */
	public $type;

	/**
	 * Widget constructor.
	 *
	 * @param $type \WPJMSF\Job|\WPJMSF\Resume
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_action( 'widgets_init', array( $this, 'register' ) );
		parent::__construct(
			"{$this->type->slug}_search_filtering_widget",
			sprintf( __( '%s Search and Filtering', 'wp-job-manager-search-and-filtering' ), ucfirst( $this->type->slug ) ),
			array( 'description' => sprintf( __( 'Widget to output %s search and filter sections for WP Job Manager and WP Resume Manager', 'wp-job-manager-search-and-filtering' ), ucfirst( $this->type->slug ) ) )
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $config   Saved values from database.
	 *
	 * @see WP_Widget::widget()
	 *
	 */
	public function widget( $args, $config ) {

		if ( empty( $config['section'] ) || ! $this->type->output->is_enabled() ) {
			return;
		}

		$widget_styles = isset( $config['widget_styles'] ) && ! empty( $config['widget_styles'] ) ? true : false;

		if ( $widget_styles ) {
			echo $args['before_widget'];
			if ( ! empty( $config['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $config['title'] ) . $args['after_title'];
			}
		}

		$this->type->output->output_section( $config['section'], false );

		if ( $widget_styles ) {
			echo $args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 *
	 * @see WP_Widget::form()
	 *
	 */
	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'New title', 'wp-job-manager-search-and-filtering' );
		$widget_styles = ( isset( $instance['widget_styles'] ) && ! empty( $instance['widget_styles'] ) ) ? $instance['widget_styles'] : '0';
		$selected_section = isset( $instance['section'] ) ? $instance['section'] : '';

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-job-manager-search-and-filtering' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'widget_styles' ); ?>"><?php _e( 'Use Widget Style:', 'wp-job-manager-search-and-filtering' ); ?></label>
			<input class="checkbox" id="<?php echo $this->get_field_id( 'widget_styles' ); ?>" name="<?php echo $this->get_field_name( 'widget_styles' ); ?>" type="checkbox"
			       value="<?php echo esc_attr( $widget_styles ); ?>" <?php checked( 1, $widget_styles, true ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'section' ); ?>"><?php _e( 'Section', 'wp-job-manager-search-and-filtering' ); ?></label>
			<select class='widefat' id="<?php echo $this->get_field_id( 'section' ); ?>" name="<?php echo $this->get_field_name( 'section' ); ?>" type="text">

				<?php
				$sections = $this->type->sections->get_sections();
				if( empty( $sections ) ){
					echo '<p>' . __( 'Please add a section and it will show up here to select from', 'wp-job-manager-search-and-filtering' ) . '</p>';
				}

				foreach ( $sections as $section ) {
					$selected = ( ( $section['ID'] == $selected_section ) ? 'selected' : '' );
					echo "<option value=\"{$section['ID']}\" {$selected}>";
					echo $section['post_title'] . " (ID: {$section['ID']})";
					echo "</option>";
				}

				?>

			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 * @see WP_Widget::update()
	 *
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['widget_styles'] = isset( $new_instance['widget_styles'] ) ? '1' : '0';
		$instance['section'] = isset( $new_instance['section'] ) ? absint( $new_instance['section'] ) : 0;
		return $instance;
	}

	public function register(){
		register_widget( $this );
	}
}