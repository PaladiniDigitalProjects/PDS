<?php
/**
 * Plantilla del widget de chat (frontend) — modo conversación integrada.
 * Franja full-bleed sin caja: la respuesta ocupa el escenario como texto
 * editorial grande (efecto máquina de escribir) y el input vive integrado abajo.
 * Override desde el tema: {tema}/pds-web-talk/chat-widget.php
 * Variables: $title, $welcome, $maxlen y $t (textos en el idioma de la página).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<section class="pdswt-chat" data-welcome="<?php echo esc_attr( $welcome ); ?>" aria-label="<?php echo esc_attr( $title ); ?>">
	<div class="pdswt-chat__inner">
		<div class="pdswt-chat__tools">
			<button type="button" class="pdswt-chat__clear" title="<?php echo esc_attr( $t['clear'] ); ?>" aria-label="<?php echo esc_attr( $t['clear'] ); ?>">↺</button>
			<button type="button" class="pdswt-chat__close" title="<?php echo esc_attr( $t['exitFull'] ); ?>" aria-label="<?php echo esc_attr( $t['exitFull'] ); ?>" hidden>✕</button>
		</div>

		<div class="pdswt-chat__stage" role="log" aria-live="polite"></div>

		<form class="pdswt-chat__form">
			<textarea class="pdswt-chat__input" rows="1" maxlength="<?php echo (int) $maxlen; ?>" placeholder="<?php echo esc_attr( $t['placeholder'] ); ?>" aria-label="<?php echo esc_attr( $t['placeholder'] ); ?>"></textarea>
			<button type="submit" class="pdswt-chat__send" aria-label="<?php echo esc_attr( $t['send'] ); ?>">→</button>
		</form>
	</div>
</section>
