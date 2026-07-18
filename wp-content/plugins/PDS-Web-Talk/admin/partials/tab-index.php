<?php
/**
 * Pestaña Indexación: selección de post types + construcción del corpus a demanda.
 * Variables: $settings, $stats, $types (post types candidatos, objetos).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

$selected = ! empty( $settings['index_post_types'] ) ? (array) $settings['index_post_types'] : array();
$embed    = isset( $settings['embedding_model'] ) ? $settings['embedding_model'] : 'text-embedding-3-small';
$has_key  = ! empty( $settings['openai_api_key'] );
$knw      = PDSWT_CPT::POST_TYPE;
?>
<p class="description"><?php esc_html_e( 'Elige qué contenido alimenta al bot. El corpus se construye a demanda: trocea el contenido, calcula embeddings y los guarda.', 'pds-web-talk' ); ?></p>

<?php if ( ! $has_key ) : ?>
	<div class="notice notice-warning inline"><p><?php esc_html_e( 'Falta la API key de OpenAI (embeddings) en la pestaña General. Configúrala antes de indexar.', 'pds-web-talk' ); ?></p></div>
<?php endif; ?>

<h2><?php esc_html_e( 'Contenido a indexar', 'pds-web-talk' ); ?></h2>
<form method="post" action="options.php">
	<?php settings_fields( PDSWT_Admin::SETTINGS_GROUP ); ?>
	<input type="hidden" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[index_types_submitted]" value="1" />

	<table class="widefat striped" style="max-width:640px;">
		<thead>
			<tr>
				<th style="width:36px;"></th>
				<th><?php esc_html_e( 'Tipo de contenido', 'pds-web-talk' ); ?></th>
				<th><?php esc_html_e( 'Visibilidad', 'pds-web-talk' ); ?></th>
				<th style="text-align:right;"><?php esc_html_e( 'Entradas', 'pds-web-talk' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $types as $name => $obj ) :
				$count   = (int) wp_count_posts( $name )->publish;
				$checked = in_array( $name, $selected, true );
				$vis     = PDSWT::get_visibility( $name );
				?>
				<tr>
					<td><input type="checkbox" name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[index_post_types][]" value="<?php echo esc_attr( $name ); ?>" <?php checked( $checked ); ?> /></td>
					<td><strong><?php echo esc_html( $obj->labels->name ? $obj->labels->name : $name ); ?></strong> <code><?php echo esc_html( $name ); ?></code></td>
					<td>
						<select name="<?php echo esc_attr( PDSWT_OPTION_KEY ); ?>[index_visibility][<?php echo esc_attr( $name ); ?>]">
							<option value="shown" <?php selected( $vis, 'shown' ); ?>><?php esc_html_e( '🌐 Indexar y mostrar', 'pds-web-talk' ); ?></option>
							<option value="hidden" <?php selected( $vis, 'hidden' ); ?>><?php esc_html_e( '🔒 Indexar y no mostrar', 'pds-web-talk' ); ?></option>
						</select>
					</td>
					<td style="text-align:right;"><?php echo esc_html( $count ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php submit_button( __( 'Guardar selección', 'pds-web-talk' ) ); ?>
</form>

<div class="pdswt-hint">
	<p>
		<?php
		printf(
			/* translators: %s: enlace para crear conocimiento */
			esc_html__( '¿Quieres que el bot sepa algo que NO aparece en la web (p. ej. la Filosofía de la empresa, FAQs, tono)? Créalo en %s: se indexa pero no se muestra.', 'pds-web-talk' ),
			'<a href="' . esc_url( admin_url( 'post-new.php?post_type=' . $knw ) ) . '">' . esc_html__( 'Conocimiento del bot', 'pds-web-talk' ) . '</a>'
		);
		?>
	</p>
</div>

<hr />

