<?php

/**
 * Plugin Name: BuddyForms MailPoet
 * Plugin URI: https://themekraft.com/products/buddyforms-mailpoet/
 * Description: Use BuddyForms with MailPoet
 * Version: 1.0.0
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/
 * License: GPLv2 or later
 * Network: false
 * Text Domain: buddyforms
 *
 *****************************************************************************
 *
 * This script is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ****************************************************************************
 */


class BuddyFormsMailPoet {
	/**
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Initiate the class
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action(
			'init',
			array( $this, 'includes' ),
			4,
			1
		);
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'buddyforms_admin_js_css_enqueue', array( $this, 'buddyforms_mailpoet_admin_js' ) );
		add_action(
			'init',
			array( $this, 'buddyforms_mailpoet_front_js_css_enqueue' ),
			2,
			1
		);
		$this->load_constants();
	}

	/**
	 * Defines constants needed throughout the plugin.
	 *
	 * These constants can be overridden in bp-custom.php or wp-config.php.
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	public function load_constants() {
		if ( ! defined( 'BUDDYFORMS_MAILPOET_PLUGIN_URL' ) ) {
			define( 'BUDDYFORMS_MAILPOET_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
		}
		if ( ! defined( 'BUDDYFORMS_MAILPOET_INSTALL_PATH' ) ) {
			define( 'BUDDYFORMS_MAILPOET_INSTALL_PATH', dirname( __FILE__ ) . '/' );
		}
		if ( ! defined( 'BUDDYFORMS_MAILPOET_INCLUDES_PATH' ) ) {
			define( 'BUDDYFORMS_MAILPOET_INCLUDES_PATH', BUDDYFORMS_MAILPOET_INSTALL_PATH . 'includes/' );
		}
		if ( ! defined( 'BUDDYFORMS_MAILPOET_TEMPLATE_PATH' ) ) {
			define( 'BUDDYFORMS_MAILPOET_TEMPLATE_PATH', BUDDYFORMS_MAILPOET_INSTALL_PATH . 'templates/' );
		}
	}

	/**
	 * Include files needed by BuddyForms
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	public function includes() {
		require_once BUDDYFORMS_MAILPOET_INCLUDES_PATH . 'form-elements.php';
	}

	/**
	 * Load the textdomain for the plugin
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'buddyforms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Enqueue the needed CSS for the admin screen
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	function buddyforms_mailpoet_admin_style( $hook_suffix ) {
	}

	/**
	 * Enqueue the needed JS for the admin screen
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	function buddyforms_mailpoet_admin_js( $hook_suffix ) {
		global $post;
		if ( isset( $post ) && $post->post_type == 'buddyforms' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' || isset( $post ) && $post->post_type == 'buddyforms' && $hook_suffix == 'post-new.php' || $hook_suffix == 'buddyforms_page_bf_add_ons' || $hook_suffix == 'buddyforms_page_bf_settings' ) {
			//wp_enqueue_script( 'buddyforms-mailpoet-form-builder-js', plugins_url( 'assets/admin/js/form-builder.js', __FILE__ ), array( 'jquery' ) );
		}
	}

	/**
	 * Enqueue the needed JS for the frontend
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	function buddyforms_mailpoet_front_js_css_enqueue() {
	}

}


if ( ! function_exists( 'buddyforms_mailpoet_fs' ) ) {
	// Create a helper function for easy SDK access.
	function buddyforms_mailpoet_fs() {
		global $buddyforms_mailpoet_fs;

		if ( ! isset( $buddyforms_mailpoet_fs ) ) {
			// Include Freemius SDK.
			if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php';
			} else if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from premium parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php';
			}

			$buddyforms_mailpoet_fs = fs_dynamic_init( array(
				'id'                  => '5209',
				'slug'                => 'bf-mailpoet',
				'type'                => 'plugin',
				'public_key'          => 'pk_4b627ab41759a079601e2d8c576da',
				'is_premium'          => true,
				'is_premium_only'     => true,
				'has_paid_plans'      => true,
				'is_org_compliant'    => false,
				'trial'               => array(
					'days'               => 14,
					'is_require_payment' => true,
				),
				'parent'              => array(
					'id'         => '391',
					'slug'       => 'buddyforms',
					'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
					'name'       => 'BuddyForms',
				),
				'menu'                => array(
					'support'        => false,
				)
			) );
		}

		return $buddyforms_mailpoet_fs;
	}
}

function buddyforms_mailpoet_fs_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function buddyforms_mailpoet_fs_is_parent_active() {
	$active_plugins = get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins         = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
	}

	foreach ( $active_plugins as $basename ) {
		if ( 0 === strpos( $basename, 'buddyforms/' ) ||
		     0 === strpos( $basename, 'buddyforms-premium/' )
		) {
			return true;
		}
	}

	return false;
}

function buddyforms_mailpoet_fs_init() {
	if ( buddyforms_mailpoet_fs_is_parent_active_and_loaded() ) {
		// Init Freemius.
		buddyforms_mailpoet_fs();


		// Signal that the add-on's SDK was initiated.
		do_action( 'buddyforms_mailpoet_fs_loaded' );

		$GLOBALS['BuddyFormsMailPoet'] = new BuddyFormsMailPoet();

	} else {
		// Parent is inactive, add your error handling here.
	}
}

if ( buddyforms_mailpoet_fs_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	buddyforms_mailpoet_fs_init();
} else if ( buddyforms_mailpoet_fs_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'buddyforms_mailpoet_fs_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	buddyforms_mailpoet_fs_init();
}
