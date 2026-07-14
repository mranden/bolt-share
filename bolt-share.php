<?php
/**
 * Plugin Name:       Bolt Share
 * Plugin URI:        https://github.com/bolt/bolt-share
 * Description:       Lightweight share shortcode with Facebook, Instagram, and e-mail options.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Bolt
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       bolt-share
 *
 * @package BoltShare
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BOLT_SHARE_VERSION', '1.0.1' );
define( 'BOLT_SHARE_FILE', __FILE__ );
define( 'BOLT_SHARE_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOLT_SHARE_URL', plugin_dir_url( __FILE__ ) );

$autoload = BOLT_SHARE_PATH . 'vendor/autoload.php';

if ( ! is_readable( $autoload ) ) {
	add_action(
		'admin_notices',
		static function (): void {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__(
					'Bolt Share requires Composer dependencies. Run composer install in the plugin directory.',
					'bolt-share'
				)
			);
		}
	);

	return;
}

require $autoload;

add_action(
	'plugins_loaded',
	static function (): void {
		load_plugin_textdomain(
			'bolt-share',
			false,
			dirname( plugin_basename( BOLT_SHARE_FILE ) ) . '/languages'
		);

		new BoltShare\Plugin();
	}
);
