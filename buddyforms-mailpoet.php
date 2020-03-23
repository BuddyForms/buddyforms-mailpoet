<?php

/**
 * Plugin Name: BuddyForms MailPoet
 * Plugin URI: https://themekraft.com/products/buddyforms-mailpoet/
 * Description: Let your users subscribe to MailPoet lists from BuddForms
 * Version: 1.0.0
 * Author: ThemeKraft
 * Author URI: https://themekraft.com/
 * License: GPLv2 or later
 * Network: false
 * Text Domain: bf-mailpoet
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class BuddyFormsMailPoet {
	/**
	 * @var string
	 */
	public static $version = '1.0.0';
	public static $include_assets = array();
	public static $slug = 'bf-mailpoet';

	/**
	 * Instance of this class
	 *
	 * @var $instance BuddyFormsMailPoet
	 */
	protected static $instance = null;

	/**
	 * Initiate the class
	 *
	 * @since 0.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ), 4, 1 );
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
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
		if ( self::is_buddy_form_active() ) {
			if ( self::is_mailpoet_active() ) {
				$freemius = self::get_freemius();
				if ( ! empty( $freemius ) && $freemius->is_paying_or_trial() ) {
					require_once BUDDYFORMS_MAILPOET_INCLUDES_PATH . 'form-elements.php';
				}
			} else {
				add_action( 'admin_notices', array( $this, 'need_mailpoet' ) );
			}
		} else {
			add_action( 'admin_notices', array( $this, 'need_buddyforms' ) );
		}
	}

	public function need_mailpoet() {
		self::admin_notice( '<b>Oops...</b> BuddyForms MailPoet cannot run without <a target="_blank" href="https://wordpress.org/plugins/mailpoet/" title="MailPoet">MailPoet</a>.' );
	}

	public function need_buddyforms() {
		self::admin_notice();
	}

	/**
	 * @return Freemius
	 */
	public static function get_freemius() {
		global $buddyforms_mailpoet_freemius;

		return $buddyforms_mailpoet_freemius;
	}

	/**
	 * Load the textdomain for the plugin
	 *
	 * @package buddyforms_mailpoet
	 * @since 0.1
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'bf-mailpoet', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	public static function load_plugins_dependency() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}

	public static function is_mailpoet_active() {
		self::load_plugins_dependency();

		return is_plugin_active( 'mailpoet/mailpoet.php' );
	}

	public static function is_buddy_form_active() {
		self::load_plugins_dependency();

		return is_plugin_active( 'buddyforms-premium/BuddyForms.php' );
	}

	public static function error_log( $message ) {
		if ( ! empty( $message ) ) {
			error_log( self::getSlug() . ' -- ' . $message );
		}
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	static function getVersion() {
		return self::$version;
	}

	/**
	 * Get plugins slug
	 *
	 * @return string
	 */
	static function getSlug() {
		return self::$slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function admin_notice( $html = '' ) {
		if ( empty( $html ) ) {
			$html = '<b>Oops...</b> BuddyForms MailPoet cannot run without <a target="_blank" href="https://themekraft.com/buddyforms/">BuddyForms</a>.';
		}
		?>
		<style>
			.buddyforms-notice label.buddyforms-title {
				background: rgba(0, 0, 0, 0.3);
				color: #fff;
				padding: 2px 10px;
				position: absolute;
				top: 100%;
				bottom: auto;
				right: auto;
				-moz-border-radius: 0 0 3px 3px;
				-webkit-border-radius: 0 0 3px 3px;
				border-radius: 0 0 3px 3px;
				left: 10px;
				font-size: 12px;
				font-weight: bold;
				cursor: auto;
			}

			.buddyforms-notice .buddyforms-notice-body {
				margin: .5em 0;
				padding: 2px;
			}

			.buddyforms-notice.buddyforms-title {
				margin-bottom: 30px !important;
			}

			.buddyforms-notice {
				position: relative;
			}
		</style>
		<div class="error buddyforms-notice buddyforms-title">
			<label class="buddyforms-title">BuddyForms MailPoet</label>
			<div class="buddyforms-notice-body">
				<?php echo $html; ?>
			</div>
		</div>
		<?php
	}

}


if ( ! function_exists( 'buddyforms_mailpoet_freemius' ) ) {
	// Create a helper function for easy SDK access.
	function buddyforms_mailpoet_freemius() {
		global $buddyforms_mailpoet_freemius;

		if ( ! isset( $buddyforms_mailpoet_freemius ) ) {
			// Include Freemius SDK.
			if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms/includes/resources/freemius/start.php';
			} else if ( file_exists( dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php' ) ) {
				// Try to load SDK from premium parent plugin folder.
				require_once dirname( dirname( __FILE__ ) ) . '/buddyforms-premium/includes/resources/freemius/start.php';
			}

			try {
				$buddyforms_mailpoet_freemius = fs_dynamic_init( array(
					'id'               => '5209',
					'slug'             => 'bf-mailpoet',
					'type'             => 'plugin',
					'public_key'       => 'pk_4b627ab41759a079601e2d8c576da',
					'is_premium'       => true,
					'is_premium_only'  => true,
					'has_paid_plans'   => true,
					'is_org_compliant' => false,
					'trial'            => array(
						'days'               => 14,
						'is_require_payment' => true,
					),
					'parent'           => array(
						'id'         => '391',
						'slug'       => 'buddyforms',
						'public_key' => 'pk_dea3d8c1c831caf06cfea10c7114c',
						'name'       => 'BuddyForms',
					),
					'menu'             => array(
						'support' => false,
					)
				) );
			} catch ( Freemius_Exception $e ) {
				return false;
			}
		}

		return $buddyforms_mailpoet_freemius;
	}
}

function buddyforms_mailpoet_freemius_is_parent_active_and_loaded() {
	// Check if the parent's init SDK method exists.
	return function_exists( 'buddyforms_core_fs' );
}

function buddyforms_mailpoet_freemius_is_parent_active() {
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

function buddyforms_mailpoet_need_buddyforms() {
	BuddyFormsMailPoet::admin_notice();
}

function buddyforms_mailpoet_freemius_init() {
	if ( buddyforms_mailpoet_freemius_is_parent_active_and_loaded() ) {
		// Init Freemius.
		buddyforms_mailpoet_freemius();


		// Signal that the add-on's SDK was initiated.
		do_action( 'buddyforms_mailpoet_freemius_loaded' );

		$GLOBALS['BuddyFormsMailPoet'] = BuddyFormsMailPoet::get_instance();

	} else {
		add_action( 'admin_notices', 'buddyforms_mailpoet_need_buddyforms' );
	}
}

if ( buddyforms_mailpoet_freemius_is_parent_active_and_loaded() ) {
	// If parent already included, init add-on.
	buddyforms_mailpoet_freemius_init();
} else if ( buddyforms_mailpoet_freemius_is_parent_active() ) {
	// Init add-on only after the parent is loaded.
	add_action( 'buddyforms_core_fs_loaded', 'buddyforms_mailpoet_freemius_init' );
} else {
	// Even though the parent is not activated, execute add-on for activation / uninstall hooks.
	buddyforms_mailpoet_freemius_init();
}
