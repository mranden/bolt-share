=== Bolt Share ===
Contributors: bolt
Tags: share, social, shortcode, facebook, instagram, email
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight WordPress share shortcode with Facebook, Instagram, and e-mail options.

== Description ==

Bolt Share renders a minimal share trigger and accessible dropdown via the `[bolt_share]` shortcode.

Features:

* Minimal text trigger with inline share icon (default label: **Del**)
* Dropdown with Facebook, Instagram, and e-mail options
* Facebook and e-mail work without JavaScript
* Instagram uses honest progressive enhancement (native share or copy-link fallback)
* Multiple shortcode instances per page
* Keyboard accessible (Escape closes panel and restores focus)
* Translatable strings via the `bolt-share` text domain

Default networks: Facebook, Instagram, e-mail.

== Installation ==

1. Upload the `bolt-share` folder to `/wp-content/plugins/`.
2. If the release package does not include `vendor/`, run `composer install --no-dev` in the plugin directory.
3. If you modify frontend source files, run `npm install` and `npm run build` so `build/` contains compiled assets.
4. Activate the plugin through the **Plugins** screen in WordPress.
5. Add `[bolt_share]` to a post, page, or any shortcode-capable area.

== Usage ==

Basic shortcode:

`[bolt_share]`

Full example with attributes:

`[bolt_share label="Del" dropdown_title="Del på..." networks="facebook,instagram,email" title="Se denne side" url="https://example.com/example/" class="my-custom-share"]`

= Attributes =

* `label` — Trigger button text. Default: `Del`
* `dropdown_title` — Heading inside the dropdown. Default: `Del på...`
* `networks` — Comma-separated allowlist: `facebook`, `instagram`, `email`. Default: all three
* `title` — Share title and e-mail subject. Default: current page title (or site name)
* `url` — URL to share. Default: current singular permalink (or home URL)
* `class` — Additional CSS classes on the component root (sanitized)

Unknown attributes are ignored. Unknown network names are removed. Duplicate networks are deduplicated.

= Styling =

Set trigger text and icon color with one CSS variable on `.bolt-share`:

`.bolt-share { --bolt-share-color: #0066cc; }`

Or per instance via the `class` attribute and theme CSS:

`.my-share { --bolt-share-color: var(--your-theme-accent); }`

`[bolt_share class="my-share"]`

The variable also tints dropdown labels and icons. Focus outline uses the same color by default.

= Examples =

Facebook and e-mail only:

`[bolt_share networks="facebook,email"]`

Custom share URL and title:

`[bolt_share title="Check this out" url="https://example.com/promo/"]`

== Development ==

Requirements:

* PHP 8.0+
* [Composer](https://getcomposer.org/)
* [Node.js](https://nodejs.org/) (for asset builds only)

Setup:

1. `composer install`
2. `npm install`
3. `npm run build`

Watch during development:

`npm run watch`

Compiled assets (committed for release):

* `build/css/frontend.css`
* `build/js/frontend.js`

PHP lives under `app/` with PSR-4 namespace `BoltShare\`. Source assets live in `src/scss/` and `src/js/`.

Generate a translation template (requires [WP-CLI](https://wp-cli.org/) and the i18n command):

`wp i18n make-pot . languages/bolt-share.pot --domain=bolt-share`

== Frequently Asked Questions ==

= Does Instagram share directly to my account? =

No. Websites cannot reliably open a direct “post to Instagram” destination. When you choose Instagram, the plugin either opens your browser or operating system share sheet (which may include Instagram among other apps) or copies the page link so you can paste it into Instagram manually.

= Does the plugin track shares? =

No. V1 has no analytics or tracking.

= Does Facebook require an App ID? =

No. Facebook sharing uses the standard sharer URL with your page link.

= Will the dropdown work if JavaScript is disabled? =

Facebook and e-mail links remain usable in the markup, but the dropdown toggle and Instagram action require JavaScript.

= Can I add Twitter, LinkedIn, or other networks? =

Not in V1. Only `facebook`, `instagram`, and `email` are supported.

= Can I enqueue assets only on pages that use the shortcode? =

By default, assets load on all public frontend requests for reliability in page builders and widgets. Use the `bolt_share_should_enqueue_assets` filter to change this.

== Screenshots ==

1. Minimal share trigger with dropdown open.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release of Bolt Share.
