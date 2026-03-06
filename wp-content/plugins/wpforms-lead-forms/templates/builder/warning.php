<?php
/**
 * Lead Forms enabled warning template.
 *
 * @since 1.0.0
 *
 * @var int $form_id Form ID.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<section class="wpforms-dyk wpforms-lead-forms-warning">
	<div class="wpforms-dyk-fbox">
		<div class="wpforms-dyk-message">
			<strong><?php echo esc_html__( 'Lead Forms Enabled', 'wpforms-lead-forms' ); ?></strong>
			<br>
			<?php
			printf(
				wp_kses( /* translators: %s is a link to the fields tab. */
					__( 'Before you save, head over to the <a href="%s">Fields tab</a> and make sure everything is formatted the way you would like.', 'wpforms-lead-forms' ),
					[
						'a' => [
							'href' => [],
						],
					]
				),
				esc_url(
					add_query_arg(
						[
							'view'    => 'fields',
							'form_id' => $form_id,
						],
						admin_url( 'admin.php?page=wpforms-builder' )
					)
				)
			)
			?>
		</div>
	</div>
</section>
