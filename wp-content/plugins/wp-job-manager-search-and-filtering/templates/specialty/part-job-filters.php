<?php
if( apply_filters( 'search_and_filtering_specialty_use_part-job-filters', false ) ):
?>
<div class="form-filter">
	<div class="container">
		<div class="row">
			<?php do_action( 'search_and_filtering_specialty_part-job-filters' ); ?>
		</div>
	</div>
</div>
<?php else: ?>
	<?php
		if ( file_exists( TEMPLATEPATH . '/part-job-filters.php' ) ) {
			require_once TEMPLATEPATH . '/part-job-filters.php';
		}
	?>
<?php endif; ?>
