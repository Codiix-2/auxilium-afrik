<?php

namespace WPML\TM\Upgrade\Commands;

use WPML\TM\ATE\ClonedSites\AliasDomainCheckHandler;
use WPML\TM\ATE\ClonedSites\SecondaryDomains;

class ValidateAliasDomain implements \IWPML_Upgrade_Command {

	const BANNER_CONTEXT_OPTION = 'wpml_cloned_site_banner_context';
	const RETRY_OPTION          = 'wpml_alias_domain_validation_retry';
	const MAX_ATTEMPTS          = 3;
	const MIN_DELAY_SECONDS     = 60;

	/** @var bool */
	private $result = false;

	public function run_admin() {
		$this->result = $this->run();

		return $this->result;
	}

	public function run_ajax() {
		return null;
	}

	public function run_frontend() {
		return null;
	}

	/** @return bool */
	public function get_results() {
		return $this->result;
	}

	private function run() {
		$domains = get_option( SecondaryDomains::OPTION, [] );
		if ( empty( $domains ) ) {
			delete_option( self::RETRY_OPTION );

			return true;
		}

		$originalSiteUrl = get_option( SecondaryDomains::ORIGINAL_SITE_URL, '' );
		if ( empty( $originalSiteUrl ) ) {
			delete_option( self::RETRY_OPTION );

			return true;
		}

		$retryState = get_option( self::RETRY_OPTION, null );

		if ( $this->isTooSoonToRetry( $retryState ) ) {
			return false;
		}

		$token    = $retryState ? $retryState['token'] : wp_generate_password( 32, false );
		$attempts = $retryState ? $retryState['attempts'] : 0;

		if ( $this->probeOriginalDomain( $originalSiteUrl, $token ) ) {
			delete_option( self::RETRY_OPTION );

			if ( $this->isSharedDatabase( $token ) ) {
				return true;
			}

			$this->resetAliasDomain();

			return true;
		}

		$attempts++;

		if ( $attempts >= self::MAX_ATTEMPTS ) {
			delete_option( self::RETRY_OPTION );
			$this->resetAliasDomain();

			return true;
		}

		$this->scheduleRetry( $token, $attempts );

		return false;
	}

	/**
	 * @param array|null $retryState
	 *
	 * @return bool
	 */
	private function isTooSoonToRetry( $retryState ) {
		return $retryState && ( time() - $retryState['last_attempt'] ) < self::MIN_DELAY_SECONDS;
	}

	/**
	 * @param string $originalSiteUrl
	 * @param string $token
	 *
	 * @return bool
	 */
	private function probeOriginalDomain( $originalSiteUrl, $token ) {
		$url = add_query_arg(
			AliasDomainCheckHandler::GET_PARAM,
			$token,
			trailingslashit( $originalSiteUrl )
		);

		$response = wp_remote_get( $url, [
			'timeout'   => 10,
			'sslverify' => false,
		] );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		return $code === 200
			&& strpos( $body, AliasDomainCheckHandler::RESPONSE_BODY ) !== false;
	}

	/**
	 * @param string $expectedToken
	 *
	 * @return bool
	 */
	private function isSharedDatabase( $expectedToken ) {
		return AliasDomainCheckHandler::getAndDeleteToken() === $expectedToken;
	}

	/**
	 * @param string $token
	 * @param int    $attempts
	 */
	private function scheduleRetry( $token, $attempts ) {
		update_option( self::RETRY_OPTION, [
			'token'        => $token,
			'attempts'     => $attempts,
			'last_attempt' => time(),
		], 'no' );
	}

	private function resetAliasDomain() {
		$secondaryDomains = new SecondaryDomains();
		$secondaryDomains->reset();

		update_option( self::BANNER_CONTEXT_OPTION, 'alias_domain_reset', 'no' );
	}
}
