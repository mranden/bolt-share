# Bolt Share — Cursor project prompt

You are building and maintaining **Bolt Share**, a small production-ready WordPress shortcode plugin.

Read these project files before changing code:

1. `.cursor/agent.md`
2. `.cursor/rules.md`
3. `docs/technical-plan.md` when it exists
4. The current plugin code and file tree

Treat `.cursor/rules.md` as strict requirements and `.cursor/agent.md` as the architectural and functional specification.

---

## Project identity

- Plugin name: **Bolt Share**
- Plugin slug: `bolt-share`
- Main plugin file: `bolt-share.php`
- Namespace: `BoltShare\`
- Text domain: `bolt-share`
- Shortcode: `[bolt_share]`
- PHP hook prefix: `bolt_share_`
- CSS block: `.bolt-share`
- JavaScript root: `[data-bolt-share]`

Do not silently rename any of these.

---

## Core feature

The shortcode renders a minimal share trigger inspired by the supplied design:

- Text reading **“Del”** by default.
- An inline share SVG icon to the right.
- Clicking the trigger toggles a small dropdown below it.
- The dropdown heading defaults to **“Del på…”**.
- The default options are Facebook, Instagram, and e-mail.
- Every option contains its written label and an inline SVG icon.

Default shortcode:

```text
[bolt_share]
```

Supported attributes:

```text
label
dropdown_title
networks
title
url
class
```

Full example:

```text
[bolt_share label="Del" dropdown_title="Del på..." networks="facebook,instagram,email"]
```

---

## Required implementation approach

### PHP

- Use Composer PSR-4 autoloading from `app/`.
- Keep the main plugin file thin.
- Wire services through `BoltShare\Plugin`.
- Put shortcode behavior in `BoltShare\Frontend\ShareShortcode`.
- Put asset registration/enqueueing in `BoltShare\Frontend\Assets`.
- Put trusted inline SVG definitions in `BoltShare\Support\SvgIcons`.
- Render markup from `resources/templates/share.php`.
- Return shortcode markup; do not echo it.
- Escape every dynamic output value.
- Sanitize and normalize every shortcode attribute.
- Allowlist network names.
- Create a unique dropdown panel ID for each instance.

### Frontend behavior

Use vanilla JavaScript only.

The component must:

- Toggle with its trigger.
- Keep `aria-expanded` synchronized.
- Close on outside click.
- Close on Escape.
- Restore focus to the trigger when Escape closes it.
- Close other open Bolt Share panels.
- Support multiple instances on one page.
- Avoid duplicate initialization.
- Use `hidden` for the closed panel.

### Sharing

- Facebook: encoded share URL, external window, `noopener noreferrer`.
- E-mail: encoded `mailto:` subject and body.
- Instagram:
  - Use `navigator.share()` when available.
  - Let the browser/OS choose the actual share target.
  - On unsupported devices, copy the URL and show:
    `Link copied – open Instagram and paste it.`
  - Treat `AbortError` as cancellation, not failure.
  - Never claim that a post was published.

### Assets

Use simple npm scripts with:

- Sass for `src/scss/frontend.scss`.
- esbuild for `src/js/frontend.js`.
- Output:
  - `build/css/frontend.css`
  - `build/js/frontend.js`
- Add `npm run build` and `npm run watch`.
- Do not add Gulp, jQuery, React, or a frontend framework.
- PHP must enqueue only compiled assets from `build/`.

### Design

Match the minimal reference:

- Transparent trigger.
- Dark navy/teal text and icon.
- Text left, share icon right.
- Inline-flex alignment and generous gap.
- Minimum 44px interactive height.
- No heavy filled-button appearance.
- Dropdown directly below, white, subtle border/shadow, compact radius.
- Full-width option rows with aligned SVG and label.
- Use CSS custom properties for easy theme overrides.
- Constrain the dropdown to the viewport on mobile.
- Preserve visible focus styles.
- Respect reduced motion.

---

## Coding behavior

Before editing:

1. Inspect the relevant files.
2. Explain the exact implementation plan briefly in `docs/technical-plan.md` when the task changes architecture.
3. Reuse existing services and conventions.
4. Do not duplicate hooks, renderers, icons, or event listeners.

While editing:

- Make complete changes, not illustrative snippets.
- Keep methods small and single-purpose.
- Add PHPDoc where WordPress hooks, filters, arrays, or non-obvious behavior need it.
- Use strict comparisons.
- Prefer early returns.
- Use translated strings.
- Avoid speculative abstractions.
- Do not add settings or future features not requested for V1.

After editing:

1. Run Composer validation/autoload generation where available.
2. Run `npm run build`.
3. Run PHP syntax checks on all plugin PHP files.
4. Inspect the generated build paths.
5. Review the shortcode markup and all escape contexts.
6. Report:
   - Files changed
   - Commands run
   - Tests completed
   - Any remaining limitations

Do not say a test passed unless it was actually run successfully.

---

## Non-negotiable V1 limitations

- No direct Instagram publishing.
- No Instagram credentials, OAuth, Graph API, or app registration.
- No admin settings.
- No tracking.
- No AJAX.
- No REST API.
- No database storage.
- No social SDK.
- No Gutenberg block.
- No shortcode-generated arbitrary HTML or arbitrary SVG.

---

## Completion standard

A task is not complete merely because files exist.

It is complete when the plugin:

- Activates cleanly.
- Renders `[bolt_share]`.
- Matches the intended design direction.
- Works with mouse, touch, and keyboard.
- Handles multiple instances.
- Provides functional Facebook and e-mail links.
- Provides honest Instagram progressive enhancement.
- Builds valid production CSS and JavaScript.
- Contains no known fatal, security, accessibility, or release-blocking issue.
