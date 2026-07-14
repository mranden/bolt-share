<?php
/**
 * Share shortcode template.
 *
 * @package BoltShare
 *
 * @var string              $instance_id
 * @var string              $label
 * @var string              $dropdown_title
 * @var string              $extra_class
 * @var list<string>        $networks
 * @var string              $share_url
 * @var string              $share_title
 * @var string              $facebook_url
 * @var string              $mailto_url
 * @var BoltShare\Support\SvgIcons $icons
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$root_classes = trim( 'bolt-share ' . $extra_class );
?>
<div class="<?php echo esc_attr( $root_classes ); ?>" data-bolt-share>
	<button
		type="button"
		class="bolt-share__trigger"
		aria-expanded="false"
		aria-controls="<?php echo esc_attr( $instance_id ); ?>"
	>
		<span class="bolt-share__trigger-label"><?php echo esc_html( $label ); ?></span>
		<?php echo $icons->get( 'share' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted allowlist SVG. ?>
	</button>

	<div
		class="bolt-share__panel"
		id="<?php echo esc_attr( $instance_id ); ?>"
		data-bolt-share-panel
		hidden
	>
		<p class="bolt-share__title"><?php echo esc_html( $dropdown_title ); ?></p>

		<ul class="bolt-share__list">
			<?php foreach ( $networks as $network ) : ?>
				<?php if ( 'facebook' === $network ) : ?>
					<li class="bolt-share__item">
						<a
							class="bolt-share__action"
							href="<?php echo esc_url( $facebook_url ); ?>"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php echo $icons->get( 'facebook' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted allowlist SVG. ?>
							<span class="bolt-share__action-label"><?php echo esc_html__( 'Facebook', 'bolt-share' ); ?></span>
						</a>
					</li>
				<?php elseif ( 'instagram' === $network ) : ?>
					<li class="bolt-share__item">
						<button
							type="button"
							class="bolt-share__action"
							data-bolt-share-instagram
							data-share-url="<?php echo esc_attr( $share_url ); ?>"
							data-share-title="<?php echo esc_attr( $share_title ); ?>"
						>
							<?php echo $icons->get( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted allowlist SVG. ?>
							<span class="bolt-share__action-label"><?php echo esc_html__( 'Instagram', 'bolt-share' ); ?></span>
						</button>
					</li>
				<?php elseif ( 'email' === $network ) : ?>
					<li class="bolt-share__item">
						<a class="bolt-share__action" href="<?php echo esc_url( $mailto_url ); ?>">
							<?php echo $icons->get( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted allowlist SVG. ?>
							<span class="bolt-share__action-label"><?php echo esc_html__( 'E-mail', 'bolt-share' ); ?></span>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<p class="bolt-share__status" data-bolt-share-status aria-live="polite"></p>
	</div>
</div>
