=== F10 Lead Capture ===
Contributors: rafamarques, f10software
Tags: lead capture, contact form, whatsapp, crm, school management
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.3.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create lead forms and floating WhatsApp capture widgets, store contacts locally, and optionally integrate with F10 Software and Brevo.

== Description ==

F10 Lead Capture provides form and floating WhatsApp lead capture management for WordPress pages, posts, landing pages, and educational marketing campaigns.

Each saved form can have its own title, description, button, success message, fields, required rules, source, product, and post-conversion action.

Floating WhatsApp widgets can target the whole site, selected content, or post categories. Visitors first submit their name and WhatsApp number, the lead is stored locally, and then the configured WhatsApp conversation can be opened.

Leads are stored locally before external integrations run. Administrators can review, filter, export, delete, and retry failed integrations.

= Main features =

* Multiple independently configured forms.
* Shortcodes generated for each saved form.
* Configurable name, course, phone, WhatsApp, email, school/company, and notes fields.
* Floating WhatsApp lead capture widgets with school-oriented defaults.
* WhatsApp targeting for the whole site, selected content, or post categories.
* Left or right position, four visual effects, color, badge, and appearance delay.
* Weekly opening hours with online and offline behavior.
* Live WhatsApp widget preview in the WordPress dashboard.
* Individual labels and required rules per form.
* Four built-in form appearance presets.
* Responsive desktop and mobile appearance controls.
* Post-conversion confirmation, Media Library download, destination link, or WhatsApp opening.
* Local storage before sending to external services.
* Optional F10 Software API integration.
* Optional Brevo transactional email notification.
* UTM, page URL, and referrer capture.
* Lead history, CSV export, retry workflow, honeypot, nonce, and rate limiting.

== Installation ==

1. Upload the plugin ZIP through the WordPress Plugins screen.
2. Activate F10 Lead Capture.
3. Open F10 Leads > Settings and configure the optional integrations.
4. Open F10 Leads > Forms to configure shortcode forms.
5. Open F10 Leads > WhatsApp to add a floating WhatsApp lead capture widget.
6. Copy a generated form shortcode into a WordPress Shortcode block when needed.

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

== WhatsApp ==

Open F10 Leads > WhatsApp to create, edit, duplicate, activate, deactivate, or delete floating WhatsApp widgets.

Each WhatsApp widget includes:

* an internal name and destination number;
* whole-site, selected-content, or post-category targeting;
* optional content exclusions;
* left or right position;
* static, pulse, radar, or attention visual effect;
* color, online and offline badges, and a zero-to-five-second delay;
* desktop and mobile visibility;
* school-oriented form texts and configurable message template variables;
* optional weekly business hours and offline behavior;
* a live preview in the WordPress dashboard.

The visitor submits name and WhatsApp number before WhatsApp opens. The contact is stored in the same local lead table and can use the same optional F10 Software and Brevo integrations.

Supported message variables include `{name}`, `{visitor_whatsapp}`, `{site_name}`, `{page_title}`, `{page_url}`, `{utm_source}`, and `{utm_campaign}`.

After a successful submission, the visitor data is stored in that browser for seven days so later clicks can open the configured WhatsApp conversation without requesting the same fields again. This storage is local to the visitor browser and is not used for advertising or third-party analytics.

== Appearance ==

Open F10 Leads > Appearance.

The Form tab controls presets, width, alignment, columns, spacing, colors, borders, typography, shadows, and button style.

The Post-conversion tab controls the result panel background, border, icon, title, description, spacing, button colors, width, radius, and shadow.

== Post-conversion replacement ==

After a successful form submission, the plugin replaces the complete form view with the post-conversion panel. The download or link panel is moved outside the HTML form before the original view is hidden, preventing theme or page-builder CSS from keeping both states visible.

When no download or link is configured, the same replacement panel displays the configured success confirmation.

== Local storage and retries ==

Leads are inserted into the `{prefix}_f10_leads` table before external integrations run.

The plugin stores integration status, HTTP responses, business errors, attempt counts, retry dates, and post-conversion activity.

== External services ==

No lead data is sent to an optional external integration until an administrator enables and configures it. The WhatsApp destination is opened only after the visitor explicitly submits the floating capture form or reuses previously submitted data stored in the same browser.

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

