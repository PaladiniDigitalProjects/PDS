<?php
/**
 * Retriever: dada una pregunta, encuentra los fragmentos más relevantes
 * del corpus por similitud coseno (búsqueda vectorial en PHP).
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Retriever {

	private $settings;
	private $embedder;

	public function __construct( $settings = null ) {
		$this->settings = is_array( $settings ) ? $settings : PDSWT::get_settings();
		$this->embedder = new PDSWT_OpenAI_Embeddings(
			isset( $this->settings['openai_api_key'] ) ? $this->settings['openai_api_key'] : '',
			isset( $this->settings['embedding_model'] ) ? $this->settings['embedding_model'] : 'text-embedding-3-small'
		);
	}

	/**
	 * @return array { ok, results:[ {score,post_id,post_type,title,link,visibility,text} ], error }
	 */
	/**
	 * @param string      $query Pregunta del usuario.
	 * @param int|null    $top_n Número de fragmentos a devolver.
	 * @param string|null $lang  Idioma de la pregunta (en|es|ca). Si se pasa, solo se
	 *                           consideran fragmentos de ese idioma, más los del CPT de
	 *                           conocimiento, que solo existe en inglés y da al bot su
	 *                           criterio: sin esa excepción, preguntar en catalán dejaría
	 *                           fuera todo el posicionamiento de PDS.
	 */
	public function retrieve( $query, $top_n = null, $lang = null ) {
		$top_n = $top_n ? (int) $top_n : ( ! empty( $this->settings['top_n'] ) ? (int) $this->settings['top_n'] : 4 );

		if ( ! $this->embedder->is_configured() ) {
			return array( 'ok' => false, 'error' => __( 'Falta la API key de OpenAI (embeddings).', 'pds-web-talk' ) );
		}

		$emb = $this->embedder->embed( (string) $query );
		if ( empty( $emb['ok'] ) ) {
			return array( 'ok' => false, 'error' => $emb['error'] );
		}
		$qvec  = $emb['vectors'][0];
		$qnorm = $this->norm( $qvec );
		if ( $qnorm <= 0 ) {
			return array( 'ok' => false, 'error' => __( 'Embedding de la consulta no válido.', 'pds-web-talk' ) );
		}

		global $wpdb;
		$table = PDSWT_Activator::corpus_table();
		$campos = 'post_id, post_type, chunk_index, chunk_text, embedding, weight, is_component, render_data';

		/*
		 * Sin filtro de idioma, cada contenido compite consigo mismo en tres versiones
		 * y el contexto acaba mezclando lenguas. Se filtra solo si hay fragmentos de ese
		 * idioma: si el detector devuelve algo inesperado, mejor buscar en todo que
		 * quedarse sin contexto.
		 */
		$idiomas_validos = $wpdb->get_col( "SELECT DISTINCT lang FROM {$table}" );
		if ( $lang && in_array( $lang, $idiomas_validos, true ) ) {
			$rows = $wpdb->get_results( $wpdb->prepare(
				"SELECT {$campos} FROM {$table} WHERE embedding IS NOT NULL AND ( lang = %s OR post_type = %s )",
				$lang,
				'pdswt_knowledge'
			) );
		} else {
			$rows = $wpdb->get_results( "SELECT {$campos} FROM {$table} WHERE embedding IS NOT NULL" );
		}

		$scored = array();
		foreach ( (array) $rows as $row ) {
			$vec = json_decode( $row->embedding, true );
			if ( ! is_array( $vec ) || empty( $vec ) ) {
				continue;
			}
			$score = $this->cosine( $qvec, $qnorm, $vec );
			// El peso es un empujón, no un mandato: desempata entre piezas de
			// relevancia parecida sin tapar la similitud semántica real.
			$final = $score + ( ( (float) $row->weight - 1 ) * 0.01 );
			$scored[] = array(
				'score'        => $final,
				'similarity'   => $score,
				'post_id'      => (int) $row->post_id,
				'post_type'    => $row->post_type,
				'chunk_index'  => (int) $row->chunk_index,
				'text'         => $row->chunk_text,
				'is_component' => (int) $row->is_component,
				'render_data'  => $row->render_data,
			);
		}

		usort( $scored, function ( $a, $b ) {
			if ( $a['score'] === $b['score'] ) {
				return 0;
			}
			return ( $a['score'] < $b['score'] ) ? 1 : -1;
		} );

		$results = $this->expand_neighbors( $scored, array_slice( $scored, 0, $top_n ) );

		// Enriquecer con metadatos y visibilidad.
		foreach ( $results as &$r ) {
			$r['title']      = get_the_title( $r['post_id'] );
			$r['link']       = get_permalink( $r['post_id'] );
			$r['visibility'] = PDSWT::get_visibility( $r['post_type'] );
		}
		unset( $r );

		// Piezas visuales: se muestran cuando la ENTRADA es relevante para la
		// pregunta (por su contenido real, no por el texto corto de la pieza).
		// Recorremos el ranking; para cada post relevante que tenga pieza
		// catalogada, añadimos su tarjeta (hasta 3, sin repetir entrada).
		$piece_map = array();
		$best_sim  = array(); // mejor similitud real (de texto) por post
		foreach ( $scored as $item ) {
			$pid = $item['post_id'];
			if ( ! empty( $item['is_component'] ) && ! empty( $item['render_data'] ) ) {
				$piece_map[ $pid ] = $item['render_data'];
			} elseif ( ! isset( $best_sim[ $pid ] ) || $item['similarity'] > $best_sim[ $pid ] ) {
				// Solo fragmentos de texto: mide cuán concreta es la pregunta
				// respecto a este contenido (el componente en sí puntúa poco).
				$best_sim[ $pid ] = $item['similarity'];
			}
		}

		// Las tarjetas solo salen cuando la pregunta es CONCRETA sobre esa
		// entrada (su contenido supera el umbral), no en preguntas genéricas.
		$pieces        = array();
		$seen          = array();
		$piece_min_sim = 0.33;
		foreach ( $results as $item ) {
			if ( count( $pieces ) >= 3 ) {
				break;
			}
			$pid = $item['post_id'];
			if ( isset( $seen[ $pid ] ) || ! isset( $piece_map[ $pid ] ) ) {
				continue;
			}
			if ( ! isset( $best_sim[ $pid ] ) || $best_sim[ $pid ] < $piece_min_sim ) {
				continue;
			}
			$seen[ $pid ] = true;
			$data = json_decode( $piece_map[ $pid ], true );
			if ( is_array( $data ) ) {
				$pieces[] = $data;
			}
		}

		return array( 'ok' => true, 'results' => $results, 'pieces' => $pieces );
	}

	/**
	 * Añade a cada fragmento ganador sus contiguos del mismo post.
	 *
	 * La pregunta suele parecerse al fragmento que la enuncia, no al que la
	 * responde: en una FAQ, "¿qué es X?" gana y "¿qué servicios ofrece?" —que
	 * está justo al lado y tiene el dato— se queda fuera. Arrastrar los vecinos
	 * recupera esa continuidad sin coste de IA.
	 *
	 * Cada ancla se emite con su vecino anterior y posterior, en orden de
	 * lectura, y las anclas se respetan por orden de score.
	 *
	 * @param array $scored  Todos los fragmentos puntuados.
	 * @param array $anchors Los top-N.
	 * @return array
	 */
	private function expand_neighbors( $scored, $anchors ) {
		// Índice post_id => chunk_index => fragmento.
		$by_post = array();
		foreach ( $scored as $item ) {
			$by_post[ $item['post_id'] ][ $item['chunk_index'] ] = $item;
		}

		$out  = array();
		$seen = array();

		foreach ( $anchors as $anchor ) {
			$idx = $anchor['chunk_index'];
			for ( $i = $idx - 1; $i <= $idx + 1; $i++ ) {
				if ( ! isset( $by_post[ $anchor['post_id'] ][ $i ] ) ) {
					continue;
				}
				$key = $anchor['post_id'] . ':' . $i;
				if ( isset( $seen[ $key ] ) ) {
					continue;
				}
				$seen[ $key ] = true;
				$out[]        = $by_post[ $anchor['post_id'] ][ $i ];
			}
		}

		return $out;
	}

	private function norm( $v ) {
		$sum = 0.0;
		foreach ( $v as $x ) {
			$sum += $x * $x;
		}
		return sqrt( $sum );
	}

	/**
	 * Coseno entre la consulta (con norma precalculada) y un vector.
	 */
	private function cosine( $qvec, $qnorm, $vec ) {
		$dot  = 0.0;
		$vsum = 0.0;
		$n    = min( count( $qvec ), count( $vec ) );
		for ( $i = 0; $i < $n; $i++ ) {
			$dot  += $qvec[ $i ] * $vec[ $i ];
			$vsum += $vec[ $i ] * $vec[ $i ];
		}
		$vnorm = sqrt( $vsum );
		if ( $vnorm <= 0 ) {
			return 0.0;
		}
		return $dot / ( $qnorm * $vnorm );
	}
}
