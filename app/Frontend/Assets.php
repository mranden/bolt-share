<?php

declare(strict_types=1);

namespace BoltShare\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Assets {

	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	public function enqueue(): void {
		if ( is_admin() ) {
			return;
		}

		/**
		 * Filter whether Bolt Share frontend assets should be enqueued.
		 *
		 * @param bool $should_enqueue Whether assets should load on the current request.
		 */
		if ( ! apply_filters( 'bolt_share_should_enqueue_assets', true ) ) {
			return;
		}

		$css_path = BOLT_SHARE_PATH . 'build/css/bolt-share-frontend.css';
		$js_path  = BOLT_SHARE_PATH . 'build/js/frontend.js';

		if ( ! is_readable( $css_path ) || ! is_readable( $js_path ) ) {
			return;
		}

		$version = BOLT_SHARE_VERSION;
		$handle  = 'bolt-share-frontend';

		wp_register_style(
			$handle,
			BOLT_SHARE_URL . 'build/css/bolt-share-frontend.css',
			[],
			$version
		);

		wp_register_script(
			$handle,
			BOLT_SHARE_URL . 'build/js/frontend.js',
			[],
			$version,
			true
		);

		wp_localize_script(
			$handle,
			'boltShareL10n',
			[
				'instagramCopied' => __( 'Link copied – open Instagram and paste it.', 'bolt-share' ),
			]
		);

		wp_enqueue_style( $handle );
		wp_enqueue_script( $handle );
	}
}
