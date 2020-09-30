<?php
/**
 * Plugin Name: ACF CDT WPAI Patch
 * Plugin URI:  https://hookturn.io
 * Description: A temporary fix that patches imports for custom database table date when using WP All Import. Won't work with ACF CDT version 1.1 or later.
 * Version:     0.2
 * Author:      Phil Kurth
 * Author URI:  http://philkurth.com.au
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path: /languages
 * Text Domain: acfcdt-wp-all-import-patch
 */


use ACFCustomDatabaseTables\Coordinator\CoreMetadataCoordinator;
use ACFCustomDatabaseTables\Vendor\Pimple\Container;


// If this file is called directly, abort.
defined( 'WPINC' ) or die();


add_action( 'plugins_loaded', 'acfcdt_wp_all_import_patch_plugin' );


function acfcdt_wp_all_import_patch_plugin() {
	if ( function_exists( 'acf_custom_database_tables' ) ) {

		$plugin = acf_custom_database_tables();

		if ( ! method_exists( $plugin, 'container' ) ) {
			return null;
		}

		$container = $plugin->container();

		if ( ! $container instanceof Container ) {
			return null;
		}

		$version = $container['plugin_version'];

		// IF using version 1.1 or later, don't do anything.
		if ( $version and version_compare( $version, '1.1', '>=' ) ) {
			return null;
		}

		add_filter( 'pmxi_acf_custom_field', function ( $value, $pid, $name ) use ( $container ) {
			update_field( $name, $value, $pid );

			/** @var CoreMetadataCoordinator $coord */
			$coord = $container['coordinator.core_metadata'];
			if ( $coord->field_is_bypassed_from_core_meta_tables( "_$name" ) ) {
				delete_post_meta( $pid, "_$name" );
			}

			return $value;
		}, 10, 3 );
	}
}