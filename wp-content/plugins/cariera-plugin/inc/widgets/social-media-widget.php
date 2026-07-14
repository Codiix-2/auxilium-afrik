<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Social_Links_Widget extends WP_Widget {

	/**
	 * Default varierable
	 *
	 * @var array
	 */
	protected $default;

	/**
	 * All Social Medias
	 *
	 * @var array
	 */
	protected $socials;

	/**
	 * Constructor
	 */
	public function __construct() {

		$this->socials = [
			'facebook'  => esc_html__( 'Facebook', 'cariera-core' ),
			'twitter'   => esc_html__( 'Twitter', 'cariera-core' ),
			'twitter-x' => esc_html__( 'X (Twitter)', 'cariera-core' ),
			'youtube'   => esc_html__( 'Youtube', 'cariera-core' ),
			'tumblr'    => esc_html__( 'Tumblr', 'cariera-core' ),
			'linkedin'  => esc_html__( 'Linkedin', 'cariera-core' ),
			'pinterest' => esc_html__( 'Pinterest', 'cariera-core' ),
			'flickr'    => esc_html__( 'Flickr', 'cariera-core' ),
			'instagram' => esc_html__( 'Instagram', 'cariera-core' ),
			'dribbble'  => esc_html__( 'Dribbble', 'cariera-core' ),
		];
		$this->default = [
			'title' => '',
		];
		foreach ( $this->socials as $k => $v ) {
			$this->default[ "{$k}_title" ] = $v;
			$this->default[ "{$k}_url" ]   = '';
		}

		$widget_options = [
			'classname'   => 'cariera-social-media',
			'description' => esc_html__( 'This widget displays social media icons.', 'cariera-core' ),
		];

		parent::__construct( 'cariera-social-media', esc_html__( 'Custom: Social Media Widget', 'cariera-core' ), $widget_options );
	}

	/**
	 * Front-End Display of the Widget
	 *
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		cariera_get_template(
			'widgets/social-media.php',
			[
				'instance' => wp_parse_args( $instance, $this->default ),
				'args'     => $args,
				'id_base'  => $this->id_base,
				'socials'  => $this->socials,
			]
		);
	}

	/**
	 * Back-End display of the Widget
	 *
	 * @param array $instance
	 * @return void
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, $this->default ); ?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'cariera-core' ); ?></label>
			<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>"/>
		</p>
		<?php
		foreach ( $this->socials as $social => $label ) {
			printf(
				'<div class="mr-recent-box">
					<label>%s</label>
					<p><input type="text" class="widefat" name="%s" placeholder="%s" value="%s"></p>
				</div>',
				esc_html( $label ),
				$this->get_field_name( $social . '_url' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html__( 'URL', 'cariera-core' ),
				$instance[ $social . '_url' ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			);
		}
	}
}

register_widget( 'Cariera_Social_Links_Widget' );
