<?php

namespace WPML\TM\ATE\ClonedSites;

use WPML\TM\Jobs\JobLog;

class MigrationLogger {

	public static function begin( $strategy ) {
		JobLog::maybeInitRequest();
		JobLog::createNewGroup(
			JobLog::GROUP_ID_SITE_MIGRATION,
			'Site migration',
			[ 'strategy' => $strategy ]
		);
	}

	public static function end() {
		JobLog::finishCurrentGroup();
	}

	public static function copyRequestSent( $url ) {
		JobLog::add( 'copy_request_sent', [ 'url' => $url ] );
	}

	public static function copyResponse( $response ) {
		self::logHttpResponse( 'copy_request', $response );
	}

	public static function copyResponseInvalid( $response ) {
		JobLog::addError( 'copy_response_invalid', self::describeResponse( $response ) );
	}

	public static function confirmSent( $newWebsiteUuid ) {
		JobLog::add( 'confirm_request_sent', [ 'new_website_uuid' => $newWebsiteUuid ] );
	}

	public static function confirmResponse( $confirmed ) {
		JobLog::add( 'confirm_request_response', [ 'confirmed' => $confirmed ] );
	}

	public static function confirmRequestFailed( $response ) {
		JobLog::addError( 'confirm_request_failed', self::describeResponse( $response ) );
	}

	public static function confirmFailed() {
		JobLog::addError( 'confirm_failed', [] );
	}

	public static function credentialsStored( $success ) {
		JobLog::add( 'credentials_stored', [ 'success' => $success ] );
	}

	public static function jobsCancelled( $count ) {
		JobLog::add( 'jobs_cancelled', [ 'count' => $count ] );
	}

	public static function siteUnlocked() {
		JobLog::add( 'site_unlocked', [] );
	}

	public static function migrationFailed( $strategy ) {
		JobLog::addError( 'migration_failed', [ 'strategy' => $strategy ] );
	}

	private static function logHttpResponse( $prefix, $response ) {
		if ( is_wp_error( $response ) ) {
			JobLog::addError( $prefix . '_failed', [ 'error' => $response->get_error_message() ] );
		} else {
			JobLog::add( $prefix . '_response', [ 'code' => $response['response']['code'] ?? null ] );
		}
	}

	private static function describeResponse( $response ) {
		return [
			'is_wp_error' => is_wp_error( $response ),
			'error'       => is_wp_error( $response ) ? $response->get_error_message() : null,
			'code'        => is_array( $response ) ? ( $response['response']['code'] ?? null ) : null,
		];
	}
}
