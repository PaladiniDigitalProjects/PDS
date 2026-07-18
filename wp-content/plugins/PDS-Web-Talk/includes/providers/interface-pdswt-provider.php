<?php
/**
 * Contrato común para los proveedores de IA.
 * Permite intercambiar Claude / OpenAI sin tocar el resto del plugin.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

interface PDSWT_Provider_Interface {

	/**
	 * Envía una conversación al modelo y devuelve la respuesta de texto.
	 *
	 * @param string $system_prompt Instrucción de sistema (persona, idioma, límites).
	 * @param array  $messages      Lista de mensajes: [ ['role' => 'user'|'assistant', 'content' => '...'], ... ].
	 * @return array {
	 *     @type bool   $ok      true si hubo respuesta correcta.
	 *     @type string $content Texto de la respuesta (si ok).
	 *     @type string $error   Mensaje de error (si !ok).
	 * }
	 */
	public function chat( $system_prompt, $messages );

	/**
	 * Identificador legible del proveedor (para logs/UI).
	 *
	 * @return string
	 */
	public function get_label();
}
