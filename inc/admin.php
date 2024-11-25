<?php
/**
 * WordPress Admin UI
 *
 * @package typesense
 */

namespace Typesense;

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'options-general.php' === $hook ) {
			wp_register_style( 'admin-typesense', plugin_uri( '/css/admin-typesense.css' ), false, plugin_version() );
			wp_enqueue_script( 'admin-typesense', plugin_uri( '/js/admin-typesense.js' ), array( 'jquery' ), plugin_version(), true );
			wp_enqueue_style( 'admin-typesense' );
		}
	}
);
