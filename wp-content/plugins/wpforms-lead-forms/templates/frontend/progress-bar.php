<?php
/**
 * Progress bar template.
 *
 * @since 1.0.0
 *
 * @var int $current Current value.
 * @var int $total   Total value.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$width = round( 1 / $total * 100, 2 );

?>
<div class="wpforms-lead-forms-progress">
	<div role='progressbar'
		 class="wpforms-lead-forms-progress-bar"
		 style="width: <?php echo (float) $width; ?>%;"
		 aria-valuenow="<?php echo absint( $current ); ?>"
		 aria-valuemin="1"
		 aria-valuemax="<?php echo absint( $total ); ?>"
		 ></div>
</div>
