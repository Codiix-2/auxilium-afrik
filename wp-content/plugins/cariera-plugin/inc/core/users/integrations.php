<?php

namespace Cariera_Core\Core\Users;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Integrations {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Social Login Support.
		add_action( 'cariera_social_login', [ $this, 'social_login_support' ] );
		add_filter( 'wsl_render_auth_widget_alter_provider_icon_markup', [ $this, 'wsl_custom_markup' ], 10, 3 );
	}

	/**
	 * Social login support for third party plugins
	 *
	 * @since   1.4.8
	 * @version 1.7.2
	 */
	public function social_login_support() {
		cariera_get_template_part( 'account/social-login' );
	}

	/**
	 * Customizing the markup for the WSL plugin
	 *
	 * @since   1.4.8
	 * @version 1.8.0
	 *
	 * @param int    $provider_id
	 * @param string $provider_name
	 * @param string $authenticate_url
	 */
	public function wsl_custom_markup( $provider_id, $provider_name, $authenticate_url ) {
		?>
		<a href="<?php echo esc_url( $authenticate_url ); ?>" rel="nofollow" data-provider="<?php echo esc_attr( $provider_id ); ?>" class="wp-social-login-provider wp-social-login-provider-<?php echo esc_attr( strtolower( $provider_id ) ); ?>">
			<span><i class="lab la-<?php echo esc_attr( strtolower( $provider_id ) ); ?>"></i><?php echo esc_html( $provider_name ); ?></span>
		</a>
		<?php
	}
}
