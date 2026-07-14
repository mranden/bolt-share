# Bolt Share — strict Cursor rules

These rules apply to every change in this repository.

---

## 1. Architecture — must always

1. Use the fixed plugin identity:
   - `bolt-share`
   - `BoltShare\`
   - `bolt-share`
   - `[bolt_share]`
   - `bolt_share_`
   - `.bolt-share`

2. Keep the main plugin file limited to:
   - Plugin header
   - Direct-access guard
   - Constants
   - Composer autoload guard
   - Text-domain loading
   - Root plugin boot

3. Put application PHP under `app/` and load it through Composer PSR-4.

4. Keep the root `Plugin` class as a small service orchestrator.

5. Use separate classes for:
   - Frontend assets
   - Shortcode rendering/normalization
   - Trusted SVG icons

6. Use a PHP template under `resources/templates/` for component markup.

7. Keep the architecture proportionate to a small plugin.

---

## 2. Architecture — must never

1. Never put shortcode markup or share-link logic in `bolt-share.php`.

2. Never add loose procedural feature files.

3. Never add:
   - A framework
   - A service container
   - jQuery
   - React
   - AJAX
   - REST routes
   - Database tables
   - Options
   - Post meta
   - A settings page
   - A Gutenberg block
   unless the user explicitly expands the V1 scope.

4. Never add a social-network SDK or require a social-network App ID for V1.

5. Never duplicate the shortcode registration, asset hooks, or document-level event listeners.

---

## 3. Shortcode and data — must always

1. Register only the documented shortcode `[bolt_share]`.

2. Use `shortcode_atts()` with documented defaults.

3. Sanitize every attribute.

4. Allowlist networks:
   - `facebook`
   - `instagram`
   - `email`

5. Remove unknown and duplicate network values.

6. Escape data again at output time.

7. Return shortcode markup; never echo it.

8. Generate a unique panel ID for each rendered shortcode.

9. Resolve a safe fallback URL when no singular permalink is available.

10. Use one text domain: `bolt-share`.

---

## 4. Shortcode and data — must never

1. Never accept raw HTML, JavaScript, or SVG through shortcode attributes.

2. Never use an arbitrary network name to construct a URL or icon.

3. Never output unescaped title, URL, label, class, ID, or status text.

4. Never trust a custom shortcode URL without sanitizing it.

5. Never use `$_GET`, `$_POST`, or `$_SERVER` casually to build the shared URL.

---

## 5. Sharing behavior — must always

### Facebook

- Encode the shared URL.
- Open externally.
- Use `rel="noopener noreferrer"`.
- Preserve a usable link without JavaScript.

### E-mail

- Encode subject and body separately.
- Include the shared URL.
- Preserve a usable link without JavaScript.

### Instagram

- Use `navigator.share()` only after a user click.
- Pass only safe title/text/URL data.
- Treat the native share sheet as browser/OS controlled.
- Use copy-link fallback.
- Announce fallback status through `aria-live`.
- Treat `AbortError` as cancellation.
- Keep the control as a button.

---

## 6. Sharing behavior — must never

1. Never promise a direct Instagram web-share endpoint.

2. Never claim content was posted to Instagram.

3. Never request Instagram credentials or permissions in V1.

4. Never silently copy without visible status feedback.

5. Never open misleading or unrelated Instagram URLs as though sharing succeeded.

6. Never use unsafe popup techniques or remove `noopener`.

---

## 7. Accessibility — must always

1. Use a real `<button type="button">` for the trigger.

2. Keep `aria-expanded` synchronized with state.

3. Connect trigger and panel with `aria-controls` and a unique ID.

4. Use the `hidden` attribute for closed panels.

5. Keep all actions keyboard reachable.

6. Close on Escape.

7. Return focus to the trigger when Escape closes the panel.

8. Close on click outside.

9. Preserve visible focus styles.

10. Use text labels in addition to icons.

11. Mark decorative SVGs:
    - `aria-hidden="true"`
    - `focusable="false"`

12. Use `aria-live="polite"` for status feedback.

13. Respect `prefers-reduced-motion`.

---

## 8. Accessibility — must never

1. Never use a `<div>` or `<a href="#">` as the trigger.

2. Never remove focus outlines without a clear replacement.

3. Never rely on hover alone.

4. Never trap focus in the dropdown.

5. Never use `role="menu"` for this simple group of share actions.

6. Never make icon-only options without accessible names.

7. Never hide status feedback only visually and omit it from assistive technology.

---

## 9. JavaScript — must always

1. Use vanilla JavaScript.

2. Initialize every `[data-bolt-share]` component.

3. Prevent duplicate initialization.

4. Support multiple instances.

5. Keep state local to each instance.

6. Close other open instances before opening a new one.

7. Use `textContent`, not `innerHTML`, for dynamic status.

8. Handle missing optional browser APIs safely.

9. Keep helper functions small and named by purpose.

10. Build source JS into `build/js/frontend.js`.

---

## 10. JavaScript — must never

1. Never add inline event attributes.

2. Never inject untrusted HTML.

3. Never use `eval`, `new Function`, or string-based timers.

4. Never depend on jQuery.

5. Never attach duplicate document listeners per repeated initialization.

6. Never log avoidable production noise.

7. Never use frontend JavaScript as the only source of Facebook or e-mail functionality.

---

## 11. SCSS and design — must always

1. Use `.bolt-share` as the block namespace.

2. Use BEM-style child classes.

3. Keep selectors shallow and theme-safe.

4. Use CSS custom properties for:
   - Text/icon color
   - Dropdown background
   - Border
   - Shadow
   - Radius
   - Width
   - Spacing

5. Inherit site typography.

6. Give interactive controls a minimum 44px height.

7. Keep the dropdown within the mobile viewport.

8. Make option rows comfortable for touch.

9. Build SCSS into `build/css/frontend.css`.

10. Match the supplied minimal “DEL + share icon” direction.

---

## 12. SCSS and design — must never

1. Never use IDs for styling.

2. Never style generic elements outside `.bolt-share`.

3. Never add `!important` unless an unavoidable integration conflict is documented.

4. Never force a theme font family.

5. Never remove the visual focus state.

6. Never create a heavy card/button design that contradicts the reference without user approval.

7. Never rely on animation to communicate state.

---

## 13. Build pipeline — must always

1. Use npm scripts with Sass and esbuild.

2. Provide:
   - `npm run build`
   - `npm run watch`
   - Individual CSS and JS build commands

3. Output exactly:
   - `build/css/frontend.css`
   - `build/js/frontend.js`

4. Enqueue only compiled files.

5. Exclude `node_modules` from version control and releases.

6. Include `build/` in production releases.

7. Run a production build before declaring completion.

8. Run PHP syntax checks before declaring completion.

---

## 14. Build pipeline — must never

1. Never add Gulp when the npm pipeline already satisfies the project.

2. Never enqueue `.scss` or source JS.

3. Never rely on a developer machine’s `node_modules` in production.

4. Never state that the build works without running it.

5. Never leave source maps in the production build unless explicitly requested.

---

## 15. Security — must always

1. Guard PHP files against direct access.

2. Escape by context:
   - `esc_html()`
   - `esc_attr()`
   - `esc_url()`
   - Safe pre-approved SVG output only

3. Sanitize shortcode URL values with `esc_url_raw()`.

4. Sanitize custom CSS class tokens individually.

5. Keep SVGs in a fixed allowlist.

6. Use strict comparisons and strict `in_array()`.

7. Keep external links safe.

---

## 16. Security — must never

1. Never accept arbitrary SVG markup.

2. Never echo user-controlled HTML.

3. Never use unsanitized attributes in data attributes.

4. Never create an open redirect.

5. Never expose file paths or stack traces to frontend visitors.

6. Never add a nonce merely for appearance when there is no state-changing request; V1 has no forms, saves, AJAX, or privileged actions.

---

## 17. Scope control

When a request is ambiguous:

- Preserve the current V1 scope.
- Implement the smallest complete solution.
- Document a limitation rather than inventing a large feature.
- Ask before adding admin UI, tracking, social APIs, direct publishing, or a block.

A future feature must not be pre-built “just in case”.

---

## 18. Completion report

Every completed implementation response must include:

- Files changed
- Commands actually run
- Tests actually completed
- Build result
- Known limitations
- Any item that still needs manual browser testing

Never report assumed success as verified success.
