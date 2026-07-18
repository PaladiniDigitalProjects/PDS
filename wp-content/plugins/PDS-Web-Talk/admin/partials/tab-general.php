<?php
/**
 * Pestaña General: proveedor, API keys, comportamiento y límites + chat de prueba.
 * Variable disponible: $settings.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$provider   = isset( $settings['provider'] ) ? $settings['provider'] : 'claude';
$claude_key = isset( $settings['claude_api_key'] ) ? $settings['claude_api_key'] : '';
$claude_mod = isset( $settings['claude_model'] ) ? $settings['claude_model'] : 'claude-sonnet-5';
$openai_key = isset( $settings['openai_api_key'] ) ? $settings['openai_api_key'] : '';
$openai_mod = isset( $settings['openai_model'] ) ? $settings['openai_model'] : 'gpt-4o-mini';
$embed_mod  = isset( $settings['embedding_model'] ) ? $settings['embedding_model'] : 'text-embedding-3-small';
$sys_prompt = isset( $settings['system_prompt'] ) ? $settings['system_prompt'] : '';
$budget     = isset( $settings['monthly_budget'] ) ? $settings['monthly_budget'] : 20;
$rate       = isset( $settings['rate_limit_per_hour'] ) ? $settings['rate_limit_per_hour'] : 30;
$maxlen     = isset( $settings['max_message_length'] ) ? $settings['max_message_length'] : 1000;
$top_n      = isset( $settings['top_n'] ) ? $settings['top_n'] : 4;

$prov      = PDSWT_Usage::provider_totals();
$month     = gmdate( 'Y-m' );
$usage_line = function ( $key ) use ( $prov ) {
	if ( empty( $prov[ $key ] ) || ( 0 === $prov[ $key ]['req'] ) ) {
		return '<span class="description">' . esc_html__( 'Sin uso este mes.', 'pds-web-talk' ) . '</span>';
	}
	$t    = $prov[ $key ];
	$cost = $t['cost_known'] ? '~$' . number_format( $t['cost'], 4 ) : '—';
	return sprintf(
		'<span class="pdswt-usage-inline">📊 %s: <strong>%s</strong> · %s %s · %s %s · <strong>%s</strong></span>',
		esc_html__( 'Este mes', 'pds-web-talk' ),
		esc_html( number_format_i18n( $t['req'] ) . ' ' . __( 'peticiones', 'pds-web-talk' ) ),
		esc_html( number_format_i18n( $t['in'] ) ), esc_html__( 'in', 'pds-web-talk' ),
		esc_html( number_format_i18n( $t['out'] ) ), esc_html__( 'out', 'pds-web-talk' ),
		esc_html( $cost )
	);
};
?>
<form method="post" action="options.php">
	<?php settings_fields( PDSWT_Admin::SETTINGS_GROUP ); ?>

	<h2><?php esc_html_e( 'Proveedor de IA', 'pds-web-talk' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="pdswt_provider"><?php esc_html_e( 'Proveedor de chat', 'pds-web-talk' ); ?></label></th>
			<td>
				<select name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[provider]" id="pdswt_provider">
					<option value="claude" <?php selected( $provider, 'claude' ); ?>>Claude (Anthropic)</option>
					<option value="openai" <?php selected( $provider, 'openai' ); ?>>OpenAI (próximamente)</option>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_claude_key"><?php esc_html_e( 'API key de Claude', 'pds-web-talk' ); ?></label></th>
			<td><input type="password" class="regular-text" id="pdswt_claude_key" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[claude_api_key]" value="<?php echo esc_attr( $claude_key ); ?>" autocomplete="off" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_claude_model"><?php esc_html_e( 'Modelo de Claude', 'pds-web-talk' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="pdswt_claude_model" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[claude_model]" value="<?php echo esc_attr( $claude_mod ); ?>" />
				<p class="description"><?php esc_html_e( 'Ej.: claude-sonnet-5, claude-opus-4-8, claude-haiku-4-5-20251001.', 'pds-web-talk' ); ?></p>
				<p><?php echo $usage_line( 'claude' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_openai_key"><?php esc_html_e( 'API key de OpenAI', 'pds-web-talk' ); ?></label></th>
			<td>
				<input type="password" class="regular-text" id="pdswt_openai_key" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[openai_api_key]" value="<?php echo esc_attr( $openai_key ); ?>" autocomplete="off" />
				<input type="hidden" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[openai_model]" value="<?php echo esc_attr( $openai_mod ); ?>" />
				<p class="description"><?php esc_html_e( 'Se usa para los embeddings del RAG (indexación).', 'pds-web-talk' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_embed_model"><?php esc_html_e( 'Modelo de embeddings', 'pds-web-talk' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="pdswt_embed_model" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[embedding_model]" value="<?php echo esc_attr( $embed_mod ); ?>" />
				<p><?php echo $usage_line( 'openai' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			</td>
		</tr>
	</table>

	<h2><?php esc_html_e( 'Comportamiento y límites', 'pds-web-talk' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="pdswt_system_prompt"><?php esc_html_e( 'Instrucción de sistema (persona/tono)', 'pds-web-talk' ); ?></label></th>
			<td><textarea class="large-text" rows="4" id="pdswt_system_prompt" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[system_prompt]"><?php echo esc_textarea( $sys_prompt ); ?></textarea></td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_top_n"><?php esc_html_e( 'Fragmentos de contexto (top-N)', 'pds-web-talk' ); ?></label></th>
			<td><input type="number" step="1" min="1" id="pdswt_top_n" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[top_n]" value="<?php echo esc_attr( $top_n ); ?>" class="small-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_budget"><?php esc_html_e( 'Tope de gasto mensual (USD aprox.)', 'pds-web-talk' ); ?></label></th>
			<td><input type="number" step="1" min="0" id="pdswt_budget" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[monthly_budget]" value="<?php echo esc_attr( $budget ); ?>" class="small-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_rate"><?php esc_html_e( 'Límite de mensajes por hora / IP', 'pds-web-talk' ); ?></label></th>
			<td><input type="number" step="1" min="0" id="pdswt_rate" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[rate_limit_per_hour]" value="<?php echo esc_attr( $rate ); ?>" class="small-text" /></td>
		</tr>
		<tr>
			<th scope="row"><label for="pdswt_maxlen"><?php esc_html_e( 'Longitud máxima de mensaje', 'pds-web-talk' ); ?></label></th>
			<td><input type="number" step="1" min="1" id="pdswt_maxlen" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[max_message_length]" value="<?php echo esc_attr( $maxlen ); ?>" class="small-text" /></td>
		</tr>
	</table>

	<?php submit_button(); ?>
</form>

<hr />

<h2><?php esc_html_e( 'Prueba de conexión', 'pds-web-talk' ); ?></h2>
<p class="description"><?php esc_html_e( 'Envía un mensaje directo al proveedor configurado (sin RAG) para verificar que la API responde.', 'pds-web-talk' ); ?></p>
<div class="pdswt-test">
	<textarea id="pdswt-test-input" class="large-text" rows="2" placeholder="<?php esc_attr_e( 'Escribe un mensaje de prueba…', 'pds-web-talk' ); ?>"></textarea>
	<p><button type="button" class="button button-primary" id="pdswt-test-send"><?php esc_html_e( 'Enviar', 'pds-web-talk' ); ?></button></p>
	<div id="pdswt-test-output" class="pdswt-test-output" aria-live="polite"></div>
</div>

<hr />

<?php
$remaining     = ( $budget > 0 ) ? max( 0, (float) $budget - (float) $month_total ) : 0;
$pct_remaining = ( $budget > 0 ) ? max( 0, min( 100, round( ( $remaining / (float) $budget ) * 100 ) ) ) : 0;
$over          = ( $budget > 0 && (float) $month_total >= (float) $budget );
?>
<?php if ( $budget > 0 ) : ?>
	<h2><?php esc_html_e( 'Presupuesto restante este mes', 'pds-web-talk' ); ?>
		<span class="pdswt-month-total<?php echo $over ? ' pdswt-over-text' : ''; ?>">$<?php echo esc_html( number_format( $remaining, 4 ) ); ?></span>
		<span class="description"><?php
			/* translators: 1: tope en $, 2: gastado en $, 3: mes */
			printf( esc_html__( 'de $%1$s · gastado $%2$s · %3$s', 'pds-web-talk' ), esc_html( number_format( (float) $budget, 2 ) ), esc_html( number_format( (float) $month_total, 4 ) ), esc_html( $month ) );
		?></span>
	</h2>
	<div class="pdswt-progress" style="max-width:560px;"><div class="pdswt-progress-bar<?php echo $over ? ' pdswt-over' : ''; ?>" style="width:<?php echo (int) $pct_remaining; ?>%;"></div></div>
	<?php if ( $over ) : ?>
		<p class="description" style="color:#b32d2e;"><strong><?php esc_html_e( '⚠️ Presupuesto del mes agotado.', 'pds-web-talk' ); ?></strong></p>
	<?php endif; ?>
