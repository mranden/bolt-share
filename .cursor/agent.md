# Bolt Share — AI agent handbook

Use this file as the primary architectural context when building or extending the **Bolt Share** WordPress plugin.

The plugin is intentionally small, but it must still be production-ready, accessible, namespaced, easy to extend, and consistent with the Bolt-style plugin conventions.

---

## 1. Project definition

### Purpose

Bolt Share provides a lightweight WordPress shortcode that renders a share trigger matching the supplied design reference:

- A minimal text button containing **“Del” / “Share”** and an inline share SVG icon.
- Clicking the trigger opens a small dropdown directly below it.
- The dropdown contains a heading such as **“Del på…”** and share choices for:
  - Facebook
  - Instagram
  - E-mail
- Each share choice displays written text and an inline SVG icon.
- The component must support more than one shortcode instance on the same page.

### V1 boundaries

V1 is a shortcode-based frontend plugin only.

V1 must not include:

- A Gutenberg block.
- A settings page.
- Tracking or analytics.
- Third-party icon packages.
- jQuery.
- Social-network SDKs.
- User accounts, database tables, post meta, options, or uninstall cleanup.
- Direct publishing to a user’s Instagram account.

---

## 2. Fixed project conventions

| Concern | Convention |
|---|---|
| Plugin name | Bolt Share |
| Plugin slug | `bolt-share` |
| Main file | `bolt-share.php` |
| Namespace | `BoltShare\` |
| Text domain | `bolt-share` |
| Shortcode | `[bolt_share]` |
| PHP prefix | `bolt_share_` |
| CSS prefix | `.bolt-share` |
| JS root selector | `[data-bolt-share]` |
| Build tool | npm scripts using Sass and esbuild |
| Minimum PHP | PHP 8.0 |
| WordPress target | Current supported WordPress versions |
| JavaScript | Vanilla JavaScript, bundled by esbuild |
| Styling | SCSS compiled to `build/css/frontend.css` |

Do not rename these conventions unless the user explicitly requests it. A rename must be complete across PHP, Composer, npm, CSS, JavaScript, documentation, hooks, filters, and translations.

---

## 3. Recommended folder structure

```text
bolt-share/
├── bolt-share.php
├── composer.json
├── package.json
├── .gitignore
├── readme.txt
├── app/
│   ├── Plugin.php
│   ├── Frontend/
│   │   ├── Assets.php
│   │   └── ShareShortcode.php
│   └── Support/
│       └── SvgIcons.php
├── resources/
│   └── templates/
│       └── share.php
├── src/
│   ├── js/
│   │   └── frontend.js
│   └── scss/
│       └── frontend.scss
├── build/
│   ├── js/
│   │   └── frontend.js
│   └── css/
│       └── frontend.css
├── languages/
└── docs/
    └── technical-plan.md