= WhatsApp =

When an administrator configures a floating WhatsApp widget and a visitor submits the widget form, the plugin builds a `https://wa.me/` URL with the configured destination number and message. Depending on the message template, this URL may contain the visitor name, submitted WhatsApp number, current page information, site name, and selected campaign parameters. The browser then navigates to the WhatsApp service.

* Service website: https://www.whatsapp.com/
* Terms: https://www.whatsapp.com/legal/terms-of-service
* Privacy: https://www.whatsapp.com/legal/privacy-policy

== Privacy ==

The plugin stores submitted lead information in the WordPress database. Site administrators are responsible for providing an appropriate privacy notice and lawful basis.

IP addresses are stored only as HMAC hashes for abuse prevention. Post-conversion events are stored locally. The plugin does not include third-party telemetry, advertising, affiliate tracking, or automatic user tracking.

The floating WhatsApp feature may store the visitor name, WhatsApp number, and expiration timestamp in browser local storage for seven days after a successful submission. This is used only to avoid asking for the same data again on later WhatsApp clicks.

== Frequently Asked Questions ==

= Can I create more than one form? =

Yes. Each form receives its own shortcode identifier.

= Can I configure more than one WhatsApp number? =

Yes. Each widget can use a different number and target the whole site, selected content, or post categories. When more than one widget matches, specific content takes precedence over category targeting, which takes precedence over a whole-site widget.

= Does the floating WhatsApp button save the lead before opening WhatsApp? =

Yes. The visitor submits name and WhatsApp number, the plugin stores the lead locally, processes enabled integrations, and then opens WhatsApp when allowed by the configured schedule.

= Can each form use different fields? =

Yes. Fields, labels, and required rules are configured independently.

= Can a form deliver a PDF or e-book? =

Yes. Select or upload the file through the WordPress Media Library in the form editor.

= Is Brevo required? =

No. Brevo notifications are optional.

= Can failed F10 requests be retried? =

Yes. Leads are stored locally and failed integrations can be retried manually or automatically.

== Changelog ==

= 1.3.7 =

* Verifies the AJAX nonce before reading WhatsApp submission or conversion-tracking data.
* Sanitizes the request payload once before field processing.
* Sanitizes the WhatsApp form-mode array before reading its fields.

= 1.3.6 =

* Adds three configurable form display modes for the floating WhatsApp widget.
* Explains the seven-day browser reuse behavior in the administration screen.
* Allows always capturing, smart one-time capture, or direct WhatsApp opening without lead capture.

= 1.3.5 =

* Aligns the WhatsApp dialog icon and close button inside the modal header.

= 1.3.4 =

* Fixes the floating form width by removing the transformed containing block after the widget appears.
* Forces the overlay to use the full viewport and keeps the dialog responsive on narrow screens.

= 1.3.3 =

* Fixes the footer render order so the floating WhatsApp markup exists before its script executes.
* Defers the WhatsApp script as an additional compatibility safeguard for themes and cache plugins.

= 1.3.2 =

* Replaces multiple-selection boxes with searchable checkbox lists.
* Highlights selected pages, content, and categories.
* Centers the WhatsApp icon inside the floating button and preview.

= 1.3.1 =

* Fixes alignment and field sizing in the WhatsApp administration editor.
* Improves responsive form layout and full-width content selectors.
* Updates the WhatsApp icon used in the widget and preview.

= 1.3.0 =

* Adds configurable floating WhatsApp lead capture widgets.
* Adds whole-site, selected-content, category, and exclusion targeting.
* Adds school-oriented defaults, business hours, online and offline states, and live preview.
* Saves WhatsApp contacts through the existing local lead and integration workflow.
* Adds WhatsApp conversion tracking and clear WhatsApp labels to the lead dashboard.

= 1.2.3 =

* Aligns the plugin text domain with the WordPress.org slug `f10-captura-de-leads`.
* Refactors CSV output so Plugin Check recognizes the dedicated non-HTML CSV escaping at the final output point.
* Documents and scopes the intentional database schema removal performed only during explicit plugin uninstallation.

= 1.2.2 =

* Rebuilt the post-conversion transition so the complete form view is replaced.
* Moves the post-conversion component outside the HTML form at runtime.
* Uses inline important visibility rules to resist theme or page-builder CSS.
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
