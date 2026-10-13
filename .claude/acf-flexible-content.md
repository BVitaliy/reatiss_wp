# ACF Flexible Content

## Core principle
All ACF fields defined in **JSON** in `acf-json/`. **Never call `acf_add_local_field_group()` in PHP.** Never create or edit fields in the ACF admin UI — admin is for viewing only (fields come from JSON).

## Project structure
```
acf-json/
├── group_rts_page_builder.json   ← Page Builder (flexible content — pages and CPT singles)
├── group_rts_theme_options.json  ← Site Settings (options page «Налаштування сайту»)
└── …                             ← CPT / taxonomy / user groups
includes/acf/
└── register-acf-fields.php       ← bootstrap: acf-json paths + options page
```

## ACF Local JSON (`acf-json/`)
JSON files are the **source of truth**. Edit them directly, then commit. ACF PRO loads them automatically via the `acf/settings/load_json` filter in `register-acf-fields.php`.

### Theme bootstrap (required)
```php
add_filter( 'acf/settings/save_json', function () {
    return get_stylesheet_directory() . '/acf-json';
} );

add_filter( 'acf/settings/load_json', function ( $paths ) {
    unset( $paths[0] );
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
} );
```

### Workflow
1. Edit the relevant `.json` file in `acf-json/` directly.
2. Commit the changed JSON.
3. (Optional) If you made edits via ACF admin UI, use **ACF → Field Groups → Save** to write changes back to the JSON file, then commit.

## JSON file structure
```json
{
  "key": "group_XXX",
  "title": "...",
  "fields": [ ... ],
  "location": [ [ { "param": "...", "operator": "==", "value": "..." } ] ],
  "menu_order": 0,
  "position": "normal",
  "style": "default",
  "label_placement": "top",
  "instruction_placement": "label",
  "hide_on_screen": "",
  "active": true,
  "description": "",
  "show_in_rest": 0,
  "modified": 1748955600
}
```

## Field keys
- **`key`**: globally unique — use `field_` + random hex (e.g. `field_c4b8a1e62d9f0735`)
- **`name`**: semantic `snake_case`; unique within its parent only
- **Never change** an existing `key` or `name` — breaks saved data

## Mandatory field settings

### wrapper — set width by context
```json
{ "name": "heading", "type": "textarea", "wrapper": { "width": "70" } },
{ "name": "image",   "type": "image",    "wrapper": { "width": "30" } }
```

### Return formats

| Type | `return_format` |
|---|---|
| `image` | `array` |
| `gallery` | `array` |
| `file` | `url` |
| `relationship` | `id` |
| `post_object` | `id` |

### Image / file instructions
Every `image`, `gallery`, or `file` field **must** have `instructions` with pixel dimensions.

### wysiwyg toolbar
Default is `full`. Use `basic` only when explicitly needed.

## Link pattern (this project)
Editor-configurable links use a `group` with `type` (none | page | custom), `page`
(page_link), `url` (text — accepts external URLs and `#anchors`) and `new_tab`.
Render with `rts_link( $value )` from `functions/helpers.php` — see `ads_line_link`.

## CTA Button pattern
Always a `group` field with `"layout": "table"`. The PHP helper `rts_acf_cta_group()` exists for reference but is not used for JSON — inline the structure directly:
```json
{
  "key": "field_cta_grp_SLUG",
  "label": "CTA Button",
  "name": "cta",
  "type": "group",
  "layout": "table",
  "wrapper": { "width": "100" },
  "sub_fields": [
    { "key": "field_cta_lbl_SLUG", "label": "Label", "name": "label", "type": "text", "wrapper": { "width": "50" } },
    { "key": "field_cta_url_SLUG", "label": "Link",  "name": "link",  "type": "link", "return_format": "array", "wrapper": { "width": "50" } }
  ]
}
```

In template: `$cta = get_sub_field('cta'); $cta['label'], $cta['link']`

## Hide block field
First `sub_field` of every flexible layout must be:
```json
{
  "key": "field_hide_SLUG",
  "label": "Hide block",
  "name": "SLUG_hide",
  "type": "true_false",
  "ui": 1,
  "wrapper": { "width": "100" }
}
```

## Conditional logic
```json
"conditional_logic": [
  [
    { "field": "field_CONTROLLING_KEY", "operator": "==", "value": "VALUE" }
  ]
]
```
- `field` = **key** of controlling field (not `name`)
- Always triple-nested array

## New layout scaffold
```json
{
  "key": "layout_OPAQUE_HEX",
  "name": "SLUG",
  "label": "Label",
  "display": "block",
  "sub_fields": [
    {
      "key": "field_hide_SLUG",
      "label": "Hide block",
      "name": "SLUG_hide",
      "type": "true_false",
      "ui": 1,
      "wrapper": { "width": "100" }
    },
    {
      "key": "field_OPAQUE_HEX",
      "label": "Label",
      "name": "field_name",
      "type": "text",
      "wrapper": { "width": "100" }
    }
  ]
}
```

## Field types reference

| Type | Notes |
|---|---|
| `text` | single line |
| `textarea` | set `rows`; `new_lines`: `"br"` |
| `wysiwyg` | `toolbar`: `"full"` (default) / `"basic"` |
| `image` | `return_format`: `"array"` — always set `instructions` with size |
| `gallery` | `return_format`: `"array"` — always set `instructions` with size |
| `file` | `return_format`: `"url"`, set `mime_types` |
| `select` | requires `choices` object; `return_format`: `"value"`; `ui: 1` |
| `true_false` | `ui: 1` for toggle |
| `repeater` | has `sub_fields`; set `layout` (`"block"` / `"table"`) |
| `flexible_content` | has `layouts` array |
| `group` | CTA always uses `"layout": "table"` |
| `relationship` | `return_format`: `"id"` |
| `post_object` | `return_format`: `"id"` |

## Page builder renderer

Any `flexible_content` field rendered in PHP **must** be split into per-layout partials:

```
includes/<renderer-name>.php          ← slim dispatcher only
includes/<renderer-name>/
    layout-<slug>.php                 ← one file per layout
```

**Dispatcher rules:**
- Define `$rts_partials_dir = get_stylesheet_directory() . '/includes/<renderer-name>/'` once before the loop
- Keep only `while / the_row / switch / require` — no HTML in the dispatcher
- Use `require` (not `include`) so a missing partial is a fatal error, not silent

**Partial rules:**
- Start with `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard
- Call `get_sub_field()` directly — row context is set by `the_row()` in the dispatcher
- Replace `break;` early-exits with `return;` (file end returns to `require` site naturally)
- Filename = `layout-<descriptive-slug>.php` — may differ from the ACF layout key if the key is legacy or misleading (never rename the ACF key; it would break saved data)

**ACF JSON:** one field group = one JSON file. Splitting across files is not supported by ACF. Large JSON files are auto-generated and do not need to be split.

## Checklist
- [ ] Every `key` is opaque and repo-unique (`field_` + random hex)
- [ ] Every field has `wrapper` with width
- [ ] `image` / `gallery` → `return_format: "array"` + `instructions` with px size
- [ ] `file` → `return_format: "url"`
- [ ] `relationship` / `post_object` → `return_format: "id"`
- [ ] `wysiwyg` → `toolbar: "full"` (unless `"basic"` is justified)
- [ ] CTA = `group` field with `layout: "table"` (inline structure, not PHP helper)
- [ ] First sub_field = Hide block (`true_false`, `ui: 1`)
- [ ] `acf/settings/save_json` + `acf/settings/load_json` → theme `acf-json/`
- [ ] Changed JSON committed to repo
- [ ] Template updated with matching `get_row_layout()` block
