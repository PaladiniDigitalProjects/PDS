<?php
/**
 * Connected account template.
 *
 * @var string $key           Connection key.
 * @var string $label         Connection label.
 * @var string $date          Connection created date.
 * @var string $provider_slug Provider slug.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li class="wpforms-clear">
	<span class="label"><?php echo esc_html( $label ); ?></span>
	<span class="date">
		<?php
		echo esc_html(
			sprintf( /* translators: %1$s - Connection date. */
				__( 'Connected on: %1$s', 'wpforms-mailchimp' ),
				wpforms_date_format( $date, '', true )
			)
		);
		?>
	</span>
	<span class="remove">
		<a href="#" data-provider="<?php echo esc_attr( $provider_slug ); ?>" data-key="<?php echo esc_attr( $key ); ?>">
			<?php esc_html_e( 'Disconnect', 'wpforms-mailchimp' ); ?>
		</a>
	</span>
</li>
