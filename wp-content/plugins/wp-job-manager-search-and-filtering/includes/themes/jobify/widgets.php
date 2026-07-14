<?php

namespace WPJMSF\Themes\Jobify;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widgets extends \WPJMSF\Themes\Widgets {

	public function should_override( $instance, $that, $args, $theme_widget ){
		$what = isset( $instance['what'] ) ? esc_attr( $instance['what'] ) : 'job';

		if( $this->type->slug === $what && $this->type->output->is_enabled() ){
			return true;
		}

		return false;
	}

	public function should_override_with_output( $instance, $that, $args, $theme_widget ){

		$outputs = $this->get_location_values_from_widget( $theme_widget );

		if( $this->should_override( $instance, $that, $args, $theme_widget ) && $this->type->sections->has_enabled_section_by_outputs( $outputs ) ){
			return true;
		}

		return false;
	}
}
