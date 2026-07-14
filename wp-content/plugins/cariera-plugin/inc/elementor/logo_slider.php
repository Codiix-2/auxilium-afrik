<?php
/**
 * ELEMENTOR WIDGET - LOGO SLIDER
 *
 * @since   1.4.5
 * @version 1.8.9
 **/

namespace Cariera_Core\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cariera_Logo_Slider extends \Elementor\Widget_Base {

	/**
	 * Get widget's name.
	 */
	public function get_name() {
		return 'logo_slider';
	}

	/**
	 * Get widget's title.
	 */
	public function get_title() {
		return esc_html__( 'Logo Slider', 'cariera-core' );
	}

	/**
	 * Get widget's icon.
	 */
	public function get_icon() {
		return 'eicon-carousel';
	}

	/**
	 * Get widget's categories.
	 */
	public function get_categories() {
		return [ 'cariera-elements' ];
	}

	/**
	 * Register the controls for the widget
	 */
	protected function register_controls() {

		// SECTION.
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Content', 'cariera-core' ),
			]
		);

		// CONTROLS.
		$this->add_control(
			'logo_items',
			[
				'label'  => esc_html__( 'Logo Item', 'cariera-core' ),
				'type'   => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name'    => 'logo',
						'label'   => esc_html__( 'Logo', 'cariera-core' ),
						'type'    => \Elementor\Controls_Manager::MEDIA,
						'default' => [
							'url' => \Elementor\Utils::get_placeholder_image_src(),
						],
					],
					[
						'name'          => 'url',
						'label'         => esc_html__( 'URL', 'cariera-core' ),
						'type'          => \Elementor\Controls_Manager::URL,
						'show_external' => true,
					],
				],
			]
		);
		$this->add_control(
			'autoplay',
			[
				'label'        => esc_html__( 'Autoplay', 'cariera-core' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Enable', 'cariera-core' ),
				'label_off'    => esc_html__( 'Disable', 'cariera-core' ),
				'return_value' => 'enable',
				'default'      => '',
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Widget output
	 */
	protected function render() {
		$settings = $this->get_settings();

		if ( 'enable' === $settings['autoplay'] ) {
			$autoplay = '1';
		} else {
			$autoplay = '0';
		} ?>


		<div class="logo-carousel" data-autoplay="<?php echo esc_attr( $autoplay ); ?>">
			<?php foreach ( (array) $settings['logo_items'] as $item ) { ?>

				<div class="logo-holder">
					<?php
					if ( ! empty( $item['url']['url'] ) ) {
						echo '<a href="' . esc_url( $item['url']['url'] ) . '" target="_blank">';
					}
					?>

					<img src="<?php echo esc_url( $item['logo']['url'] ); ?>" alt="<?php esc_attr_e( 'Logo image', 'cariera-core' ); ?>">

					<?php
					if ( ! empty( $item['url']['url'] ) ) {
						echo '</a>';
					}
					?>
				</div>

			<?php } ?>
		</div>

		<?php
	}
}
