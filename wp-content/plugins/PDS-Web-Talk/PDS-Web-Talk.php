<?php
/**
 * Plugin Name:       PDS-Web-Talk
 * Plugin URI:        https://paladinidigital.com
 * Description:       Asistente conversacional con IA (RAG) sobre el contenido del site. Motor Claude (Anthropic), preparado para OpenAI. Sin dependencias de terceros.
 * Version:           0.5.7
 * Author:            Daniel PDS
 * Author URI:        https://paladinidigital.com
 * License:           GPL-2.0+
 * Text Domain:       pds-web-talk
 * Domain Path:       /languages
 */

// Abort if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin version y constantes base.
 */
define( 'PDSWT_VERSION', '0.5.7' );
define( 'PDSWT_PLUGIN_FILE', __FILE__ );
define( 'PDSWT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PDSWT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PDSWT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Nombre de la opción única donde se guardan los ajustes del plugin.
 */
define( 'PDSWT_OPTION_KEY', 'pdswt_settings' );

/**
 * Ficheros core.
 */
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-loader.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-i18n.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-activator.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-deactivator.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-cpt.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-chunker.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-usage.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-corpus-io.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-indexer.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-retriever.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-chat.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-rate-limiter.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-transcript.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt-rest.php';
require PDSWT_PLUGIN_DIR . 'includes/providers/interface-pdswt-provider.php';
require PDSWT_PLUGIN_DIR . 'includes/providers/class-pdswt-claude-provider.php';
require PDSWT_PLUGIN_DIR . 'includes/providers/class-pdswt-provider-factory.php';
require PDSWT_PLUGIN_DIR . 'includes/providers/class-pdswt-openai-embeddings.php';
require PDSWT_PLUGIN_DIR . 'includes/class-pdswt.php';
require PDSWT_PLUGIN_DIR . 'admin/class-pdswt-admin.php';
require PDSWT_PLUGIN_DIR . 'admin/class-pdswt-index-page.php';
require PDSWT_PLUGIN_DIR . 'admin/class-pdswt-usage-page.php';
require PDSWT_PLUGIN_DIR . 'admin/class-pdswt-piece-meta.php';
require PDSWT_PLUGIN_DIR . 'public/class-pdswt-frontend.php';

/**
 * Activación / desactivación.
 */
register_activation_hook( __FILE__, array( 'PDSWT_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PDSWT_Deactivator', 'deactivate' ) );

/**
 * Arranque del plugin.
 */
function pdswt_run() {
	$plugin = new PDSWT();
	$plugin->run();
}
pdswt_run();
