<?php
/**
 * Metabox "Pieza para el chatbot": cataloga una entrada (proyecto/partner)
 * como componente visual que el bot puede mostrar bajo la respuesta.
 * Guarda en post meta; el indexer construye la pieza a partir de estos datos.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Piece_Meta {

	/** Post types que pueden catalogarse como pieza. */
	const POST_TYPES = array( 'proyecto', 'partners', 'page' );

	const NONCE = 'pdswt_piece_meta_nonce';

	/** Claves de meta. */
	const M_ENABLED = '_pdswt_piece_enabled';
	const M_IMAGE   = '_pdswt_piece_image';
	const M_LOGO    = '_pdswt_piece_logo';
	const M_TITLE   = '_pdswt_piece_title';
	const M_WEIGHT  = '_pdswt_piece_weight';

	public function add_meta_box() {
		foreach ( self::POST_TYPES as $pt ) {
			add_meta_box(
				'pdswt_piece',
				__( 'Pieza para el chatbot', 'pds-web-talk' ),
				array( $this, 'render' ),
				$pt,
				'side',
				'default'
			);
		}
	}

	public function render( $post ) {
		wp_nonce_field( 'pdswt_piece_meta', self::NONCE );

		$enabled = (bool) get_post_meta( $post->ID, self::M_ENABLED, true );
		$image   = (int) get_post_meta( $post->ID, self::M_IMAGE, true );
		$logo    = (int) get_post_meta( $post->ID, self::M_LOGO, true );
		$title   = (string) get_post_meta( $post->ID, self::M_TITLE, true );
		$weight  = get_post_meta( $post->ID, self::M_WEIGHT, true );
		$weight  = ( '' === $weight ) ? 5 : (int) $weight;

		// La imagen por defecto es la destacada, si la hay.
		$image_url = $image ? wp_get_attachment_image_url( $image, 'medium' ) : '';
		$image_ph  = ( ! $image_url && has_post_thumbnail( $post->ID ) ) ? get_the_post_thumbnail_url( $post->ID, 'medium' ) : '';
		$logo_url  = $logo ? wp_get_attachment_image_url( $logo, 'medium' ) : '';

		$title_ph = get_the_title( $post->ID );
		?>
		<div class="pdswt-piece">
			<p>
				<label>
					<input type="checkbox" name="pdswt_piece_enabled" value="1" <?php checked( $enabled ); ?> class="pdswt-piece__toggle" />
					<strong><?php esc_html_e( 'Mostrar como tarjeta en el chatbot', 'pds-web-talk' ); ?></strong>
				</label>
			</p>

			<div class="pdswt-piece__fields" style="<?php echo $enabled ? '' : 'display:none'; ?>">
				<p>
					<label><?php esc_html_e( 'Imagen de la tarjeta', 'pds-web-talk' ); ?></label><br>
					<span class="pdswt-piece__media" data-target="image">
						<img class="pdswt-piece__preview" src="<?php echo esc_url( $image_url ? $image_url : $image_ph ); ?>" style="<?php echo ( $image_url || $image_ph ) ? '' : 'display:none'; ?>max-width:100%;height:auto;border-radius:4px;margin-bottom:4px" />
						<input type="hidden" name="pdswt_piece_image" value="<?php echo esc_attr( $image ); ?>" />
						<button type="button" class="button pdswt-piece__pick"><?php esc_html_e( 'Elegir imagen', 'pds-web-talk' ); ?></button>
						<button type="button" class="button-link pdswt-piece__clear" style="<?php echo $image ? '' : 'display:none'; ?>"><?php esc_html_e( 'Quitar', 'pds-web-talk' ); ?></button>
					</span>
					<?php if ( ! $image && $image_ph ) : ?>
						<em class="description"><?php esc_html_e( '(usando la imagen destacada por defecto)', 'pds-web-talk' ); ?></em>
					<?php endif; ?>
				</p>

				<p>
					<label><?php esc_html_e( 'Logo (opcional)', 'pds-web-talk' ); ?></label><br>
					<span class="pdswt-piece__media" data-target="logo">
						<img class="pdswt-piece__preview" src="<?php echo esc_url( $logo_url ); ?>" style="<?php echo $logo_url ? '' : 'display:none'; ?>max-width:120px;height:auto;margin-bottom:4px" />
						<input type="hidden" name="pdswt_piece_logo" value="<?php echo esc_attr( $logo ); ?>" />
						<button type="button" class="button pdswt-piece__pick"><?php esc_html_e( 'Elegir logo', 'pds-web-talk' ); ?></button>
						<button type="button" class="button-link pdswt-piece__clear" style="<?php echo $logo ? '' : 'display:none'; ?>"><?php esc_html_e( 'Quitar', 'pds-web-talk' ); ?></button>
					</span>
				</p>

				<p>
					<label for="pdswt_piece_title"><?php esc_html_e( 'Título de la tarjeta', 'pds-web-talk' ); ?></label>
					<input type="text" id="pdswt_piece_title" name="pdswt_piece_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( $title_ph ); ?>" class="widefat" />
				</p>

				<p>
					<label for="pdswt_piece_weight"><?php esc_html_e( 'Importancia (0–10)', 'pds-web-talk' ); ?></label>
					<input type="number" id="pdswt_piece_weight" name="pdswt_piece_weight" value="<?php echo esc_attr( $weight ); ?>" min="0" max="10" step="1" style="width:70px" />
					<span class="description"><?php esc_html_e( 'Peso en el ranking del bot (no anula la relevancia).', 'pds-web-talk' ); ?></span>
				</p>
			</div>
		</div>
		<?php
	}

	public function save( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE ] ), 'pdswt_piece_meta' ) ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! in_array( get_post_type( $post_id ), self::POST_TYPES, true ) ) {
			return;
		}

		$enabled = ! empty( $_POST['pdswt_piece_enabled'] );
		update_post_meta( $post_id, self::M_ENABLED, $enabled ? 1 : 0 );

		$image = isset( $_POST['pdswt_piece_image'] ) ? absint( $_POST['pdswt_piece_image'] ) : 0;
		$logo  = isset( $_POST['pdswt_piece_logo'] ) ? absint( $_POST['pdswt_piece_logo'] ) : 0;
		$title = isset( $_POST['pdswt_piece_title'] ) ? sanitize_text_field( wp_unslash( $_POST['pdswt_piece_title'] ) ) : '';
		$weight = isset( $_POST['pdswt_piece_weight'] ) ? max( 0, min( 10, absint( $_POST['pdswt_piece_weight'] ) ) ) : 5;

		$image ? update_post_meta( $post_id, self::M_IMAGE, $image ) : delete_post_meta( $post_id, self::M_IMAGE );
		$logo ? update_post_meta( $post_id, self::M_LOGO, $logo ) : delete_post_meta( $post_id, self::M_LOGO );
		'' !== $title ? update_post_meta( $post_id, self::M_TITLE, $title ) : delete_post_meta( $post_id, self::M_TITLE );
		update_post_meta( $post_id, self::M_WEIGHT, $weight );
	}

	public function enqueue( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, self::POST_TYPES, true ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'pdswt-piece-meta',
			PDSWT_PLUGIN_URL . 'admin/js/pdswt-piece-meta.js',
			array( 'jquery' ),
			PDSWT_VERSION,
			true
		);
	}

	/**
	 * ¿Está la entrada catalogada como pieza mostrable? (para el indexer)
	 */
	public static function is_enabled( $post_id ) {
		return (bool) get_post_meta( $post_id, self::M_ENABLED, true );
	}

	/**
	 * Datos de render de la pieza (imagen, logo, título, enlace, peso) o null.
	 */
	public static function get_piece( $post_id ) {
		if ( ! self::is_enabled( $post_id ) ) {
			return null;
		}
		$image = (int) get_post_meta( $post_id, self::M_IMAGE, true );
		if ( ! $image && has_post_thumbnail( $post_id ) ) {
			$image = get_post_thumbnail_id( $post_id );
		}
		$logo  = (int) get_post_meta( $post_id, self::M_LOGO, true );
		$title = (string) get_post_meta( $post_id, self::M_TITLE, true );
		if ( '' === $title ) {
			$title = get_the_title( $post_id );
		}
		$weight = get_post_meta( $post_id, self::M_WEIGHT, true );
		$weight = ( '' === $weight ) ? 5 : (int) $weight;

		$cat_map  = array( 'partners' => 'partner', 'proyecto' => 'project', 'page' => 'page' );
		$post_type = get_post_type( $post_id );

		return array(
			'category'  => isset( $cat_map[ $post_type ] ) ? $cat_map[ $post_type ] : $post_type,
			'title'     => $title,
			'image'     => $image ? wp_get_attachment_image_url( $image, 'large' ) : '',
			'logo'      => $logo ? wp_get_attachment_image_url( $logo, 'medium' ) : '',
			'link'      => get_permalink( $post_id ),
			'weight'    => $weight,
		);
	}
}
