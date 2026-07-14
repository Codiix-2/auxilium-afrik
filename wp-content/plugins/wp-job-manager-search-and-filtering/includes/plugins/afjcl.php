<?php

namespace WPJMSF\Plugins;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AFJCL
 *
 * Astoundify Company Listings
 *
 * Normally initialized on plugins_loaded hook from within the type class
 *
 * @package WPJMSF\Plugins
 */
class AFJCL extends \WPJMSF\Plugin {

	/**
	 * @var string
	 */
	public $plugin_class = 'WP_Job_Manager_Company_Listings';

	/**
	 * AFJCL constructor.
	 *
	 * @param $type
	 */
	public function __construct( $type ) {
		$this->type = $type;
		add_filter( 'search_and_filtering_dont_deregister_default_job_filters_script', array( $this, 'check_deregister_script' ) );
		add_filter( 'company_listings_tabs', array( $this, 'disable_sf' ) );
		add_action( 'search_and_filtering_filters_start_job', array( $this, 'maybe_add_company_id' ) );
	}

	/**
	 * Maybe Add Company ID (inside form)
	 *
	 * This method matches WP_Job_Manager_search_form_group_field() to make sure that the company ID is included in the form,
	 * since we override the default job-filters.php file which adds this through the default job_manager_job_filters_search_jobs_start hook
	 *
	 * @return false|void
	 * @since 1.1.9
	 *
	 */
	public function maybe_add_company_id() {
		global $wpdb, $post;

		if ( ! is_singular( 'company_listings' ) ) {
			return false;
		} ?>
		<input type="hidden" name="job_company_id" value="<?php echo $post->ID; ?>"/>
		<?php
	}

	/**
	 * Remove Specific Hooks (that duplicate functionality)
	 *
	 * @since 1.1.9
	 *
	 */
	public function check_deregister_script( $deregister ) {

		if( is_singular( 'company_listings' ) ){
			return true;
		}

		return $deregister;
	}

	/**
	 * Change Callback for Jobs Tab
	 *
	 * @param $tabs
	 *
	 * @return array
	 * @since 1.1.9
	 *
	 */
	public function disable_sf( $tabs ) {

		if ( isset( $tabs['jobs'] ) ) {
			$tabs['jobs']['callback'] = array( $this, 'company_jobs_tab_no_sf' );
		}

		return $tabs;
	}

	/**
	 * Output Jobs Tab
	 *
	 * @since 1.1.9
	 *
	 */
	public function company_jobs_tab_no_sf() { ?>
		<div class="cmp-posted-jobs">
			<h3 class="container-title"><?php printf( __( 'Jobs at %s', 'afj-company-listings', 'wp-job-manager-search-and-filtering' ), get_the_title() ) ?></h3>
			<?php
				$jobs_shortcode = apply_filters( 'search_and_filtering_afjcl_company_jobs_tab_shortcode', '[jobs show_sf_filters="false"]', $this );
				echo do_shortcode( $jobs_shortcode );
			?>
		</div> <?php
	}
}