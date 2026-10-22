<?php
/**
 * Plantilla del chat flotante: botón fijo abajo a la derecha y panel con la
 * misma conversación que el chat de la home (mismo JS y mismo historial).
 * Override desde el tema: {tema}/pds-web-talk/chat-floating.php
 * Variables: $title, $welcome, $maxlen y $t (textos en el idioma de la página).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="pdswt-float">
	<div class="pdswt-float__backdrop" hidden></div>
	<section id="pdswt-float-panel" class="pdswt-chat pdswt-chat--floating" data-welcome="<?php echo esc_attr( $welcome ); ?>" aria-label="<?php echo esc_attr( $title ); ?>" hidden>
		<div class="pdswt-chat__inner">
			<div class="pdswt-chat__tools">
				<p class="pdswt-chat__title"><?php echo esc_html( $title ); ?></p>
				<button type="button" class="pdswt-float__mail" title="<?php echo esc_attr( $t['emailOpen'] ); ?>" aria-label="<?php echo esc_attr( $t['emailOpen'] ); ?>" hidden><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" d="M3.5 6h17v12h-17z M3.5 6.5l8.5 7 8.5-7"/></svg></button>
				<button type="button" class="pdswt-chat__clear" title="<?php echo esc_attr( $t['clear'] ); ?>" aria-label="<?php echo esc_attr( $t['clear'] ); ?>">↺</button>
				<button type="button" class="pdswt-float__close" title="<?php echo esc_attr( $t['close'] ); ?>" aria-label="<?php echo esc_attr( $t['close'] ); ?>">✕</button>
			</div>

			<div class="pdswt-chat__stage" role="log" aria-live="polite"></div>

			<form class="pdswt-chat__form">
				<textarea class="pdswt-chat__input" rows="1" maxlength="<?php echo (int) $maxlen; ?>" placeholder="<?php echo esc_attr( $t['placeholder'] ); ?>" aria-label="<?php echo esc_attr( $t['placeholder'] ); ?>"></textarea>
				<button type="submit" class="pdswt-chat__send" aria-label="<?php echo esc_attr( $t['send'] ); ?>">→</button>
			</form>
		</div>
	</section>

	<button type="button" class="pdswt-float__fab shadow10" aria-controls="pdswt-float-panel" aria-expanded="false" aria-label="<?php echo esc_attr( $t['open'] ); ?>" data-label-open="<?php echo esc_attr( $t['open'] ); ?>" data-label-close="<?php echo esc_attr( $t['close'] ); ?>">
		<svg class="pdswt-float__icon pdswt-float__icon--chat" viewBox="0 0 24 24" width="34" height="34" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 4v-4H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
	</button>
</div>
