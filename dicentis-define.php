<?php

if (!defined('DIPO_DB_Version')) {
	define('DIPO_DB_Version', 1);
}

if (!defined('DIPO_PLUGIN_NAME'))
	define('DIPO_PLUGIN_NAME',
		trim(dirname(plugin_basename(__FILE__)), '/'));

if (!defined('DIPO_TEXTDOMAIN'))
	define( 'DIPO_TEXTDOMAIN', DIPO_PLUGIN_NAME );

// Directories
if (!defined('DIPO_PLUGIN_DIR'))
	define('DIPO_PLUGIN_DIR', WP_PLUGIN_DIR . '/' .
		DIPO_PLUGIN_NAME);

if (!defined('DIPO_ASSETS_DIR'))
	define( 'DIPO_ASSETS_DIR', DIPO_PLUGIN_DIR . '/assets' );

if (!defined('DIPO_INC_DIR'))
	define( 'DIPO_INC_DIR', DIPO_PLUGIN_DIR . '/includes' );

if (!defined('DIPO_LIB_DIR'))
	define( 'DIPO_LIB_DIR', DIPO_INC_DIR . '/libraries' );

if (!defined('DIPO_TEMPLATES_DIR'))
	define( 'DIPO_TEMPLATES_DIR', DIPO_INC_DIR . '/templates' );

// URLs
if (!defined('DIPO_PLUGIN_URL'))
	define('DIPO_PLUGIN_URL', WP_PLUGIN_URL . '/' .
		DIPO_PLUGIN_NAME);

if (!defined('DIPO_ASSETS_URL'))
	define( 'DIPO_ASSETS_URL', DIPO_PLUGIN_URL . '/assets' );

