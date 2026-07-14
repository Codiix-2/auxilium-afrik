<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Output the main footer template.
\Cariera\print_footer();

do_action( 'cariera_footer_after' );
?>

<!-- End of Website wrapper -->
</div>

<?php
wp_footer();

do_action( 'cariera_body_end' );
?>

</body>
</html>
