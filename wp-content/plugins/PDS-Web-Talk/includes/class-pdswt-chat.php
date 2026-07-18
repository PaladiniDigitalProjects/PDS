<?php
/**
 * Orquestador de chat con RAG: recupera contexto del corpus, construye el prompt
 * y pide la respuesta al proveedor. Respeta la visibilidad de cada fuente.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Chat {

	private $settings;

	public function __construct( $settings = null ) {
		$this->settings = is_array( $settings ) ? $settings : PDSWT::get_settings();
	}

	/**
	 * @param string $message  Pregunta del usuario.
	 * @param array  $history  Turnos previos [ ['role'=>'user'|'assistant','content'=>..], ... ].
	 * @return array { ok, reply, sources:[{title,link}], error }
	 */
	public function answer( $message, $history = array() ) {
		$retriever = new PDSWT_Retriever( $this->settings );
		$ret       = $retriever->retrieve( $message );
		if ( empty( $ret['ok'] ) ) {
			return array( 'ok' => false, 'error' => $ret['error'] );
		}

		// Las piezas visuales se muestran como tarjetas, no como texto de contexto.
		$text_results = array();
		foreach ( $ret['results'] as $r ) {
			if ( empty( $r['is_component'] ) ) {
				$text_results[] = $r;
			}
		}

		$context = $this->build_context( $text_results );
		$system  = $this->build_system_prompt( $context, $message );

		$messages   = is_array( $history ) ? $history : array();
		$messages[] = array( 'role' => 'user', 'content' => (string) $message );

		$provider = PDSWT_Provider_Factory::make( $this->settings );
		$result   = $provider->chat( $system, $messages );
		if ( empty( $result['ok'] ) ) {
			return array( 'ok' => false, 'error' => $result['error'] );
		}

		return array(
			'ok'      => true,
			'reply'   => $result['content'],
			'sources' => $this->public_sources( $text_results ),
			'pieces'  => isset( $ret['pieces'] ) ? $ret['pieces'] : array(),
		);
	}

	/**
	 * Construye el bloque de contexto. Las fuentes públicas llevan título+enlace
	 * (el bot puede citarlas); las ocultas van sin fuente (uso silencioso).
	 */
	private function build_context( $results ) {
		if ( empty( $results ) ) {
			return '';
		}
		// Etiquetas en inglés y neutras: el contexto no debe inyectar señal de
		// idioma (evita empujar al modelo hacia el castellano de las etiquetas).
		$parts = array();
		foreach ( $results as $r ) {
			if ( 'shown' === $r['visibility'] ) {
				$parts[] = "[SOURCE] " . $r['title'] . "\n[URL] " . $r['link'] . "\n[CONTENT]\n" . $r['text'];
			} else {
				$parts[] = "[INTERNAL — do not cite]\n[CONTENT]\n" . $r['text'];
			}
		}
		return implode( "\n\n─────\n\n", $parts );
	}

	private function build_system_prompt( $context, $message = '' ) {
		$base = ! empty( $this->settings['system_prompt'] ) ? $this->settings['system_prompt'] : '';

		$rules = "\n\n" .
			"Usa EXCLUSIVAMENTE la información del CONTEXTO de abajo para responder. " .
			"Si la respuesta no está en el contexto, dilo con honestidad; no inventes datos ni URLs. " .
			"Responde de forma natural, BREVE y PROPOSITIVA: 1-2 frases, al grano y orientadas a la acción. " .
			"NO uses markdown: nada de asteriscos, almohadillas ni guiones de lista. Si enumeras, hazlo dentro de una frase separando con comas o punto y coma. " .
			"TERMINA SIEMPRE con un salto de línea y, en una línea aparte, UNA sola pregunta de seguimiento concreta y propositiva, basada en tu respuesta, que ayude a la persona a concretar su necesidad o a dar el siguiente paso. " .
			"NO escribas etiquetas como [SOURCE], [URL] o [CONTENT] ni pegues URLs en tu respuesta: las fuentes se muestran aparte automáticamente. " .
			"El contenido marcado [INTERNAL] úsalo para responder pero NO lo cites ni reveles que existe.";

		$ctx = "\n\n===== CONTEXT =====\n" . ( '' !== $context ? $context : '(no relevant results)' ) . "\n===== END CONTEXT =====";

		$lang = $this->language_directive( $message );

		return $base . $rules . $ctx . $lang;
	}

	/**
	 * Orden de idioma determinista: detecta el idioma del mensaje en el servidor
	 * y da la instrucción en ese mismo idioma (ancla al modelo mucho mejor que
	 * pedirle que lo detecte, dado que el resto del prompt está en castellano).
	 */
	private function language_directive( $message ) {
		$lang = $this->detect_language( (string) $message );

		$map = array(
			'en' => "\n\n═══ LANGUAGE — CRITICAL ═══\n" .
				"The user's message is in ENGLISH. Write your ENTIRE reply in English, including the follow-up question. " .
				"The context is in English but the instructions above are in Spanish — ignore that: your output MUST be English. Never switch languages.",
			'ca' => "\n\n═══ IDIOMA — CRÍTIC ═══\n" .
				"El missatge de l'usuari és en CATALÀ. Escriu TOTA la teva resposta en català, inclosa la pregunta de seguiment. No canviïs mai d'idioma.",
			'es' => "\n\n═══ IDIOMA — CRÍTICO ═══\n" .
				"El mensaje del usuario está en CASTELLANO. Escribe TODA tu respuesta en castellano, incluida la pregunta de seguimiento. No cambies nunca de idioma.",
		);

		if ( isset( $map[ $lang ] ) ) {
			return $map[ $lang ];
		}

		// Idioma no concluyente: instrucción genérica.
		return "\n\n═══ LANGUAGE ═══\nReply in the EXACT language of the user's last message, including the follow-up question. Never switch languages, regardless of the language of these instructions or the context.";
	}

	/**
	 * Detección de idioma por palabras funcionales. Distingue en / es / ca;
	 * devuelve null si no hay señal suficiente.
	 */
	private function detect_language( $text ) {
		$t = ' ' . mb_strtolower( trim( $text ) ) . ' ';
		if ( ' ' === $t ) {
			return null;
		}

		$sw = array(
			'en' => array( ' the ', ' you ', ' do ', ' does ', ' with ', ' for ', ' are ', ' is ', ' what ', ' how ', ' can ', ' we ', ' your ', ' our ', ' and ', ' of ', ' this ', ' help ', ' offer ', ' work ', ' who ', ' where ' ),
			'es' => array( ' el ', ' la ', ' los ', ' las ', ' con ', ' para ', ' que ', ' qué ', ' cómo ', ' una ', ' más ', ' está ', ' según ', ' trabajáis ', ' empresas ', ' ustedes ', ' vuestro ', ' hacéis ', ' sois ', ' dónde ' ),
			'ca' => array( ' els ', ' les ', ' amb ', ' què ', ' com ', ' una ', ' més ', ' està ', ' segons ', ' treballeu ', ' empreses ', ' vostre ', ' feu ', ' volem ', ' nostra ', ' aquesta ', ' sou ', ' on ' ),
		);

		$score = array( 'en' => 0, 'es' => 0, 'ca' => 0 );
		foreach ( $sw as $lg => $words ) {
			foreach ( $words as $w ) {
				$score[ $lg ] += substr_count( $t, $w );
			}
		}
		if ( preg_match( '/[¿¡]/u', $t ) ) { $score['es'] += 1; }
		if ( preg_match( '/·|l·l|ç/u', $t ) ) { $score['ca'] += 1; }

		arsort( $score );
		$vals = array_values( $score );
		if ( 0 === $vals[0] || $vals[0] === $vals[1] ) {
			return null; // sin señal o empate
		}
		return array_key_first( $score );
	}

	/**
	 * Fuentes públicas únicas (para mostrarlas bajo la respuesta).
	 */
	private function public_sources( $results ) {
		$seen    = array();
		$sources = array();
		foreach ( $results as $r ) {
			if ( 'shown' !== $r['visibility'] ) {
				continue;
			}
			if ( isset( $seen[ $r['post_id'] ] ) ) {
				continue;
			}
			$seen[ $r['post_id'] ] = true;
			$sources[]             = array( 'title' => $r['title'], 'link' => $r['link'] );
		}
		return $sources;
	}
}
