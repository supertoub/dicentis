<?php
/*
Plugin Name: dicentis Podcast
Plugin URI: http://
Description: Manage multiple podcasts with ease in one plugin
Version: 0.1dev
Author: Hans-Helge Buerger
Author URI: http://hanshelgebuerger.de

Copyright 2013 Hans-Helge Buerger (http://hanshelgebuerger.de)
License: GPL (http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt)
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('Dicentis') ) {
	class Dicentis {

		public function __construct() {
			// Load the plugin's translated strings
			add_action( 'init' , array( $this, 'load_localisation' ) );

			add_filter( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'add_menu') );

			// Initilize Settings
			require_once( sprintf("%s/classes/settings.php", dirname( __FILE__ ) ) );
			$Dicentis_Settings = new Dicentis_Settings();

			// Create CPT Podcast
			require_once( sprintf("%s/classes/post-type-podcast.php", dirname(__FILE__)) );
			$Dicentis_Podcast_CPT = new Dicentis_Podcast_CPT();
		} // END public function __construct()

		/**
		 * Hook into WP's admin_init hook and do some admin stuff
		 * 		1. reorder Podcast's submenu
		 */
		public function admin_init() {
			$this->menu_order();
		} // END public function admin_init()

		/**
		 * Add admin menu pages to podcast post type
		 */
		public function add_menu() {
			add_submenu_page(
				'edit.php?post_type=podcast', // add to podcast menu
				__( 'Dashboard' ),
				__( 'Dashboard' ),
				'edit_posts',
				'dicentis_dashboard',
				array( $this, 'dicentis_dashboard_page' )
			);
		} // END public function add_menu()

		public function dicentis_dashboard_page() {
			if ( !current_user_can('edit_post') ) {
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}

			// Render the dashboard template
			include( sprintf( "%s/templates/dashboard.php", dirname(__FILE__) ) );
		} // END public function dicentis_dashboard_page()

		/**
		 * create a custom menu order to display the dashboard
		 * menu always at the top of this post type
		 */
		public function menu_order() {
			// $submenu contains the order of the complete menu
			// i.e. top-level menus and submenus
			global $submenu;

			// Look for $find_page and the submenu $find_sub
			$find_page = 'edit.php?post_type=podcast';
			$find_sub  = 'Dashboard';

			// Loop thru $submenu until $find_page is found
			foreach ( $submenu as $page => $items ) :
				if ( $page == $find_page ) :
					// loop thru $find_page item and look for
					// $find_sub b/c we want to reorder it
					foreach ( $items as $id => $meta ) :
						if ( $meta[0] == $find_sub ) :
							// $find_sub is found so assing it to
							// first place (0-based) in sub-array and unset
							// its former entry.
							// Last but not least sort it again.
							// 'Dashboard' is now at the top of this submenu
							$submenu[$find_page][0] = $meta;
							unset( $submenu[$find_page][$id] );
							ksort( $submenu[$find_page] );
						endif;
					endforeach;
				endif;
			endforeach;
		} // END public function menu_order() 

		public function load_localisation() {
			load_plugin_textdomain( 'dicentis', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		} // END public function load_localisation()

		/**
		 * Activate the plugin
		 * this plugin needs at least WP v3.6
		 */
		public static function activate() {
			// If WP version is not > 3.6 this plugin dies and cannot be used
			if ( version_compare( get_bloginfo( 'version' ), '3.6', '<' ) ) {
				// deactivate plugin
				die( __( 'This Plugin requires WordPress version 3.6 or higher.', 'dicentis' ) );
			}

			// register deactivation hook only then plugin is activated
			// and not on every plugin load.
			register_deactivation_hook( __FILE__, array('Dicentis', 'deactivate') );
		} // END public static function activate()

		/**
		 * Deactivate the plugin
		 * @return [type] [description]
		 */
		public static function deactivate() {

		} // END public static function deactivate()

	}
}


if ( class_exists('Dicentis') ) {

	// Installation and uninstallation hooks
	register_activation_hook( __FILE__, array('Dicentis', 'activate') );

	$dicentis = new Dicentis();

	// Add a link to the settings page onto the plugin page
	if( isset( $dicentis ) ) {
		// Add the settings link to the plugin page
		function dicentis_settings_link( $links ) {
			$settings_link = __( '<a href="options-general.php?page=dicentis">Settings</a>', 'dicentis' );
			array_unshift( $links, $settings_link );
			return $links;
		}

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", 'dicentis_settings_link' );
	}
}

?>