<h2><?php esc_html_e( 'Construir el índice', 'pds-web-talk' ); ?></h2>
<table class="widefat" style="max-width:520px;margin:12px 0;">
	<tbody>
		<tr><th><?php esc_html_e( 'Modelo de embeddings', 'pds-web-talk' ); ?></th><td><code><?php echo esc_html( $embed ); ?></code></td></tr>
		<tr><th><?php esc_html_e( 'Fragmentos indexados', 'pds-web-talk' ); ?></th><td id="pdswt-stat-chunks"><strong><?php echo (int) $stats['chunks']; ?></strong></td></tr>
		<tr><th><?php esc_html_e( 'Contenidos indexados', 'pds-web-talk' ); ?></th><td id="pdswt-stat-posts"><strong><?php echo (int) $stats['posts']; ?></strong></td></tr>
	</tbody>
</table>

<p>
	<button type="button" class="button button-primary" id="pdswt-index-start" <?php disabled( ! $has_key ); ?>><?php esc_html_e( 'Reconstruir índice', 'pds-web-talk' ); ?></button>
	<button type="button" class="button" id="pdswt-index-clear"><?php esc_html_e( 'Vaciar índice', 'pds-web-talk' ); ?></button>
</p>

<div id="pdswt-progress-wrap" style="display:none;max-width:520px;">
	<div class="pdswt-progress"><div class="pdswt-progress-bar" id="pdswt-progress-bar"></div></div>
	<p id="pdswt-progress-label" class="description"></p>
</div>

<div id="pdswt-index-log" class="pdswt-test-output" aria-live="polite"></div>

<hr />

<h2><?php esc_html_e( 'Archivo del índice (JSON)', 'pds-web-talk' ); ?></h2>
<p class="description"><?php esc_html_e( 'El corpus se guarda también como archivo JSON en un directorio protegido, para verificarlo, hacer copia y restaurar sin volver a pagar embeddings. Se regenera al reconstruir el índice.', 'pds-web-talk' ); ?></p>

<table class="widefat" style="max-width:640px;margin:12px 0;">
	<tbody>
		<tr><th><?php esc_html_e( 'Ubicación', 'pds-web-talk' ); ?></th><td><code>uploads/pds-web-talk/<?php echo esc_html( PDSWT_Corpus_IO::FILENAME ); ?></code></td></tr>
		<?php if ( ! empty( $file_info['exists'] ) ) : ?>
			<tr><th><?php esc_html_e( 'Tamaño', 'pds-web-talk' ); ?></th><td><?php echo esc_html( size_format( $file_info['size'] ) ); ?></td></tr>
			<tr><th><?php esc_html_e( 'Generado', 'pds-web-talk' ); ?></th><td><?php echo esc_html( wp_date( 'Y-m-d H:i', $file_info['modified'] ) ); ?></td></tr>
		<?php else : ?>
			<tr><td colspan="2"><em><?php esc_html_e( 'Aún no generado. Reconstruye el índice o pulsa «Regenerar archivo».', 'pds-web-talk' ); ?></em></td></tr>
		<?php endif; ?>
	</tbody>
</table>

<p>
	<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pdswt_corpus_regen' ), 'pdswt_corpus' ) ); ?>"><?php esc_html_e( 'Regenerar archivo', 'pds-web-talk' ); ?></a>
	<?php if ( ! empty( $file_info['exists'] ) ) : ?>
		<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pdswt_corpus_download' ), 'pdswt_corpus' ) ); ?>"><?php esc_html_e( 'Descargar JSON', 'pds-web-talk' ); ?></a>
	<?php endif; ?>
</p>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="margin-top:10px;">
	<input type="hidden" name="action" value="pdswt_corpus_import" />
	<?php wp_nonce_field( 'pdswt_corpus' ); ?>
	<label><?php esc_html_e( 'Importar un JSON (reemplaza el corpus, sin coste de IA):', 'pds-web-talk' ); ?>
		<input type="file" name="corpus_file" accept="application/json,.json" />
	</label>
	<?php submit_button( __( 'Importar', 'pds-web-talk' ), 'secondary', 'submit', false ); ?>
</form>
