=== Authorship Box ===
Contributors: cole
Tags: author, schema, structured data, json-ld, seo
Requires at least: 5.9
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create reusable Author profiles with full schema.org Person markup, and attach them to posts, pages, and any custom post type.

== Description ==

Authorship Box adds an "Authors" content type to WordPress. Each Author profile has its own fields — name, job title, organization, bio, photo, social profiles, education, memberships, awards, and more — and the plugin automatically turns those fields into schema.org/Person JSON-LD structured data.

Assign an Author to any post, page, or custom post type. Turn the author box (and its structured data) on globally for a whole content type from **Authors → Settings**, then override that decision per item — force it on or off for one specific post without changing the global default.

= Features =

* Author profiles as a dedicated, reusable content type — not tied to WordPress user accounts, so guest writers and editorial staff don't need logins.
* Full schema.org/Person field set: name parts & honorifics, job title, organization (worksFor), email, phone, address, gender, nationality, birth date, alma mater (alumniOf), memberships (memberOf), awards, areas of expertise (knowsAbout), social profiles (sameAs), and photo.
* Live JSON-LD preview right on the author edit screen.
* Global per-post-type toggle (Settings → choose which post types show the box by default).
* Per-item override: Default / Always show / Always hide.
* Automatic JSON-LD output in <head> for any content an author is assigned to, plus a public author profile page.
* Theme-overridable templates, a template tag (`abx_the_author_box()`), and a `[authorship_box]` shortcode.

== Installation ==

1. Upload the `authorship-box` folder to `/wp-content/plugins/`.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Go to **Authors → Add New** to create your first author profile.
4. Go to **Authors → Settings** to choose which content types show the box by default.
5. Edit any post/page/CPT item and use the "Authorship Box" panel in the sidebar to assign an author or override the display setting.

== Frequently Asked Questions ==

= Does this replace WordPress's built-in post author? =

No. It's independent, so you can assign a display "Author" persona (e.g. a guest writer, a subject-matter expert, or a shared editorial byline) separate from whichever WordPress user account technically published the content.

= Can I control where the box appears in the content? =

Yes, under Authors → Settings → Display Options, or place it manually in a template with `abx_the_author_box()`.

== Changelog ==

= 1.0.3 =
* Minor CSS tweak to the author box avatar.

= 1.0.2 =
* Fix 502/fatal on pages showing the author box: when an assigned author had no "Short Bio" and no manual excerpt, the fallback to get_the_excerpt() re-triggered the_content (which this plugin hooks to render the box), causing infinite recursion. The bio fallback no longer touches the_content, and the_content hook now guards against re-entry as a second line of defense.

= 1.0.1 =
* Fix fatal error on the Plugins screen: the bundled Plugin Update Checker library was missing its vendored Parsedown/readme-parser dependency (used to render GitHub release changelogs).

= 1.0.0 =
* Initial release.