<?php else : ?>
	<h2><?php esc_html_e( 'Coste estimado del mes', 'pds-web-talk' ); ?>
		<span class="pdswt-month-total">$<?php echo esc_html( number_format( (float) $month_total, 4 ) ); ?></span>
		<span class="description"><?php esc_html_e( 'sin tope configurado', 'pds-web-talk' ); ?> · <?php echo esc_html( $month ); ?></span>
	</h2>
<?php endif; ?>
<?php if ( isset( $_GET['updated'] ) ) : ?>
	<div class="notice notice-success inline"><p><?php esc_html_e( 'Precios guardados.', 'pds-web-talk' ); ?></p></div>
<?php endif; ?>
<p class="description"><?php esc_html_e( 'Tokens exactos; coste estimado según estos precios (ajústalos a tu tarifa real). La factura real está en el panel de cada proveedor.', 'pds-web-talk' ); ?></p>

<?php
$models_seen = array_keys( PDSWT_Usage::default_rates() );
foreach ( PDSWT_Usage::get_month() as $models ) {
	foreach ( $models as $model => $r ) {
		$models_seen[] = $model;
	}
}
$models_seen = array_unique( $models_seen );
?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<input type="hidden" name="action" value="pdswt_save_rates" />
	<?php wp_nonce_field( 'pdswt_save_rates' ); ?>
	<table class="widefat" style="max-width:560px;">
		<thead><tr><th><?php esc_html_e( 'Modelo', 'pds-web-talk' ); ?></th><th><?php esc_html_e( 'Entrada ($/1M)', 'pds-web-talk' ); ?></th><th><?php esc_html_e( 'Salida ($/1M)', 'pds-web-talk' ); ?></th></tr></thead>
		<tbody>
		<?php foreach ( $models_seen as $model ) :
			$ri = isset( $rates[ $model ]['in'] ) ? $rates[ $model ]['in'] : 0;
			$ro = isset( $rates[ $model ]['out'] ) ? $rates[ $model ]['out'] : 0;
			?>
			<tr>
				<td><code><?php echo esc_html( $model ); ?></code></td>
				<td><input type="number" step="0.001" min="0" name="rate_in[<?php echo esc_attr( $model ); ?>]" value="<?php echo esc_attr( $ri ); ?>" class="small-text" /></td>
				<td><input type="number" step="0.001" min="0" name="rate_out[<?php echo esc_attr( $model ); ?>]" value="<?php echo esc_attr( $ro ); ?>" class="small-text" /></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php submit_button( __( 'Guardar precios', 'pds-web-talk' ) ); ?>
</form>