```

Keep the architecture proportionate. Do not add a service container, framework, router, REST API, AJAX layer, admin architecture, or model layer unless a later feature genuinely requires it.

---

## 4. PHP architecture

### `bolt-share.php`

The main plugin file is thin bootstrap glue only.

Its responsibilities are:

1. Declare the WordPress plugin header.
2. Prevent direct access.
3. Define:
   - `BOLT_SHARE_VERSION`
   - `BOLT_SHARE_FILE`
   - `BOLT_SHARE_PATH`
   - `BOLT_SHARE_URL`
4. Require `vendor/autoload.php`.
5. Show an admin notice if Composer dependencies are missing.
6. Load translations.
7. Instantiate `BoltShare\Plugin` on `plugins_loaded`.

Do not place shortcode markup, SVG strings, asset registration, share-link generation, or frontend logic in the bootstrap file.

### `app/Plugin.php`

The root class wires the plugin services:

```php
public function __construct() {
    $assets = new Frontend\Assets();

    new Frontend\ShareShortcode(
        $assets,
        new Support\SvgIcons()
    );
}
```

Simple constructor injection is preferred where it makes dependencies explicit. Do not add a dependency injection container.

### `app/Frontend/Assets.php`

Responsibilities:

- Register the compiled frontend CSS and JavaScript.
- Enqueue the assets reliably on the frontend.
- Use stable handles:
  - `bolt-share-frontend`
- Use `BOLT_SHARE_VERSION` for versioning.
- Load JavaScript in the footer.
- Never enqueue assets in wp-admin.
- Permit an extension filter:

```php
apply_filters( 'bolt_share_should_enqueue_assets', true )
```

For V1, the assets may be loaded on all public frontend requests. The files are tiny and this is more reliable than shortcode detection inside builders, widgets, templates, and dynamically rendered content.

### `app/Frontend/ShareShortcode.php`

Responsibilities:

- Register `[bolt_share]`.
- Parse and sanitize shortcode attributes.
- Resolve the share URL and title.
- Normalize the requested network list.
- Build safe Facebook and e-mail URLs.
- Prepare template data.
- Generate a unique panel ID for every component instance.
- Render `resources/templates/share.php`.
- Return markup; never echo directly from the shortcode callback.

The class must not contain a large HTML string.

### `app/Support/SvgIcons.php`

Responsibilities:

- Store the small, trusted inline SVG definitions used by the plugin.
- Return only icons from an allowlist.
- Support:
  - `share`
  - `facebook`
  - `instagram`
  - `email`
- Add `aria-hidden="true"` and `focusable="false"` to decorative icons.
- Do not accept arbitrary SVG markup from shortcode attributes or filters.

### `resources/templates/share.php`

The template receives an explicit array of already prepared variables.

It contains markup only, with minimal conditionals. Escape every dynamic value in the correct context.

---

## 5. Shortcode contract

### Basic use

```text
[bolt_share]
```

### Full example

```text
[bolt_share
    label="Del"
    dropdown_title="Del på..."
    networks="facebook,instagram,email"
    title="Se denne side"
    url="https://example.com/example/"
    class="my-custom-share"
]
```

### Supported attributes

| Attribute | Default | Behavior |
|---|---|---|
| `label` | `Del` | Visible trigger text |
| `dropdown_title` | `Del på...` | Heading inside the dropdown |
| `networks` | `facebook,instagram,email` | Comma-separated allowlisted choices |
| `title` | Current post/page title | Share title and e-mail subject |
| `url` | Current singular permalink | URL being shared |
| `class` | Empty | Additional sanitized CSS classes |

Rules:

- Unknown attributes are ignored by `shortcode_atts()`.
- Unknown networks are discarded.
- Duplicate networks are removed while preserving order.
- Empty labels fall back to the translated default.
- A custom URL must be sanitized with `esc_url_raw()`.
- If no reliable current permalink exists, fall back to `home_url( '/' )`.
- The visible output must be escaped again in the template.
- `class` supports one or more class tokens sanitized with `sanitize_html_class()`.

### Suggested filters

```php
bolt_share_shortcode_defaults
bolt_share_share_url
bolt_share_share_title
bolt_share_networks
bolt_share_should_enqueue_assets
```

Every filter must be documented with a PHPDoc block describing arguments and return type.

---

## 6. Share behavior

### Facebook

Create an encoded Facebook sharing URL for the resolved page URL and open it in a new tab/window.

Requirements:

- Use `target="_blank"`.
- Include `rel="noopener noreferrer"`.
- Encode the shared URL with `rawurlencode()`.
- Do not load the Facebook JavaScript SDK.
- Do not require a Facebook App ID in V1.

### E-mail

Create a `mailto:` URL containing:

- Encoded subject from the resolved share title.
- Encoded body containing the resolved share URL.

The template must use the already prepared safe `mailto:` value.

### Instagram

A normal website cannot guarantee a direct “share this link to Instagram” destination in the same way as Facebook or e-mail.

The Instagram option therefore uses progressive enhancement:

1. On click, call `navigator.share()` with `title`, optional text, and URL when available.
2. The browser or operating system decides which installed share targets are shown.
3. If native sharing is unavailable or fails for a non-cancellation reason:
   - Copy the URL using the Clipboard API when available.
   - Fall back to a temporary textarea and `document.execCommand( 'copy' )` only as a compatibility fallback.
   - Show a visible and screen-reader-readable status:
     - `Link copied – open Instagram and paste it.`
4. If the user cancels the native share sheet, do not show an error.
5. Never claim that Instagram was opened or that the content was published.

The Instagram control is a `<button type="button">`, not a fake link.

---

## 7. Accessible markup contract

Recommended structure:

```html
<div class="bolt-share" data-bolt-share>
    <button
        class="bolt-share__trigger"
        type="button"
        aria-expanded="false"
        aria-controls="bolt-share-panel-1"
    >
        <span class="bolt-share__trigger-label">Del</span>
        <!-- inline share SVG -->
    </button>

    <div
        class="bolt-share__panel"
        id="bolt-share-panel-1"
        data-bolt-share-panel
        hidden
    >
        <p class="bolt-share__title">Del på...</p>

        <ul class="bolt-share__list">
            <!-- network actions -->
        </ul>

        <p
            class="bolt-share__status"
            data-bolt-share-status
            aria-live="polite"
        ></p>
    </div>
