<?php

declare(strict_types=1);

namespace BoltShare\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BoltShare\Support\SvgIcons;

final class ShareShortcode {

	private static int $instance_counter = 0;

	/**
	 * @var list<string>
	 */
	private const ALLOWED_NETWORKS = [ 'facebook', 'instagram', 'email' ];

	private SvgIcons $svg_icons;

	/**
	 * @param Assets   $assets    Frontend assets service (constructed in Plugin for registration side effects).
	 * @param SvgIcons $svg_icons Trusted inline SVG allowlist.
	 */
	public function __construct( Assets $assets, SvgIcons $svg_icons ) {
		unset( $assets );

		$this->svg_icons = $svg_icons;

		add_shortcode( 'bolt_share', [ $this, 'render' ] );
	}

	public function render( $atts = [], ?string $content = null, string $tag = '' ): string {
		unset( $content, $tag );

		$defaults = $this->get_defaults();
		$atts     = shortcode_atts( $defaults, (array) $atts, 'bolt_share' );
		$atts     = $this->sanitize_attributes( $atts );

		$share_url   = $this->resolve_share_url( $atts['url'], $atts );
		$share_title = $this->resolve_share_title( $atts['title'], $atts );
		$networks    = $this->normalize_networks( $atts['networks'], $atts );

		return $this->render_template(
			[
				'instance_id'    => $this->get_panel_id(),
				'label'          => $atts['label'],
				'dropdown_title' => $atts['dropdown_title'],
				'extra_class'    => $atts['class'],
				'networks'       => $networks,
				'share_url'      => $share_url,
				'share_title'    => $share_title,
				'facebook_url'   => $this->build_facebook_url( $share_url ),
				'mailto_url'     => $this->build_mailto_url( $share_title, $share_url ),
				'icons'          => $this->svg_icons,
			]
		);
	}

	/**
	 * @return array<string, string>
	 */
	private function get_defaults(): array {
		$defaults = [
			'label'          => __( 'Del', 'bolt-share' ),
			'dropdown_title' => __( 'Del på...', 'bolt-share' ),
			'networks'       => 'facebook,instagram,email',
			'title'          => '',
			'url'            => '',
			'class'          => '',
		];

		/**
		 * Filter default shortcode attributes for [bolt_share].
		 *
		 * @param array<string, string> $defaults Default attribute values.
		 */
		return apply_filters( 'bolt_share_shortcode_defaults', $defaults );
	}

	/**
	 * @param array<string, string> $atts
	 *
	 * @return array<string, string>
	 */
	private function sanitize_attributes( array $atts ): array {
		$label = sanitize_text_field( $atts['label'] );
		if ( '' === $label ) {
			$label = __( 'Del', 'bolt-share' );
		}

		$dropdown_title = sanitize_text_field( $atts['dropdown_title'] );
		if ( '' === $dropdown_title ) {
			$dropdown_title = __( 'Del på...', 'bolt-share' );
		}

		$class_tokens = array_filter(
			preg_split( '/\s+/', trim( $atts['class'] ) ) ?: [],
			static fn( string $token ): bool => '' !== $token
		);

		$sanitized_classes = array_filter(
			array_map( 'sanitize_html_class', $class_tokens ),
			static fn( string $token ): bool => '' !== $token
		);

		return [
			'label'          => $label,
			'dropdown_title' => $dropdown_title,
			'networks'       => sanitize_text_field( $atts['networks'] ),
			'title'          => sanitize_text_field( $atts['title'] ),
			'url'            => esc_url_raw( $atts['url'] ),
			'class'          => implode( ' ', $sanitized_classes ),
		];
	}

	/**
	 * @param array<string, string> $atts
	 */
	private function resolve_share_url( string $url_attr, array $atts ): string {
		$url = '';

		if ( '' !== $url_attr ) {
			$url = esc_url_raw( $url_attr );
		}

		if ( '' === $url ) {
			if ( is_singular() ) {
				$url = (string) get_permalink( get_queried_object_id() );
			} else {
				$url = home_url( '/' );
			}
		}

		if ( '' === $url ) {
			$url = home_url( '/' );
		}

		/**
		 * Filter the resolved share URL for a shortcode instance.
		 *
		 * @param string               $url  Resolved share URL.
		 * @param array<string, string> $atts Sanitized shortcode attributes.
		 */
		$url = apply_filters( 'bolt_share_share_url', $url, $atts );

		$url = esc_url_raw( $url );

		if ( '' === $url ) {
			$url = home_url( '/' );
		}

		return $url;
	}

	/**
	 * @param array<string, string> $atts
	 */
	private function resolve_share_title( string $title_attr, array $atts ): string {
		if ( '' !== $title_attr ) {
			$title = $title_attr;
		} elseif ( is_singular() ) {
			$title = (string) get_the_title( get_queried_object_id() );
		} else {
			$title = (string) get_bloginfo( 'name' );
		}

		$title = wp_strip_all_tags( $title );
		$title = trim( $title );

		if ( '' === $title ) {
			$title = __( 'Share this page', 'bolt-share' );
		}

		/**
		 * Filter the resolved share title for a shortcode instance.
		 *
		 * @param string               $title Resolved share title.
		 * @param array<string, string> $atts  Sanitized shortcode attributes.
		 */
		$title = apply_filters( 'bolt_share_share_title', $title, $atts );

		$title = wp_strip_all_tags( $title );
		$title = trim( $title );

		if ( '' === $title ) {
			$title = __( 'Share this page', 'bolt-share' );
		}

		return $title;
	}

	/**
	 * @param array<string, string> $atts
	 *
	 * @return list<string>
	 */
	private function normalize_networks( string $networks, array $atts ): array {
		$requested = array_filter(
			array_map( 'trim', explode( ',', $networks ) ),
			static fn( string $network ): bool => '' !== $network
		);

		$normalized = [];

		foreach ( $requested as $network ) {
			$network = strtolower( sanitize_key( $network ) );

			if ( ! in_array( $network, self::ALLOWED_NETWORKS, true ) ) {
				continue;
			}

			if ( in_array( $network, $normalized, true ) ) {
				continue;
			}

			$normalized[] = $network;
		}

		/**
		 * Filter the normalized network list for a shortcode instance.
		 *
		 * @param list<string>         $normalized Allowlisted network slugs.
		 * @param array<string, string> $atts    Sanitized shortcode attributes.
		 */
		$normalized = apply_filters( 'bolt_share_networks', $normalized, $atts );

		return array_values(
			array_filter(
				$normalized,
				static fn( string $network ): bool => in_array( $network, self::ALLOWED_NETWORKS, true )
			)
		);
	}

	private function build_facebook_url( string $share_url ): string {
		return 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $share_url );
	}

	private function build_mailto_url( string $share_title, string $share_url ): string {
		return 'mailto:?subject=' . rawurlencode( $share_title ) . '&body=' . rawurlencode( $share_url );
	}

	private function get_panel_id(): string {
		++self::$instance_counter;

		return 'bolt-share-panel-' . self::$instance_counter;
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function render_template( array $data ): string {
		$template = BOLT_SHARE_PATH . 'resources/templates/share.php';

		if ( ! is_readable( $template ) ) {
			return '';
		}

		ob_start();

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Explicit template contract.
		extract( $data, EXTR_SKIP );

		include $template;

		return (string) ob_get_clean();
	}
}
