<?php
/**
 * Fábrica de proveedores de chat.
 * Devuelve la implementación adecuada según los ajustes.
 * En la Fase 3 se añade aquí OpenAI.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class PDSWT_Provider_Factory {

	/**
	 * @param array $settings Ajustes del plugin (PDSWT_OPTION_KEY).
	 * @return PDSWT_Provider_Interface
	 */
	public static function make( $settings ) {
		$provider = isset( $settings['provider'] ) ? $settings['provider'] : 'claude';

		switch ( $provider ) {
			case 'openai':
				// TODO Fase 3: return new PDSWT_OpenAI_Provider( ... );
				// De momento cae a Claude como fallback seguro.
			case 'claude':
			default:
				return new PDSWT_Claude_Provider(
					isset( $settings['claude_api_key'] ) ? $settings['claude_api_key'] : '',
					isset( $settings['claude_model'] ) ? $settings['claude_model'] : 'claude-sonnet-5'
				);
		}
	}
}
