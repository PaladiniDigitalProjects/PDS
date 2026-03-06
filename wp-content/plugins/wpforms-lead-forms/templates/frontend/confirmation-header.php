<?php
/**
 * Confirmation header template.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wpforms-lead-forms-confirmation-icon">
	<svg width="62" height="47" viewBox="0 0 62 47" fill="none">
		<path d="M21.2734 45.0625C22.4453 46.2344 24.4375 46.2344 25.6094 45.0625L60.0625 10.6094C61.2344 9.4375 61.2344 7.44531 60.0625 6.27344L55.8438 2.05469C54.6719 0.882812 52.7969 0.882812 51.625 2.05469L23.5 30.1797L10.2578 17.0547C9.08594 15.8828 7.21094 15.8828 6.03906 17.0547L1.82031 21.2734C0.648438 22.4453 0.648438 24.4375 1.82031 25.6094L21.2734 45.0625Z"/>
	</svg>
</div>

<div class="wpforms-lead-forms-confirmation-title">
	<?php esc_html_e( 'Thank You!', 'wpforms-lead-forms' ); ?>
</div>
