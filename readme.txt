=== F10 Lead Capture ===
Contributors: rafamarques, f10software
Tags: lead capture, contact form, crm, brevo, school management
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create multiple WordPress lead forms, track downloads and links, store contacts locally, and optionally integrate with F10 Software and Brevo.

== Description ==

F10 Lead Capture provides a form manager for WordPress pages, posts, landing pages, and educational marketing campaigns.

Each saved form can have its own title, description, button, success message, fields, required rules, source, product, and post-conversion action.

Leads are stored locally before external integrations run. Administrators can review, filter, export, delete, and retry failed integrations.

= Main features =

* Multiple independently configured forms.
* Shortcodes generated for each saved form.
* Configurable name, course, phone, WhatsApp, email, school/company, and notes fields.
* Individual labels and required rules per form.
* Four built-in appearance presets.
* Responsive desktop and mobile appearance controls.
* Post-conversion confirmation, Media Library download, or destination link.
* Manual button or automatic post-conversion behavior.
* Download and link tracking associated with each lead.
* Local storage before sending to external services.
* Optional F10 Software API integration.
* Optional Brevo transactional email notification.
* UTM, page URL, and referrer capture.
* Lead history, CSV export, retry workflow, honeypot, nonce, and rate limiting.

== Installation ==

1. Upload the plugin ZIP through the WordPress Plugins screen.
2. Activate F10 Lead Capture.
3. Open F10 Leads > Settings and configure the integrations.
4. Open F10 Leads > Forms and edit the main form or create a new form.
5. Copy the generated shortcode into a WordPress Shortcode block.

== Shortcode ==

Main form:

`[f10_lead_form]`

Specific saved form:

`[f10_lead_form id="ebook-school-management"]`

Existing shortcode attributes remain available as optional runtime overrides:

* `title`
* `description`
* `button`
* `product`
* `form_id`
* `source`
* `sub_source`
* `show_institution`
* `redirect_url`

== Forms ==

Open F10 Leads > Forms to create, edit, duplicate, activate, deactivate, or delete forms.

Each form includes:

* internal name and identifier;
* frontend title and description;
* submit button and success message;
* product, source, and subsource defaults;
* enabled, optional, and required field settings;
* confirmation-only, file-download, or destination-link post-conversion behavior.

== Appearance ==

Open F10 Leads > Appearance.

The Form tab controls presets, width, alignment, columns, spacing, colors, borders, typography, shadows, and button style.

The Post-conversion tab controls the result panel background, border, icon, title, description, spacing, button colors, width, radius, and shadow.

== Post-conversion replacement ==

After a successful submission, the plugin replaces the complete form view with the post-conversion panel. The download or link panel is moved outside the HTML form before the original view is hidden, preventing theme or page-builder CSS from keeping both states visible.

When no download or link is configured, the same replacement panel displays the configured success confirmation.

== Local storage and retries ==

Leads are inserted into the `{prefix}_f10_leads` table before external integrations run.

The plugin stores integration status, HTTP responses, business errors, attempt counts, retry dates, and post-conversion activity.

== External services ==

No lead data is sent to an external service until an administrator enables and configures that integration.

= F10 Software API =

When enabled, the plugin sends lead information to:

`https://nuvem.f10.com.br/fx-api/digitacao`

The payload may contain the configured JWT token, API type, unit ID, source, media, contact information, course or interest, school/company, notes, and capture-page information.

A successful HTTP response is also validated by its business content. F10 success requires `incluidos.digitacao` greater than zero and no `nao_incluidas` errors.

* Service website: https://f10.com.br/
* Terms: https://f10.com.br/termos-de-uso
* Privacy: https://f10.com.br/politica-de-privacidade

= Brevo Transactional Email API =

When enabled, the plugin sends lead information to Brevo to create a transactional email for the configured recipient.

* Service website: https://www.brevo.com/
* Terms: https://www.brevo.com/legal/termsofuse/
* Privacy: https://www.brevo.com/legal/privacypolicy/

== Privacy ==

The plugin stores submitted lead information in the WordPress database. Site administrators are responsible for providing an appropriate privacy notice and lawful basis.

IP addresses are stored only as HMAC hashes for abuse prevention. Post-conversion events are stored locally. The plugin does not include third-party telemetry or advertising tracking.

== Frequently Asked Questions ==

= Can I create more than one form? =

Yes. Each form receives its own shortcode identifier.

= Can each form use different fields? =

Yes. Fields, labels, and required rules are configured independently.

= Can a form deliver a PDF or e-book? =

Yes. Select or upload the file through the WordPress Media Library in the form editor.

= Is Brevo required? =

No. Brevo notifications are optional.

= Can failed F10 requests be retried? =

Yes. Leads are stored locally and failed integrations can be retried manually or automatically.

== Changelog ==

= 1.2.3 =

* Aligns the plugin text domain with the WordPress.org slug `f10-captura-de-leads`.
* Refactors CSV output so Plugin Check recognizes the dedicated non-HTML CSV escaping at the final output point.
* Documents and scopes the intentional database schema removal performed only during explicit plugin uninstallation.

= 1.2.2 =

* Rebuilt the post-conversion transition so the complete form view is replaced.
* Moves the post-conversion component outside the HTML form at runtime.
* Uses inline important visibility rules to resist theme and page-builder CSS.
* Confirmation-only forms also replace the original fields with a result panel.

= 1.2.1 =

* Added the first form-to-post-conversion view transition.
* Added standalone confirmation when no download or link is configured.

= 1.2.0 =

* Added multiple saved forms, individual fields and texts, Media Library downloads, destination links, and appearance tabs.

= 1.1.0 =

* Added appearance presets, post-conversion tracking, lead conversion statuses, and CSV fields.

= 1.0.7 =

* Added F10 business-response validation and reconciliation of false-positive successes.

= 1.0.6 =

* Fixed AJAX form endpoint resolution and added masked credential previews.

= 1.0.5 =

* Fixed required-field rendering inside WordPress REST autosaves.

= 1.0.0 =

* Initial release.
