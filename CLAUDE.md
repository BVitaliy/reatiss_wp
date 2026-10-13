# Project Rules

## Project context
- WordPress **custom theme** (`wp-content/themes/reatiss`), no page-builder plugins
- Reatiss — semiconductor reverse engineering / IC analysis company site (English)
- Built by integrating a static PHP markup project into WordPress
- Markup source of truth: `/Applications/MAMP/htdocs/reatiss/app/` (+ its `CLAUDE.md` —
  component list, adaptive rules, JS modules). When a template looks wrong, diff it
  against the original markup before "fixing" the CSS
- Local site: http://localhost:8888/reatiss_wp (DB `reatiss_wp`, table prefix `rts_`)
- Site language English only (qTranslate-XT not installed). **The admin is English too** (for
  English-speaking editors): ACF labels / instructions, CPT labels, menus, Site Settings are
  English source strings in the `reatiss` text domain (`acf/settings/l10n_textdomain`), so a
  translation file can localise them per user language later. SEO via Yoast.
- WP core was copied from malvy_wp — a Ukrainian build (`$wp_local_package = 'uk'` in
  wp-includes/version.php). The `WPLANG` option is set to '' on purpose: without it the whole
  site (front `<html lang>`, login) falls back to Ukrainian.
- Reference integration with the same conventions: `/Applications/MAMP/htdocs/malvy_wp`

## Integration plan (stages)
1. Infrastructure — WP, plugins, security, theme skeleton, assets, header / footer /
   modals, Site Settings, menus, CF7 forms, text pages, 404 ✅
2. Page builder + Home ✅
3. About ✅
4. Services / Labs / Expertise (CPTs, list pages, singles on the builder) ✅
5. Blog / Events / Authors ✅
6. On Shelf + forms (reCAPTCHA keys later) ✅
7. Search (live + results page), Contacts, map
8. Content seed, Yoast, performance, team checklist, adaptive QA

## Project structure
```
functions.php     — constants + require bootstrap only
functions/        — PHP feature classes and helpers (init, security, admin, media, forms…)
includes/acf/     — ACF bootstrap (acf-json paths, options page «Налаштування сайту»)
acf-json/         — ACF field groups (source of truth — see .claude/acf-flexible-content.md)
templates/        — page templates
template-parts/   — section partials ported from the markup's template-parts/
template-parts/global/ — header, footer, modals, page-top (breadcrumbs)
js/               — markup JS as delivered + WP-only files (app-cf7.js); js/vendors/ = third-party
css/              — compiled CSS (do not edit; generated from scss/ by `gulp styles`)
scss/             — SCSS from the markup; WordPress-only additions go to scss/style/_wp.scss,
                    local @font-face in scss/main/_fonts.scss
img/ fonts/ video/ — theme assets (icons, logo, 404 art, solutions scene clips);
                    content images / videos / PDFs / models belong in the media library
logs/             — custom logs (rts_log()), gitignored
```

## Deviations from the markup (keep the list short and current)
- Fonts are local (`fonts/*.woff2`, `scss/main/_fonts.scss`) instead of Google Fonts.
- `js/app-gallery.js`, `js/app-model.js`: on-demand vendor URLs are prefixed with
  `window.reatissThemeUrl` (set inline before app-global.js).
- Forms are CF7 (`form.rts-form`), not `.js-form`: error states / busy button / "thanks"
  popup in `js/app-cf7.js`; CF7 wrap fix in `_wp.scss`. Field HTML lives in the CF7 form
  editor (`label.field > span.field__label + [tag class:field__input] + span.field__error`),
  submit = `[rts_submit "Send"]`.
- `js/app-wp.js`: editor links to `#consultation` / `#reports` open those popups.
- Section partials take every text from fields; decorative run-lines (`.run-line` SVG paths)
  stay hardcoded in the block partials, as in the markup.
- Menus come from WP (`RTS_Nav_Walker` adds `.is-active` / `aria-current`); app-header.js's
  demo active-link code matches nothing on WP URLs and is harmless.

## Page builder
- ACF flexible content `page_builder` (group `group_rts_page_builder`) on pages and on
  service / lab / expertise singles. Layout `foo_bar` → `template-parts/blocks/foo-bar.php`
  with the row as `$args` (`functions/builder.php`). First sub-field `{layout}_hide`.
- Every rendered row is followed by `.spacer-xl` (`seo` → `.spacer-sm`); hidden rows print
  nothing, so no gaps.