</div>
```

Interaction requirements:

- Trigger is a real button.
- `aria-expanded` always matches the panel state.
- `aria-controls` references a unique ID.
- Opening does not unnecessarily move focus.
- `Escape` closes the open panel and returns focus to its trigger.
- Clicking outside closes an open panel.
- Clicking the same trigger toggles its panel.
- Opening one instance closes other instances.
- Tab order remains natural.
- Hidden panels use the `hidden` attribute.
- Visible focus styles must not be removed.
- Status feedback uses `aria-live="polite"`.
- The component remains understandable without icons.

Do not apply `role="menu"` or menu-item keyboard behavior. These are ordinary share actions, not an application menu.

---

## 8. Visual direction

The supplied reference shows a very minimal horizontal trigger:

- White or transparent background.
- Dark navy/teal text and icon.
- Short uppercase or compact label.
- Text on the left.
- Share symbol on the right.
- Generous space between label and icon.
- No heavy border or filled button treatment.
- The icon is visually prominent but remains aligned with the text.

Recommended V1 design:

```text
Trigger:
- display: inline-flex
- align-items: center
- gap: 0.65rem
- min-height: 44px
- padding: 0.4rem 0
- transparent background
- no border
- inherited font family
- font-weight: 500–600
- letter-spacing: subtle
- color: #0f3442
- icon: approximately 26 × 26px

