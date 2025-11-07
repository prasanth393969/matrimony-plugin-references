## [2.0.42] - 2025-10-31 

### Added

- [Etch](https://etchwp.com/) integration.
- Added selector choice to all the relational fields. Allowed values: `advanced`, `select2`.

### Fixed

- Encapsulating `esc_attr()` to prevent warnings from formatting.php in case the string is empty.
- Correct fetching of field values in quick edit mode.
- Displaying error notices using cookies instead of sessions (Fixed `session_start` warning).

### Changed

- Default Phone field value selector improvements.
- Enhanced post taxonomy form selector: added radio/checkbox option.
- Improved rendering of media fields in Generate Blocks.
- Replaced [choices.js](https://github.com/Choices-js/Choices) with [tom-select](https://tom-select.js.org/) in form select inputs.

## [2.0.41] - 2025-10-26 

### Fixed

- Fixed fetching of repeater fields values in Bricks builder.

## [2.0.40] - 2025-10-24 

### Added

- DB Tools improvements (change column type).
- Added CURL to health checks.
- Introduced min and max elements for relational fields.

### Fixed

- Fixes and improvements in Divi5 dynamic content tags.
- Fixed the Country field behaviour in forms.
- Fixed saving of Table fields values with 20+ rows.
- Fixed the initialization of nested relational fields in a repeater field.
- Fixed saving of date field settings.
- Fixed fetching of global variables.
- Fixed assets loading (fixed incompatibility with [Wishlist Member](https://wishlistmember.com/) plugin).
- Fixed `allow_html` flag wrong behaviour in Text and Textarea fields.
- Fixed fetching the value of relational fields.
- Fixed relational fields in Bricks builder.

### Changed

- Refactoring of relational fields (Post, Post Multi, Term, Term Multi, User, and User Multi), with selector improvements.
- Meta field default value selector fixes and improvements: added Audio, Image, Gallery, Video, and Relational fields.
- Improved error displaying using sessions.
- Removed visibility checks on meta fields in the Bricks builder data provider.

## [2.0.39] - 2025-10-14 

### Added

- Introduced logs (with a dedicated manager).
- Integrated changelog (with a dedicated reader).
- Added acpt/save_dynamic_block hook.
- Added acpt/error hook.
- Allow black values in Date fields.
- Added URL label control in Divi5 dynamic tags.
- Added the Acceptance field in forms.

### Fixed

- Fixed table field (import template when there is more than one table field).
- Fixed assets loading (using module relative paths in admin.js).
- Fixed post type cache invalidation.
- Fixed Gallery and Audio multiple fields (back-end + front-end).
- Fixed CSS (incompatibility with Yoast).
- Fixed Dynamic block controls rendering.

### Changed

- Import modules using relative paths in `admin.js` file. 
- Integrated Post object fields in the Generate Blocks Query block.

## [2.0.38] - 2025-10-03 

### Added

- Introduced custom action hooks.
- Introduced keyboard shortcuts for List, Repeater, and flexible fields.
- Integration with Elementor PRO Posts widget.
- Added relative path option to URL fields.

### Fixed

- UI/UX fixes and improvements in Dataset manager.
- Missing JS assets (Date range picker and Table fields).
- Fixed fatal errors triggered by Slim SEO integration.
- Gallery field fix.
- MetaGroupModel::isVisible() fix (correct fields fetching associated with Users in Forms).
- Fixed data fetching in dynamic data in Generate Press.
- Form shortcode fix.

## [2.0.37] - 2025-09-26

### Added

- Ensure compatibility with Divi5 (in legacy mode).
- Added ACPT fields to dynamic content tags in Divi5.
- Sortable relationship field selector.
- Introduced allow dangerous content option in HTML fields.

### Fixed

- Missing JS imports.
- Fixed form assets loading.
- Fixed MetaGroupModal::isVisibilty() method.

## [2.0.36] - 2025-09-22

### Added

- Added support to Elementor PRO dynamic display conditions.
- Added typographic elements (headings, paragraphs, links, and images) to forms.
- Added font-size control to form elements.
- Added skip_sanitize argument to Bricks builder strings.
- Added user meta to API REST routes.
- Save meta box visibility preferencies in cookies.

### Fixed

- Correct handling of meta-group context and priority.
- Display option page meta fields in Gutenberg.
- Fixed form nonce validation when there are 2 forms on the same page.
- Fixed errors in Elementor PRO gallery field tag.
- Fixed null pointer error while dragging elements to the Form canvas.
- Fixed OptionPageModel capability constraints.
- Fixed Date Range field (correct application of the default value).
- Fixed the Relational field query loop in Bricks builder.
- WPML/Polylang - regeneration of wpml-config.xml when the plugin is updated.

### Changed

- Improved assets loading using wp_enqueue_script_module function.

### Removed

- Unused admin.js file.

## [2.0.35] - 2025-09-08

### Added

- Introduced ID field

### Fixed

- Fixed Breakdance gallery field.
- Fixed warnings in Currency, Length and Weight fields.
- Correct handling of taxonomies "Show admin column" setting.
- Application scrollbar CSS fixes.
- Correct handling of embed fields in forms.
- Fixes and improvements to Checkbox and Radio form fields.
- Address field fixes.

## [2.0.34] - 2025-08-22

### Fixed

- Fixed WooCommerce product search.
- Fixed MetaRepository::get().
- Fixed MetaGroup visibility checks (with only belongsTo argument).

## [2.0.33] - 2025-08-21

### Added

- Introduced Image slider (Before/After) field.
- Added prefix and suffix to Gutenberg basic block.
- Added the option to set the first image as post thumbnail to the Gallery field.
- Improved Relational field rendering load time (async AJAX data loading).
- Introduced variables in admin.css.

### Fixed

- `MetaGroupModel:isVisible()` method fix (fixed bulk edit).
- Fixes and refactoring of date fields.
- Fixed ActiveCampaign/PostMark incompatibility.
- Fixed conditional rendering rules based on taxonomy values.
- Gutenberg relational field block fixed.
- Polylang/WPML integration fix (wrong removal of wpml-config.xml file).

### Changed

- Refactoring of third parts integrations: added `with_context` arg to `get_meta_field` function to return after and before values
- Refactoring of Email, URL, and Phone fields rendering in Bricks builder
- `save_acpt_field_values` function refactoring, now accepts also file IDs

## [2.0.32] - 2025-08-06

### Added

- Integration with Bricks query filters.
- Added /block/ API endpoints.
- Improved integration with WPGraphQL: added forms, option pages and dynamic blocks.
- Added `toolbar` and `tabs` options to the Editor field.
- Added `Allow HTML` option to Text and Textarea fields.

### Fixed

- Fixed terms selector.
- Fixed regex validation of form fields.
- Fixed GenerateBlocks URL field.

### Changed

- Complete refactoring of Meta group visibility checks against the assigned location rules.
- Using custom date picker in all date fields (Date, Date time, Date Range and Time fields).
- Render images as `picture` tag in shortcodes.

## [2.0.31] - 2025-07-18

### Changed

- Make the plugin completely compliant with GNU/GPL rules.

## [2.0.30] - 2025-07-12

### Fixed

- Fixed fetching of option page meta field values.

### Changed

- Application boot improved: tested on a website with 32000 posts.

## [2.0.29] - 2025-07-10

### Added

- Introduced the Barcode field.
- Added display option (block or inline) to Checkbox and Radio fields.
- Adding a custom hook to override AbstractIntegration->isActive() method.

### Fixed

- `syncDBAndRunHealthCheck` fix.
- Post taxonomies form field: fixes and improvements.

### Changed

- Complete refactoring of Gutenberg blocks for Repeater and Relational fields.
- Redesign of WPML/Polylang integration.

## [2.0.29-beta-2] - 2025-06-20

### Added

- Introduced metabox conditional rules.
- Introduced the boolean param assoc in `get_acpt_fields` to return a associative array.
- Introduced [libphonenumber-for-php-lite](https://github.com/giggsey/libphonenumber-for-php-lite) for Phone numbers formatting.
- Added support to repeater field in GeneratePress.

### Fixed

- Fixed number field default value.
- DateRange data validation (check to <= from).
- Fixed beta versions check.

## [2.0.29-beta-1] - 2025-06-11

### Added

- Introduced [GenerateBlocks](https://generateblocks.com/) integration.
- Meta box settings: enable/disable toggle visibility and title.
- Support for Redis and Memcached as cache drivers.
- Performances improvements: added a second layer of cache in Repositories.
- Cache entries manager.
- Added bulk edit mode to the meta fields manager.
- Added a specific setting to delete the unused MySQL tables.

### Fixed

- Fixed DB warnings.
- DB schema compatibilty with legacy MySQL databases (<= 5.6).
- Fixed WooCommerce product search.
- Correct saving of "0" as meta field value.
- Fixed editor field missing scrollbar.
- Showing the correct saved value of Toggle field.
- Fixed `AbstractField::addBeforeAndAfter`: display only the value in preview mode.

## [2.0.28] - 2025-05-10

### Added

- Improved plugin boot.
- Conditional rendering improvements.
- Quick edit fields improvements.

### Fixed

- Fixed `customWpKses` function.
- Fixed filterable fields.

## [2.0.26] - 2025-05-06

### Added

- Added URL field render options (HTML, URL, or Label).
- Minified CSS and JS assets.

### Fixed

- Define ACPT_CACHE as a global variable (Fix fatal errors for PHP < 8.0).
- Fixed `validateAgainstMaxAndMin` function.
- Reduced size of the release file (removed unnecessary font in `endroid/qr-code` package).
- Fixed WooCommerce admin list (removed duplicated product_brand and product_shipping_class terms).

## [2.0.25] - 2025-05-03

### Added

- Introduced a character counter component for Textarea and Editor fields.
- Added min and max length to HTML5 form elements.
- Added back-end validation against min and max attributes.
- Added disable media option in Editor fields.
- Added three render modes to media fields (Image, Gallery, Audio, Audio Multiple, and File fields): HTML, URL, or ID. Applied only when rendered via shortcode.
- Added TWIG patcher (to fix the `Fatal error: Cannot redeclare twig_cycle()`).

### Fixed

- Fixed Date range field (min and max values).
- Fixed field slug generation in Breakdance provider.
- Correct height handling in Wygiwys editor (tinyMCE and Quill).
- Correctly enqueing of `[acpt]` shortcode assets in Gutenberg.
- Fixed repeater fields in Breakdance.
- Loading missing TinyMCE editor assets.

### Changed

- Using cache in WPAttachment for performance improvements.
- UI/UX refinements and improvements (contextual menus, added export code function where missing).
- Using Breakdance provider with Oxygen 6.0.0.
- Save nested field visibility in localStorage.
- WPML/Polylang improvement (translation of labels of URL and file fields).
- Fixed Address form field
- Fixed Repeater form field
- Improved CSS for the Color form field
- Added Address Multiple form field
- Added QR Code form field
- Introduced CSS variables in forms
- Support for dark color scheme in forms

## [2.0.24-beta-4] - 2025-04-11

### Added

- Dark theme skin.
- Apply conditional rendering rules to form fields.
- Sync form fields with meta field validation rules.
- Added attachments to native custom post types.
- Added support for Gallery fields in Divi integration.
- Added country value to Address fields.
- Create a post and link it to relational fields.

### Fixed

- Fixed repeater fields in forms.
- Fixed fatal error when registering new custom post types.

### Changed

- Custom audio player improvements (added support for thumbnails + improved mobile layout).

## [2.0.24-beta-3] - 2025-04-05

### Added

- Introduced Audio and Audio multiple fields.
- Enable beta versions on single licenses.
- Added user roles to form submission limits rules.
- Added relational fields to forms.

### Fixed

- Fixed Icon field (prevent fatal errors from htmlspecialchars and preg_replace).
- Fixed numeric fields in Elementor PRO.
- Fixed sorting of meta fields in the backend.
- Fixed add of new user columns in the back-end (fixed incompatibility with ASE).
- Fixed conditional rendering operand selector.
- Fixed repeater fields AJAX calls in forms.
- WooCommerce fix (remove selectize in product variations to avoid conflicts).

## [2.0.24-beta-2] - 2025-03-29

### Added

- Added sorting option to gallery fields (ascendand, descendant or random).
- Added support for conditional logic to address and embed fields.

### Fixed

- Fixed fatal error in dynamic block controls.
- Fixed PHP deprecation notices.

## [2.0.24-beta-1] - 2025-03-27

### Added

- Dynamic Gutenberg block manager.
- Added list and repeater fields to Forms.
- Added form submission limit.
- Added support for media widgets to URL fields in Bricks builder.
- Added numeric fields to the textual fields group in Elementor PRO.

### Fixed

- Form submission fix: OP fields are now properly saved.
- WPML integration fixes and improvements.
- Fixed importing of datasets from a CSV file.
- Fixed post selection bug in Form modal with a different language than English.
- Fixed form redirection.
- Fixed `JSON::isValid` function (wrong detection of float values as valid JSON strings).
- Patched `ACPT_Admin::addCustomPostTypeColumnsToAdminPanel()` function (avoid critical errors in case of post type name null).

### Changed

- Custom post types has_archive property correct handling.
- Select all fields toggle behavior improvement in meta field manager.
- Replaced Codemirror with [Monaco](https://github.com/suren-atoyan/monaco-react).

## [2.0.23] - 2025-03-03

### Added

- Added option page fields to SlimSEO.
- Added option page fields to SEOPress.
- Relational fields improvements in Bricks builder.

### Fixed

- Duplicate meta group fix.
- Fixed product data field manager.
- Color field fixes (removed the eye dropper from unsupported browsers).
- Textarea field bug (missing minlength and maxlength attributes).
- Fixed video field conditional rendering.
- Meta field conditional rendering tab fixes.
- Fixed PHP warning in Bricks builder.

### Changed

- Divi integration refactoring and improvements (all meta fields available in theme builder).

## [2.0.22] - 2025-02-13

### Added

- Integration with [SlimSEO](https://wpslimseo.com/).
- Added users to Conditional Rendering conditions.

### Fixed

- Repeater and flexible fields fixes and UI/UX improvements.
- QR Code field fix.
- Meta fields quick navigation menu fix.
- Fixed meta field manager glitches.
- Fixed datetime field formatting in Bricks builder.

### Changed

- Repeater and flexible fields fixes and UI/UX improvements.

## [2.0.21] - 2025-02-05

### Added

- Integration with [SEOPress](https://www.seopress.org/).
- Introduced the QR Code field.
- Sortable quick navigation items.
- Added Date, Datetime, and Time to quick edit fields.
- Automatic import data from ACPT Lite.
- Added disabled control to form fields.
- Added post-status management in the form settings.
- Added form redirection management in the form settings.
- Activation of the plugin via `wp-config.php`.

### Fixed

- Fixed Editor field.
- Bricks builder integration fixes.
- Gallery field improvements.

## [2.0.20] - 2025-01-23

### Added

- Added "With front" param to Custom post type Settings tab.
- Added 6 more fields (color, date, datetime, email, number, phone and url) to WooCommerce Product Data.
- Dismissable navigation in Meta fields and Option page managers.

### Fixed

- Allow iframe in Editor fields.
- Fixed error when a form is submitted by an anonymous user.
- Fixed custom post types empty supports.
- Fixed Custom Post Type fields horizontal and vertical tabs layouts.
- Fixed repeater fields conditional rendering.
- Fixed date fields nested in repeater fields.
- Improved repeater and flexible fields generation performances.
- Fixed Toogle field default value.
- Fixed null pointer exceptions.
- UI/UX fixes and improvements.

### Changed

- Improved fetching of a city from coordinates with GoogleMaps.
- Updated messages for registered custom post types.

## [2.0.19] - 2025-01-13

### Added

- Introduced the Password field.
- Added fields to WC Product Variations.

### Fixed

- Fixed Editor field when Gutenberg is enabled.
- Blocksy integration fixes.
- Fixed Phone field - intlTelInput library updated to latest version.

### Changed

- Image, Gallery, File, and Video UI/UX improvements.
- Restyling of range input (cross-browser compatible).
- Select all fields inside the edit form modal.
- Icon picker improvements.

## [2.0.19-beta-3] - 2024-12-31

### Added

- Duplicate elements feature (in bulk or singularly).

### Fixed

- Added meta-field columns to taxonomy list table.
- Fixed repeaters in term(taxonomy) loops in Bricks builder.
- Deactivating license fixes and improvements.
- RankMath SEO integration fix.

### Changed

- Complete refactoring of the logic of field groups assignments.
- Assign meta fields to WooCommerce orders.
- Post, post multiple, and relational fields added to Bricks builder loop tags.
- Improved meta fields fetching in Elementor data providers.
- WooCommerce product data field manager updated UI.

## [2.0.19-beta-2] - 2024-12-17

### Added

- Hide custom post type, taxonomies and option pages from the lists (using the localStorage).
- Added custom post types front URL prefix.
- Regenerate taxonomy and post type labels.

### Fixed

- Fixed `warning: avoid strip_tags(): Passing null to parameter #1 ($string) of type string is deprecated`.
- Fixed taxonomy loop in Breakdance.
- Breakdance data provider refactoring (reduced number of queries).
- Fixed `FetchMetaFieldValueQuery.php`.

### Changed

- Editor field improvements (added media and editor tabs buttons).
- Make available quick edit fields for bulk editing.

## [2.0.19-beta-1] - 2024-12-10

### Added

- Integration with [WP Grid Builder](https://wpgridbuilder.com/).
- Introduced the Clone field.
- Added parent page to group location options.
- Added `return => raw|object` argument to `get_acpt_field()` function.

### Fixed

- Fixed post fields rendering inside a repeater in Bricks builder.
- Fixed user meta fields in Bricks builder.
- Fixed assets loading for multisite installations (global `$pagenow` is null).
- Fixed select and select multiple form fields.
- Fixed meta box labels in horizontal and vertical tabs.
- Fixed meta fields UI/UX.
- Fixed permissions associated to a custom user role (ex. WooCommerce customer).
- Fixed singlular and plural names in CPTs and Taxonomies.
- Fixed conditional rendering with radio fields.
- Fixed the installation on DB without prefix.
- Correct the URL of French phone numbers.

### Changed

- Improved currency, length and weight default fields value selector.
- `box_name` argument now optional in `get_acpt_fields()` function.
- Reduced the number of queries in the Bricks builder data provider.

## [2.0.18] - 2024-11-12

### Added

- Saving form submissions.
- Added preview element in the Form Manager.
- Added user biography field to Forms.

### Fixed

- Fixed saving meta fields (aka sorting relational fields).
- Fixed range field.
- Fixed the front-end Editor field: TinyMCE was replaced by [Quill](https://quilljs.com/).
- Other fixes and minor improvements in forms.

### Changed

- Content settings improvements (toggle post types, taxonomies, meta, or option pages).
- The `[acpt_form]` shortcode now supports `tid`, `pid` or `uid` arguments.

## [2.0.17] - 2024-11-06

### Added

- Table field tab navigation.
- Added prefixes and suffixes to field inputs.
- Added URL field's label in forms.

### Fixed

- Fixed incompatibility with [Complianz](https://complianz.io/).
- TinyMCE correct loading in the Editor field.
- Fixed schema creation to ensure the compatibily with MySQL databases using MyISAM engine.
- Renamed `wpAjax()` function (the collision of function name makes it impossible to add new taxonomies).
- Conditional rendering in flexible fields fully working.
- Correct relations handing in ` save_acpt_meta_field_value()`.
- Nested flexible fields fully working.
- Correct data fetching of nested fields in Breakdance.
- Correct initialization of DIVI builder data provider.

### Changed

- Meta field manager micro-improvements (added show/collapse all feature).

## [2.0.16] - 2024-10-25

### Added

- New Meta field manager UI.
- New Option page manager UI.
- Permissions managing.
- Introduced the Table field.
- Added post thumbnails to forms.

### Fixed

- Fixed nested repeaters in Bricks builder.
- Fixed WooCommerce product search.
- Option pages manager fixes (fixed close and delete page actions).
- Fixed visibility checks of fields nested inside a repeater.
- Fixed meta fields import (missing labels).
- Fixed Elementor gallery fields.

### Changed

- Correct handling of custom post type `show_in_menu` setting property.
- Custom meta fields default value selector.
- Improved styling of date and time inputs.
- Breakdance fields provider improvements (including all fields).

## [2.0.16-beta-3] - 2024-10-15

### Added

- Added support for icon fields in Bricks builder.
- Added custom taxonomies to post type filters.
- Added post taxonomies field to forms.

### Fixed

- Fixed fatal error on activation.
- Avoid null pointer exception (Breakdance provider).
- Fixed rendering or URL strings in Bricks builder.

### Changed

- Improved fetching of meta fields in Bricks builder.
- Improved number field rendering in Bricks builder.
- Icon field fixes and improvements (custom SVG upload, size and color controls).
- User fields advanced settings improvements.

## [2.0.16-beta-2] - 2024-10-09

### Added

- Introduced the Table field.
- Adding user permissions to PHP functions.

### Fixed

- Fixed rendering of nested repeaters in Bricks.
- Fixed broken WooCommerce product search.
- Import fix and improvements.
- Other improvements and fixes to the Bricks integration.

### Changed

- Meta fields UI fixes and improvements.
- OP management UI fixes and improvements.
- WPML/Polylang custom settings improvements.
 
## [2.0.16-beta-1] - 2024-09-21

### Added

- New Meta field manager UI.
- New Option page manager UI.
- Permissions managing.
- Added `pid` argument to form shortcode.

### Fixed

- Prevents orphan box/fields (origing the "name is already taken" error).
- Fixed Editor fields.
- Fixed terms rendering in Bricks.
- Quick edit fields fixes and improvements.
- Fixed copy meta fields.
- Fixed date shortcode rendering.
- Added forms to Import/export tool.

### Changed

- Permissions managing.
- Conditional rendering fixes and improvements.
- Meta fields UI refactoring (now the empty boxes and/or with no visible fields are not showed as expected).
- Repeater/flexible fields UI improvements.
- Set Image fields as post thumbnail.
- Sync Taxonomy fields with post terms.
- Assets loading improvement (detection on secure protocols).

## [2.0.15] - 2024-09-03

### Added

- Multiple address field.
- Added [Cloudflare turnstile](https://www.cloudflare.com/it-it/products/turnstile/) to form field list.
- Added context and priority to meta group settings.
- Added several new meta field advanced settings.
- Added 7 new WooCommerce product data field types.

### Fixed

- Fixed Yoast integration.
- Fixed incompatibility with ActiveCampaign Postmark and other plugins.
- Conditional rules handing fixed.

### Changed

- Redesign of the appearance of meta fields.

## [2.0.15-beta-2] - 2024-08-09

### Added

- Multiple address field.
- Added layout support (image and label) to relational fields.
- Added context and priority to meta group settings.
- dded rows and cols settings to Textarea fields.
- Added 7 new WooCommerce product data field types.
- Added WYSIWY editor to Option page manager.

### Fixed

- Fixed incompatibility with [ActiveCampaign Postmark](https://wordpress.org/plugins/postmark-approved-wordpress-plugin/). 
- Migration for changing VARCHAR fields length from 50 to 255.
- Field quick edit fix and improvements.
- Conditional rules handing fixed.
 
## [2.0.15-beta-1] - 2024-07-16

### Added

- Added [Cloudflare turnstile](https://www.cloudflare.com/it-it/products/turnstile/) to form field list.
- Added advanced options to Post and Term Object fields.
- Added health check page.

### Fixed

- Fixed term object field.
- Fixed Vite assets loading (fixed `document.globals.site_url` value).
- Fixed nested image field deletion.
- Fixed WPGrapQL settings.

### Changed

- Complete refactoring of the relational field.
- Added advanced options to Post and Term Object fields.
- Translate CPT, Taxonomies, and Option page labels on [Polylang](https://polylang.pro/).
- List field UI/UX refactoring.

## [2.0.14] - 2024-07-04

### Added

- Added meta fields to post comments.
- Introduced nestable fields layouts (table, row, and block).
- Switching from Webpack to [Vite](https://vitejs.dev/): bundle size reduced by 50%.
- Introduced support for [Polylang](https://polylang.pro/) (`wpml-config.xml`).
- Added Romanian and Slovak translations.

### Fixed

- Fixed term object field.
- Fixed min and max attributes behavior in textual fields.
- Saving editor field in a new term.
- Correct Vite assets loading.
- Correct `session_start()` handling.
- Copy meta field fixes.
- Allow to copy a meta field inside a block.

### Changed

- Open Street Map integration (Address field).
- Bricks builder improvements.
- Nestable field UI/UX improvements.
- Migration versioning.
- Allow data retrieval from a certain post ID in Elementor dynamic data widgets.

## [2.0.14-beta-3] - 2024-06-28

### Added

- Introduced flexible fields layouts (table, row, and block).
- Added nested repeaters in Bricks builder.

### Fixed

- Forms fixes and small improvements.
- Fix repeater fields UI/UX.
- Fixed fetching meta.
- Using `wp_unslash` when saving metadata.
- Minor UI/UX fixes.

### Changed

- Migration versioning.

## [2.0.14-beta-2] - 2024-06-18

### Added

- Introduced repeater fields layouts (table, row, and block).
- Introduced support for [Polylang](https://polylang.pro/) (`wpml-config.xml`).
- Repeater fields preview.
- Added leading field for repeaters.
- Added accordion view in the meta field editor.

### Fixes

- Fixed API Rest integration.

### Changed

- Address field: get coordinates from clicking on the map (OSM).
- Address field: extract the city.
- Address field: added reset value function.
- Bricks builder: improved address field rendering.
- Field on comments basic CSS support.
      
## [2.0.14-beta-1] - 2024-06-07

### Added

Added Romanian and Slovak translations

### Fixed

- Fixed conditional rendering logics for toggle fields.
- Bricks builder: file field rendering improvements.
- Bricks builder: fixed Option Page flexible fields.

### Changed

- Switching from Webpack to [Vite](https://vitejs.dev/): bundle size reduced by 50%. 
- Manual posts synchronization.  
- Address field improvement: integration with [OpenStreetMap](https://www.openstreetmap.org/). 
- Conditional rendering improvements: all fields allowed to have CR rules and use of localStorage cache.
- Meta fields on archive page lists improvements.
 
## [2.0.13] - 2024-05-31     

### Fixed

- Fixed meta field assignment.
- Fixed destroy DB schema.

## [2.0.12] - 2024-05-30

### Fixed

- Fixed custom post types meta box fields assignment and rendering.
- Fixed draggable elements wrong behavior.
- Conditional field rendering fixes and improvements.

### Changed

- Keep license active after plugin deactivation.
- License page refactoring.

## [2.0.11] - 2024-05-27

### Fixed

- Avoid `Objects::cast()` fatal error.
- Input file fix (accepts document).
- Correct cache regeneration for the extracting post Ids query (allow draft and revision states).
- Fixed incompatibility with Polylang.
- Fixed incompatibility with ASE.
- Fixed sanitizing of HTML fields.
- Fixed Phone field rendering.

## [2.0.10] - 2024-05-23

### Added

- Added meta fields to Media elements.
- DB health check component.

### Fixed

- Selective cache invalidation when creating new posts.
- Relational field fixes.
- Fixed several null pointer exceptions.

### Changed

- Save/fetch meta fields refactoring.
- Conditional rendering improvements.

## [2.0.9] - 2024-05-17

### Fixed

- Fixed wrong migration.


## [2.0.8] - 2024-05-17

### Fixed

- Correct integrations loading (fixed `appLazyLoad()` function).

## [2.0.7] - 2024-05-16

### Added

- Support for [WPML](https://wpml.org/).
- Introduction of an [ErrorBoundary](https://github.com/bvaughn/react-error-boundary).
- Integration with [Query Monitor](https://querymonitor.com/wordpress-debugging/profiling-and-logging/). 
- Meta field generation logic refactoring.
- English US and English UK translations.

### Fixed

- Import fix.
- Fix `getLastPartOfUrl()` function.
- Fixed validation tab.
- Fixed import page.
- Fixed OP shortcode render.
- Fixed unit tests.
- Fix `TaxonomyMetaSync` component.
- Fixed empty relations after the plugin update.

### Changed

- Advanced options panel redesign.
- Meta fields UI/UX improvements.

## [2.0.6] - 2024-05-02

### Added

- Support for [Yoast](https://yaost.com/).
- Introduced custom datasets.
- Improved currency/length/weight fields rendering in Bricks builder.
- Avoid slug collision with reserved terms when creating new taxonomies.
- Allowing SVGs in HTML fields.
- Added max and min elements options to repeater fields.
- Introduced collapsable repeaters.
- Removing custom template support.

### Fixed

- Import fix
- Fixed [Complianz](https://complianz.io/) incompatibility.
- Fixed [Akeeba](https://www.akeeba.com/) incompatibility.
- Unexpected error when booting application correct handling.
- Saving relationship field fix.
- UI/UX fixes.

### Changed

- Reducing bundle size + optimising application performances.

## [2.0.5] - 2024-04-25

### Added

- Support for [Elementor PRO](https://elementor.com/) dynamic tags.
- Integration of repeater and flexible field in Elementor.
- Introduced the Country field.

### Fixed

- Fixed field conditional rendering tab.
- Fixed nested fields saving.
- Walk-around for not working Editor fields.
- Other minor bug fixes.

### Changed

- Correct resizing of custom post type menu icon.

## [2.0.4] - 2024-04-16

### Added

- New plugin activation flow.
- New phone number picker.

### Fixed

- Fixed OP meta fields not showing in Bricks builder.
- Fixed saving nested Editor fields.
- Meta fields validation improvements (required fields).

### Changed

- Meta field option UI enhancement.

## [2.0.3] - 2024-04-11

### Added

- Integration with [Rank Math](https://rankmath.com/).
- Support for Full Site Editing.

### Fixed

- Meta fields manager fixes.
- Fixed API playground (wrong endpoint).
- Fixed saving OP nestable fields.
- Fixed broken Media file manager.
- Fixed missing assets (html5sortable).

## [2.0.2] - 2024-04-05

### Added

- Font selector
- Hide/show all feature in option pages manager.
- Option to render email, phone, and URL fields as strings in Bricks builder.

### Fixed

- Fixed DB migration from v1.
- Fixed field group location.
- Fixed `save_acpt_meta_field_value` function.
- Gutenberg blocks correct assets loading.
- Fixed deleting tables when deactivating the plugin.
- Breakdanace repeater field fix.
- Fixed textarea field rendering (in Gutenberg, Bricks, and Breakdance).
- Using `strip_tags` in the email, phone, and URL fields rendering.

### Changed

- Meta fields manager improvements and fixes.
- Menu position UX improvement.

## [2.0.1] - 2024-03-25

### Added

- Meta fields groups, freely assignale to CTPs, Taxonomies, Users or Option Pages.
- New meta fields builder, with bulk actions (copy, duplicate, delete).
- Brand new form builder.
- 3 new Gutenberg blocks.
- Nestable repeaters and flexible fields.
- 7 new meta field types.

### Changed

- Totally new UI look.
- Relational field complete refactoring, with a totally new UI.
- Improved template handling.

## [2.0.0-beta-rc4] - 2024-02-22

### Added

- Vertical lists rendering performance improvements.
- Added bulk actions to the option page manager.
- Added bulk actions to Custom Post Types, Taxonomies, and Meta groups list page.
- Added shortcuts to create meta field groups.
- Added numeric parameter to truncate strings in Bricks builder.
- Truncate field text in Bricks builder.

### Fixed

- Fixed CPT meta box registration.
- Fixed import/export data.
- Fixed URL meta field.
- Fixed `MetaSync` component.
- Fixed The plugin generated unexpected output error.
- Fixed Site Health warnings.
- Fixed The plugin generated unexpected output error.
- Fixed Bricks builder broken language selection.
- Removed direct `mysqli()` invocation.
- Saving relational field fix.

### Changed

- WooCommerce Product Data UI fixes and improvements.

## [2.0.0-beta-rc3] - 2024-02-08

### Added

- Added `isDefault` attribute to meta field options.
- Added CSS property to meta fields advanced options panel.
- Back-end meta fields improved (and harmonized) styling.

### Fixed

- Fixed meta fields editor saving.

### Changed

- `show_in_rest` value default `TRUE` for Taxanomies.
- UI/UX improvements.
  
## [2.0.0-beta-rc2] - 2024-02-07

### Added

- Chinese translation.
- Added conditional rendering to Post Object, Term Obect, and User Object fields.
- Added OP fields to forms.
- Added relational field in Bricks builder.
- Added index to gallery field in Bricks builder (example: `{acpt_gallery:1}`).

### Fixed

- Fixed OP fields in Gutenberg widget.
- Fixed JS assets pointing in meta fields.
- Fixed several null pointer errors.
- Fixed Post Object Multi field.
- Correct `save_post` hook invoking.
- Fixed meta fields builder saving.
- Fixed DB migration from 1.0.19x.
- Fixed meta editor glitches.
- Fixed permalinks flush rules after creating a new CPT.
- Fixed Oxygen data provider.

### Changed

- Improved meta field conditional rendering: introduced live rendering on Back-end.
- Input file improvements.
- OP meta fields persisting strategy refactoring.
- UI/UX optimizations.

## [2.0.0-beta-rc1] - 2024-01-23

### Added

- Meta fields groups, freely assignale to CTPs, Taxonomies, Users or Option Pages.
- New meta fields builder, with bulk actions (copy, duplicate, delete).
- Brand new form builder.
- 3 new Gutenberg blocks.
- Nestable repeaters and flexible fields.
- 5 new meta fields: DateTime, Post Object (single or multiple) and Term Object (single or multiple).

### Changed

- Totally new UI look.
- Relational field complete refactoring, with a totally new UI.
- Improved template handling.

## [1.0.197] - 2023-12-22

### Added

- Hungarian translation.

### Fixed

- Fix taxonomy meta box name rendering.
- Fix option page builder.
- Fix update values on meta field manager (tab view).
- Fix relational field.
