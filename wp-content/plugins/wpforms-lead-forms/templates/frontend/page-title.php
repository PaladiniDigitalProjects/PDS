<?php
/**
 * Page break title template.
 *
 * @since 1.0.0
 *
 * @var string $title Page break title.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-page-break-title">
	<?php echo esc_html( $title ); ?>
</div>