Dropdown:
- position: absolute
- below and left-aligned with trigger
- min-width: approximately 220px
- white background
- subtle border and shadow
- compact rounded corners
- safe z-index
- links/buttons use full-width rows
- icon and text aligned consistently
```

Use CSS custom properties on `.bolt-share` so a theme can override color, background, border, shadow, radius, width, and spacing without editing plugin source.

On narrow screens, constrain the panel to the viewport:

```scss
max-width: calc(100vw - 2rem);
```

Respect `prefers-reduced-motion: reduce`.

Do not hardcode theme typography beyond safe inherited defaults.

---

## 9. JavaScript architecture

Use one vanilla JavaScript entry: `src/js/frontend.js`.

Requirements:

- No jQuery.
- Initialize all `[data-bolt-share]` roots.
- Prevent duplicate initialization with a data flag or `WeakSet`.
- Use event listeners scoped to each instance.
- Keep helpers small:
  - `openPanel()`
  - `closePanel()`
  - `closeOtherPanels()`
  - `shareToInstagram()`
  - `copyUrl()`
  - `setStatus()`
- Use data attributes for:
  - root
  - panel
  - Instagram action
  - share URL
  - share title
  - status
- Do not inject untrusted HTML.
- Use `textContent` for status messages.
- Handle `AbortError` from `navigator.share()` as a user cancellation.
- Do not log noisy errors in production.
- The JS bundle must work with multiple shortcode instances.

The component should still expose normal Facebook and e-mail links if JavaScript fails. Only dropdown toggling and Instagram progressive enhancement depend on JavaScript.

---

## 10. SCSS and build pipeline

Use simple npm scripts rather than Gulp or a custom webpack configuration.

### Development dependencies

- `sass`
- `esbuild`
- `concurrently`

### Expected scripts

```json
{
  "scripts": {
    "clean": "node -e \"require('fs').rmSync('build', { recursive: true, force: true })\"",
    "build": "npm run clean && npm run build:css && npm run build:js",
    "build:css": "sass src/scss/frontend.scss build/css/frontend.css --style=compressed --no-source-map",
    "build:js": "esbuild src/js/frontend.js --bundle --minify --target=es2018 --outfile=build/js/frontend.js",
    "watch": "concurrently \"npm:watch:css\" \"npm:watch:js\"",
    "watch:css": "sass --watch src/scss/frontend.scss:build/css/frontend.css --style=expanded",
    "watch:js": "esbuild src/js/frontend.js --bundle --sourcemap --target=es2018 --outfile=build/js/frontend.js --watch"
  }
}
```

Requirements:

- `npm run build` must create both expected compiled files.
- PHP enqueues files only from `build/`.
- Production packaging must include `build/`.
- Do not enqueue source SCSS or unbundled source JavaScript.
- Do not commit `node_modules/`.
- Avoid source maps in the production build.
- Build failures must not leave a partially valid release unnoticed.

---

## 11. Security and output rules

This plugin has no saves or privileged actions in V1, so it needs no nonce.

It still must:

- Prevent direct PHP file access.
- Escape all template output.
- Sanitize shortcode attributes.
- Allowlist networks and SVG names.
- Encode external share URLs correctly.
- Never accept arbitrary HTML or SVG through shortcode attributes.
- Never use `eval`, inline event attributes, or unsafe DOM injection.
- Use `noopener noreferrer` for external new-window links.
- Avoid exposing internal paths or PHP errors to visitors.
- Use translated strings for all visible text and status messages.

---

## 12. Performance rules

- Keep the plugin dependency-free at runtime.
- No API calls.
- No social SDKs.
- No database queries beyond normal WordPress title/permalink resolution.
- No AJAX.
- No REST route.
- No frontend framework.
- One small CSS file and one small JS file.
- Inline SVGs are preferred over separate network requests.
- Do not repeatedly calculate the same URL/title inside a single shortcode render.

---

## 13. Testing checklist

### Functional

- `[bolt_share]` renders the trigger.
- Trigger opens and closes the correct panel.
- Multiple instances behave independently.
- Opening one closes another.
- Outside click closes.
- Escape closes and restores trigger focus.
- Facebook URL contains the encoded share URL.
- E-mail URL contains encoded subject and URL.
- Instagram invokes native sharing when supported.
- Instagram copy fallback shows status.
- User cancellation is silent.
- Custom shortcode attributes work.
- Unsupported networks do not render.
- Empty or malformed attributes fall back safely.

### Accessibility

- Keyboard-only operation works.
- Focus indicator is visible.
- `aria-expanded` updates.
- `aria-controls` points to a unique panel.
- Icons are decorative and hidden from assistive technology.
- Text labels remain present.
- Status is announced.
- Reduced-motion preference is respected.

### WordPress

- Plugin activates without warnings.
- Missing Composer autoload displays a clear admin notice rather than a fatal error.
- No output occurs during activation.
- No notices with `WP_DEBUG` enabled.
- Works in normal page content and shortcode-capable template areas.
- Translation functions use `bolt-share`.
- Plugin does not run assets in wp-admin.

### Build

- Clean install: `npm install`.
- Production build: `npm run build`.
- Built files exist at the paths PHP expects.
- No `node_modules` in release.
- Release archive includes `vendor/` and `build/`.

---

## 14. Definition of done

V1 is done only when:

1. The plugin activates cleanly.
2. Composer autoloading works.
3. `[bolt_share]` renders valid, escaped markup.
4. The design resembles the supplied minimal “DEL + share icon” reference.
5. Dropdown behavior works with mouse, touch, and keyboard.
6. Facebook, Instagram progressive enhancement, and e-mail behavior work as documented.
7. Multiple instances work on one page.
8. The production npm build succeeds.
9. Built assets are enqueued from `build/`.
10. The final code review reports no known fatal, security, accessibility, or release-blocking issue.
