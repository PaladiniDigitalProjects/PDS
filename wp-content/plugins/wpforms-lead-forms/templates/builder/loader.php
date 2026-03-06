<?php
/**
 * Lead Forms loader template.
 *
 * @since 1.0.0
 *
 * @var string $title       Message title.
 * @var string $description Message body.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="wpforms-builder-lead-forms-overlay">
	<div class="wpforms-builder-overlay-content">
		<i class="spinner"></i>
		<i class="avatar"></i>
	</div>

	<div class="wpforms-lead-forms-description">
		<div class="title">
			<?php echo esc_html( $title ); ?>
		</div>

		<p>
			<?php echo esc_html( $description ); ?>
		</p>
	</div>

	<div class="wpforms-lead-forms-progress-wrap">
		<div class="wpforms-lead-forms-page-progress" style="width: 0;"></div>
	</div>

	<div class="wpforms-lead-forms-progress-indicator-steps">
		<?php
		printf( /* translators: %1$s - a number of already converted fields, %2$s - a number of total fields to convert. */
			esc_html__( 'Converting field %1$s of %2$s', 'wpforms-lead-forms' ),
			'<strong class="wpforms-lead-forms-progress-indicator-steps-current">0</strong>',
			'<strong class="wpforms-lead-forms-progress-indicator-steps-all">{{ data.total }}</strong>'
		);
		?>
	</div>
</div>
