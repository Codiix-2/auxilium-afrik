<?php
/**
 * Onboarding Importer Popup: Download images form
 *
 * This template can be overridden by copying it to cariera-child/cariera_core/backend/importer/popup-download-images-form.php.
 *
 * @package     cariera
 * @category    Template
 * @since       1.7.3
 * @version     1.7.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form action="#" method="POST" id="download-import-data-form">
	<h4 class="popup-title"><?php esc_html_e( 'Download import data package', 'cariera-core' ); ?></h4>
	<p class="cariera-error-text"></p>

	<div class="svg-wrapper">
		<svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 653.82 322.52"><defs><style>.cls-1{fill:#3f3d56}.cls-2{fill:#6c63ff}.cls-3{fill:#f2f2f2}.cls-4{fill:#fff}.cls-5{fill:#9f616a}.cls-6{fill:#2f2e41}.cls-7{fill:#cbcbcb}</style></defs><path class="cls-1" d="M618.53 185.75H413.78v-1.07a3.15 3.15 0 0 0-3.15-3.15h-86.55a3.15 3.15 0 0 0-3.15 3.15v1.07h-205.6a13.85 13.85 0 0 0-13.85 13.85V480a13.85 13.85 0 0 0 13.85 13.85h503.2A13.85 13.85 0 0 0 632.38 480V199.6a13.85 13.85 0 0 0-13.85-13.85Z" transform="translate(-45.74 -181.53)"/><path class="cls-2" d="M126.34 215.23a1.29 1.29 0 0 0-1.29 1.29v266a1.29 1.29 0 0 0 1.29 1.29h481.18a1.29 1.29 0 0 0 1.29-1.29v-266a1.29 1.29 0 0 0-1.29-1.29Z" transform="translate(-45.74 -181.53)"/><circle class="cls-2" cx="320.77" cy="16.04" r="5.06"/><path class="cls-1" d="M641.6 474.93h-48.18v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.31-.49-.69-.49H548c-.38 0-.69.22-.69.49v5H537v-5c0-.27-.31-.49-.69-.49h-16.55c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.32v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.32v-5c0-.27-.31-.49-.69-.49H294c-.38 0-.69.22-.69.49v5H283v-5c0-.27-.31-.49-.69-.49h-16.57c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5H226.5v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.32v-5c0-.27-.31-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5h-10.33v-5c0-.27-.3-.49-.69-.49h-16.52c-.38 0-.69.22-.69.49v5H92.26c-9.13 0-16.52 5.32-16.52 11.88v5.37c0 6.56 7.39 11.87 16.52 11.87H641.6c9.13 0 16.53-5.31 16.53-11.87v-5.37c0-6.56-7.4-11.88-16.53-11.88Z" transform="translate(-45.74 -181.53)"/><path class="cls-3" d="M310.91 327.11H168.69a1.12 1.12 0 0 1 0-2.24h142.22a1.12 1.12 0 0 1 0 2.24ZM310.91 351.75H168.69a1.12 1.12 0 1 1 0-2.24h142.22a1.12 1.12 0 0 1 0 2.24ZM310.91 376.39H168.69a1.12 1.12 0 1 1 0-2.24h142.22a1.12 1.12 0 0 1 0 2.24Z" transform="translate(-45.74 -181.53)"/><rect class="cls-4" x="327.33" y="81.19" width="182.54" height="175.82" rx="3.36"/><path class="cls-2" d="M409.61 355.77a45 45 0 0 1 88.31-9.49h1.64a17.86 17.86 0 0 1 5.51.94C527.46 354.53 521.56 388 498 388h-57.91a30.44 30.44 0 0 1-30.53-30.35v-1.17Z" transform="translate(-45.74 -181.53)"/><path class="cls-4" d="M416.66 173.54v-18.42h-9.92v18.42h-7.92l6.44 11.15 6.44 11.16 6.44-11.16 6.44-11.15h-7.92z"/><path class="cls-1" d="M158.41 331.64h-1.24v-34a19.7 19.7 0 0 0-19.69-19.69H65.42a19.69 19.69 0 0 0-19.68 19.69v186.58a19.69 19.69 0 0 0 19.68 19.69h72.06a19.7 19.7 0 0 0 19.69-19.69V355.85h1.24Z" transform="translate(-45.74 -181.53)"/><path class="cls-4" d="M138.27 283.07h-9.4a7 7 0 0 1-6.47 9.62H81.12a7 7 0 0 1-6.47-9.62h-8.78a14.7 14.7 0 0 0-14.7 14.7v186.31a14.7 14.7 0 0 0 14.7 14.7h72.4a14.71 14.71 0 0 0 14.73-14.7V297.77a14.71 14.71 0 0 0-14.71-14.7Z" transform="translate(-45.74 -181.53)"/><circle id="b5dcbba4-f290-467d-a612-e64d8f6895fb" class="cls-2" cx="56.34" cy="205.34" r="35.21"/><path class="cls-4" d="m112.3 388.75-8.75 9a2 2 0 0 1-1.46.59 2 2 0 0 1-1.47-.59l-8.75-9-.09-.09a1.93 1.93 0 0 1 .09-2.73 2.15 2.15 0 0 1 3 0l5.19 5.42v-14a2.09 2.09 0 0 1 4.18 0v14l5.19-5.42a2.15 2.15 0 0 1 3 0l.09.09a1.93 1.93 0 0 1-.22 2.73Z" transform="translate(-45.74 -181.53)"/><path class="cls-5" d="M584.98 316.75h-4.82l-2.28-18.56h7.1v18.56z"/><path class="cls-6" d="M631.94 503h-15.52v-.2a6 6 0 0 1 6-6h9.48Z" transform="translate(-45.74 -181.53)"/><path class="cls-5" d="m548.41 316.6-4.65-1.25 2.58-18.53 6.87 1.84-4.8 17.94z"/><path class="cls-6" d="m594.13 503-15-4v-.19a6 6 0 0 1 7.4-4.27l9.16 2.45Z" transform="translate(-45.74 -181.53)"/><path class="cls-6" d="m587.75 212.7 4.03 4.68-5.21 95.2h-11.14l-5.62-73.25-17.41 75.21-11.58-2.84 10.53-95.53 36.4-3.47z"/><path class="cls-7" d="m599.78 332.77 11.09-5.56 17 .3L642.6 335l-8.35 41.71 3.61 21.74a89.1 89.1 0 0 1-42.54.35h-.11s8.29-29.41 4.76-38.39Z" transform="translate(-45.74 -181.53)"/><path class="cls-5" d="M631.86 310.55a12.57 12.57 0 1 0 0 .09Z" transform="translate(-45.74 -181.53)"/><path class="cls-6" d="M613.67 303.37a3.58 3.58 0 0 1 2.4-1c1.34 0 3.36.43 4.21 1.52a5.09 5.09 0 0 1 .72 3.2v3.08a5.87 5.87 0 0 0 .43 2.67 2 2 0 0 0 2.23 1.15c1-.33 1.48-1.7 2.53-1.94a1.85 1.85 0 0 1 1.95 1.12 4.89 4.89 0 0 1 .28 2.39c-.1 1.62-2.41 2.25-2.74 3.84-.19.91-.85 2.73 0 2.35 3.93-.39 5.65-2.69 7.45-4.84l4.05-4.87a4.78 4.78 0 0 0 1-1.69 4.38 4.38 0 0 0 .11-1.35c0-1.21-.05-2.42-.1-3.63 0-1.06-.3-2.39-1.34-2.61-.53-.11-1.25.09-1.53-.38a1.08 1.08 0 0 1 0-.75 7.3 7.3 0 0 0 .22-3.64c-.6-1.75-2.67-2.47-4.49-2.8s-3.87-.62-5-2.08a16.87 16.87 0 0 0-1-1.4 4 4 0 0 0-1.87-.94 10.37 10.37 0 0 0-6.79.6 5.88 5.88 0 0 1-2.73.89c-1-.07-1.87-.77-2.86-1-1.6-.27-3.14.93-3.93 2.35-1 1.76-1.07 4.18.32 5.64.69.74 1.72 1.24 2 2.2.12.4.09.82.2 1.21a2.18 2.18 0 0 0 1.76 1.53 3.11 3.11 0 0 0 2.52-.82Z" transform="translate(-45.74 -181.53)"/><path class="cls-5" d="M598.25 363.18a4.49 4.49 0 0 0 5.82 2.56 3.61 3.61 0 0 0 .71-.35l22.52 11.9.73-5.48-21.88-12.9a4.52 4.52 0 0 0-7.9 4.27Z" transform="translate(-45.74 -181.53)"/><path class="cls-5" d="M628.26 364.13a4.51 4.51 0 0 1-6.25 1.22 3.67 3.67 0 0 1-.62-.5l-24.58 6.69.22-7 24.45-6.35a4.53 4.53 0 0 1 6.78 5.9Z" transform="translate(-45.74 -181.53)"/><path class="cls-7" d="m637.81 335.32 4.79-.3s5.61 7.41 2.5 15.44c0 0 .54 28.86-11.89 27.67S616.86 377 616.86 377l3.73-10.41 8.34-2.57s-2.57-11.35 2.3-16.07ZM603.2 335.07l-.67-3.47s-10-.24-12 14.9c0 0-9 22.65-.18 25.57s18.49 0 18.49 0l-.73-10-9.68-2s5-6.47 2.28-12.09ZM699.17 504h-149.6a.39.39 0 0 1 0-.78h149.6a.39.39 0 1 1 0 .78Z" transform="translate(-45.74 -181.53)"/></svg>
	</div>

	<div class="importer-progress-bar">
		<span class="progress-bar-text"><?php esc_html_e( 'Initializing', 'cariera-core' ); ?></span>
		<div class="progress-bar-wrapper">
			<div class="progress-bar-inner">&nbsp;</div>
		</div>
	</div>

	<div class="popup-footer">
		<input type="hidden" name="import_data_url" id="import_data_url" value="<?php echo esc_attr( $import_data_url ); ?>">
		<input type="hidden" name="demo_slug" id="demo_slug" value="<?php echo esc_attr( $demo_slug ); ?>">
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'download_import_data' ) ); ?>">
		
		<div class="buttons">
			<i class="popup-note"><?php esc_html_e( 'Please do not close this window until the process is completed', 'cariera-core' ); ?></i>
			<a href="#" class="close-button"><?php esc_html_e( 'Close', 'cariera-core' ); ?></a>
		</div>
	</div>
</form>
