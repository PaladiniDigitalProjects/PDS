<?php
/**
 * Trocea el contenido de un post en fragmentos autocontenidos.
 * Estrategia: recorre los bloques Gutenberg, agrupa por encabezados
 * y respeta un tamaño máximo por chunk. Ignora bloques decorativos.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Chunker {

	/** Tamaño máximo aproximado de un chunk (caracteres). */
	const MAX_CHARS = 1500;

	/**
	 * Longitud mínima de un chunk (caracteres). Por debajo son encabezados
	 * sueltos o rótulos de maquetación ("FAQS", "More Projects", "01") que
	 * no responden a nada y solo ensucian el corpus.
	 */
	const MIN_CHARS = 40;

	/** Bloques puramente decorativos que no aportan texto. */
	private static $skip_blocks = array(
		'core/spacer',
		'core/separator',
		'core/buttons',
		'core/button',
		'core/html',
		'core/post-featured-image',
		'core/query',
		'core/post-template',
	);

	/**
	 * @param string $title   Título del post (encabeza el primer chunk).
	 * @param string $content post_content con bloques.
	 * @return array Lista de strings (chunks).
	 */
	public static function chunk( $title, $content ) {
		$blocks   = parse_blocks( $content );
		$flat     = self::flatten( $blocks );

		$chunks   = array();
		$current  = '';
		$heading  = trim( wp_strip_all_tags( (string) $title ) );

		$push = function () use ( &$chunks, &$current ) {
			$text = trim( $current );
			if ( mb_strlen( $text ) >= self::MIN_CHARS ) {
				$chunks[] = $text;
			}
			$current = '';
		};

		foreach ( $flat as $block ) {
			$name = isset( $block['blockName'] ) ? $block['blockName'] : '';
			if ( $name && in_array( $name, self::$skip_blocks, true ) ) {
				continue;
			}

			$text = self::block_text( $block );
			if ( '' === $text ) {
				continue;
			}

			// Un encabezado inicia un chunk nuevo.
			if ( 'core/heading' === $name ) {
				$push();
				$heading = $text;
				$current = $heading . "\n";
				continue;
			}

			// Si añadir el bloque excede el tope, cierra el chunk y reabre con el encabezado de contexto.
			if ( strlen( $current ) + strlen( $text ) > self::MAX_CHARS && '' !== trim( $current ) ) {
				$push();
				$current = ( '' !== $heading ? $heading . "\n" : '' );
			}

			$current .= $text . "\n";
		}
		$push();

		// Si no había bloques con texto, cae al contenido plano como último recurso.
		if ( empty( $chunks ) ) {
			$plain = trim( wp_strip_all_tags( $content ) );
			if ( '' !== $plain ) {
				$prefix   = ( '' !== $heading ? $heading . "\n" : '' );
				$chunks   = self::split_long( $prefix . $plain );
			}
		}

		return $chunks;
	}

	/**
	 * Texto que se envía a los embeddings: el fragmento precedido de su
	 * procedencia. Un fragmento aislado ("Okay, but what services do you
	 * offer? ...") no menciona de quién habla, así que no casa con preguntas
	 * que nombran la marca. El prefijo le devuelve ese contexto.
	 *
	 * El texto que se guarda y se le pasa al modelo sigue siendo el original.
	 *
	 * @param string $title Título del post.
	 * @param string $chunk Fragmento.
	 * @return string
	 */
	public static function embed_text( $title, $chunk ) {
		$site  = trim( wp_strip_all_tags( (string) get_bloginfo( 'name' ) ) );
		$title = trim( wp_strip_all_tags( (string) $title ) );

		$context = array_filter( array( $site, $title ) );
		if ( empty( $context ) ) {
			return $chunk;
		}

		return implode( ' — ', $context ) . "\n\n" . $chunk;
	}

	/**
	 * Aplana bloques anidados (columns, group, etc.) en una lista lineal.
	 */
	private static function flatten( $blocks ) {
		$out = array();
		foreach ( (array) $blocks as $block ) {
			$out[] = $block;
			if ( ! empty( $block['innerBlocks'] ) ) {
				$out = array_merge( $out, self::flatten( $block['innerBlocks'] ) );
			}
		}
		return $out;
	}

	/**
	 * Extrae el texto plano de un bloque.
	 */
	private static function block_text( $block ) {
		$html = isset( $block['innerHTML'] ) ? $block['innerHTML'] : '';
		if ( '' === trim( $html ) && ! empty( $block['innerContent'] ) ) {
			$html = implode( ' ', array_filter( $block['innerContent'], 'is_string' ) );
		}
		$text = wp_strip_all_tags( $html );
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( (string) $text );
	}

	/**
	 * Parte un texto largo en trozos <= MAX_CHARS respetando frases.
	 */
	private static function split_long( $text ) {
		if ( strlen( $text ) <= self::MAX_CHARS ) {
			return array( $text );
		}
		$parts     = array();
		$sentences = preg_split( '/(?<=[.!?])\s+/u', $text );
		$buffer    = '';
		foreach ( (array) $sentences as $s ) {
			if ( strlen( $buffer ) + strlen( $s ) > self::MAX_CHARS && '' !== $buffer ) {
				$parts[] = trim( $buffer );
				$buffer  = '';
			}
			$buffer .= $s . ' ';
		}
		if ( '' !== trim( $buffer ) ) {
			$parts[] = trim( $buffer );
		}
		return $parts;
	}
}