- A page whose first block is in `RTS_DARK_TOP_LAYOUTS` (hero, page_banner) keeps the
  transparent header and has no `.page-top`; otherwise `header--light` + breadcrumbs.
- `page.php`: builder blocks, or the text page when there are none. Builder pages hide the
  classic editor in the admin (`functions/admin-editor.php`).
- Cards: `template-parts/cards/{expertise,lab,news,event}.php`, lists via `rts_card_posts()`
  (`functions/cards.php`). Section list pages: Site Settings → Сторінки розділів
  (`functions/sections.php` — breadcrumbs, active menu item).
- Service / lab / expertise singles use `single-builder.php` (`RTS_BUILDER_TYPES`); their list
  pages are ordinary pages with the `list_page` block.
- Editor inserts (`functions/shortcodes.php`): `[rts_cta]`, `[rts_slider ids]`, `[rts_youtube url image]`
  → the markup's `.article-cta` / `.article-slider` / `.article-video`. Print WYSIWYG that may
  hold them with `rts_editor_content()` (kses + strips the `<p>` wpautop puts around them).
- Lists (`functions/lists.php`): blog = posts page (`home.php`), categories / tags / event categories
  = `archive.php`, events = page template «Події» (`templates/events.php`, own `/events/page/N/`
  rule), author = `author.php`; all render `template-parts/lists/list.php` + `rts_pager()`.
  "Show more" = `js/app-load-more.js` → `wp_ajax_rts_load_more` (nonce `rts_nonce`).
- **rspanel sorts posts by menu_order** (its `pre_get_posts`): date-ordered post lists set
  `orderby=date` explicitly and use `rts_get_posts()` (WP_Query without `suppress_filters`).
- Articles / events: `single.php` → `template-parts/article/article.php` (+ related); tables are
  wrapped in `.article-table` by a `the_content` filter; author fields = ACF on users.
- On Shelf: `reports_list` block (CPT `rts_report`, 10 a page) — a row opens the `reports`
  popup and `js/app-reports.js` puts its title into the CF7 hidden field `your-report`.
- Admin list rows of pages, posts and every theme post type have **Duplicate** (draft copy with
  all fields, image, terms) — `functions/admin-duplicate.php`.
- Buttons / links: group { label, type: none|page|custom|modal, page, url, new_tab } →
  `rts_button()` / `rts_link()` + `rts_link_attrs()`.

## Build
`npm install` then `gulp` in the theme dir runs styles + img + BrowserSync (proxies
the local WP site) and watches. **Never edit `css/*.css` directly.**
`js/vendors/` is refreshed by `gulp updateJS` — never hand-edit those files.

## Prefixes and text domain
- Text domain: `reatiss`
- PHP functions/globals: `rts_`
- PHP classes: `RTS_` (e.g. `RTS_Theme`, `RTS_Nav_Walker`)
- Constants: `RTS_UPPER_SNAKE`
- CSS classes: come from the markup — **do not rename them**

## Language
- All code, comments, variable names, and commit messages in **English**
- Front-end strings are **English**, wrapped for translation: `esc_html_e( 'Read more', 'reatiss' )`
- Admin labels / ACF labels / instructions are **English**, translatable (`__( '…', 'reatiss' )`)

## Escaping ACF output — no exceptions
`get_field()` / `get_sub_field()` / `rts_option()` return unescaped data. Escape at
output: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses_post()` for wysiwyg.

## PHP — checking variables before use
Prefer **`! empty( $var )`**; do not stack redundant `isset()` / type checks.

## PHP — no alternative control structure syntax
Always use curly braces `{}`. Never `:` / `endif` / `endforeach`.

## Team Back End checklist
Security (no registration, REST user/post insert blocked, XML-RPC off), `ABSPATH`
guard in every PHP file, local fonts, WebP, nonce on every AJAX call,
`update_post_meta_cache` / `update_post_term_cache` => false on custom queries
that don't need them, removable/hideable blocks, no empty gaps, everything
editable from the admin.

## No debug code in commits
Remove all `var_dump()`, `console.log()`, `error_log()` before committing.

---

## Additional rules
- [PHP Standards](.claude/php-standards.md)
- [JavaScript Standards](.claude/js-standards.md)
- [Security](.claude/security.md)
- [ACF Flexible Content](.claude/acf-flexible-content.md)
