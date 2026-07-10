=== F10 Lead Capture ===
Contributors: f10software
Tags: lead capture, contact form, crm, brevo, school management
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Capture WordPress leads, store them locally, and optionally send them to F10 Software and Brevo.

== Description ==

F10 Lead Capture turns WordPress pages, posts, and landing pages into reliable lead-generation points.

The plugin provides a form manager for creating multiple responsive shortcode forms. Each form can have its own heading, description, button text, fields, labels, required rules, lead context, and post-conversion action.

Every lead is stored in a dedicated WordPress database table before any external request is made. This local-first workflow reduces the risk of losing contacts when an external API or email provider is temporarily unavailable.

Administrators can review, filter, export, delete, and resend leads from the WordPress dashboard. The plugin can also record the capture page, referrer, campaign source, and UTM parameters associated with each conversion.

The integration with F10 Software is optional and requires a valid JWT token, unit ID, source, and media supplied or configured with the F10 team. The F10 endpoint and API type are defined by the plugin. Email notifications through Brevo are also optional.

F10 Software provides solutions for school management, educational CRM, student enrollment, commercial service, finance, academic operations, and communication with students and families.

* F10 Software: https://f10.com.br/
* F10 School CRM: https://f10.com.br/solucoes/crm-escolar
* Request a demonstration: https://f10.com.br/contato
* School management content: https://blog.f10.com.br/

= Main features =

* Multiple forms managed from a dedicated Forms screen.
* Individual title, description, submit button, success message, source, and product settings per form.
* Configurable name, course, phone, WhatsApp, email, school or company, and notes fields per form.
* Each field can be enabled, required, optional, or given a custom frontend label.
* Local database storage before external integrations run.
* Optional authenticated integration with the F10 Software API.
* Optional transactional email notification through Brevo.
* Automatic capture of page URL, referrer, and UTM parameters.
* Administrative lead history with filters and technical details.
* CSV export protected against spreadsheet formula injection.
* Manual resend and automatic retry through WP-Cron.
* Nonce validation, honeypot protection, request rate limiting, and input sanitization.
* Configurable consent text.
* Hashed IP storage for abuse prevention.
* Four built-in appearance presets with responsive desktop and mobile controls.
* Custom colors, spacing, widths, borders, shadows, typography, and button styles.
* Individual post-conversion download or link action per form.
* Download and link click tracking stored with each lead and included in CSV exports.

== Installation ==

1. Upload the `f10-lead-capture` directory to `/wp-content/plugins/`, or install the ZIP file through the WordPress Plugins screen.
2. Activate **F10 Lead Capture**.
3. Open **F10 Leads > Settings** in the WordPress dashboard.
4. Configure the F10 JWT token, unit ID, source, and media values when the F10 integration is required. The API endpoint is fixed by the plugin.
5. Enable Brevo notifications only when needed, then provide the API key, recipient address, and an authorized sender address.
6. Open **F10 Leads > Forms** to create or edit forms.
7. Copy the generated shortcode into a WordPress Shortcode block.

== Shortcode ==

The main migrated form remains available through:

`[f10_lead_form]`

Every form created in **F10 Leads > Forms** receives its own shortcode:

`[f10_lead_form id="ebook-school-management"]`

The `id` selects the saved form. Existing attributes remain supported as optional runtime overrides:

* `title`: form heading.
* `description`: supporting text displayed below the heading.
* `button`: submit button label.
* `product`: product or interest sent with the lead.
* `form_id`: reporting identifier stored with the lead.
* `source`: descriptive lead source.
* `sub_source`: descriptive lead subsource.
* `show_institution`: use `yes` or `no` to show or hide the institution field.
* `redirect_url`: validated URL that overrides the saved post-conversion action.

== Forms ==

Open **F10 Leads > Forms** to create, edit, duplicate, activate, deactivate, or delete forms. Each form contains:

* an internal name and stable identifier;
* frontend title, description, submit button, and success message;
* product, source, and subsource defaults;
* individual enabled and required field settings;
* a post-conversion action: confirmation only, Media Library download, or destination link;
* manual button or automatic opening behavior.

The original global field settings are migrated into the main form during the update.

== Appearance ==

Open **F10 Leads > Appearance**. The screen now has two tabs:

* **Form**: presets, responsive columns, width, spacing, colors, borders, typography, shadows, and button style.
* **Post-conversion**: background, border, spacing, icon, title, description, button colors, radius, width, and shadow.

The built-in presets are Classic F10, Minimal, Soft, and Dark. Appearance changes apply to all existing saved forms without editing posts or pages.

Post-conversion content and destinations are configured inside each form. The lead record stores the action type, destination, first action time, and total action count. The lead list and CSV export show whether the visitor triggered the download or link.

== Local storage and retries ==

Leads are inserted into the `{prefix}_f10_leads` table before any external integration runs.

The plugin stores the current integration status, HTTP response, error message, number of attempts, last attempt time, and next scheduled retry. An integration that has already completed successfully is not called again during a retry.

== External services ==

This plugin can connect to two external services. No lead data is sent to either service until a WordPress administrator enables and configures the corresponding integration.

= F10 Software API =

When the F10 integration is enabled, the plugin sends lead information to the fixed endpoint `https://nuvem.f10.com.br/fx-api/digitacao`.

The request uses a flat JSON payload and may contain:

* JWT token supplied by the site administrator;
* API type, always set to `2`;
* F10 unit ID, source, and media;
* name;
* course or interest;
* phone number;
* WhatsApp or mobile number;
* email address;
* school or company name;
* visitor notes and lead context;
* capture page path in `extra1`;
* full capture page URL in `extra2`.

The JWT token is sent in the request body because this is required by the F10 API contract. The information is sent to register the contact in F10 Software. Transmission occurs after a visitor submits the form and may occur again during a manual or automatic retry when a previous request failed.

A valid F10 Software account and integration credentials are required.

* Service website: https://f10.com.br/
* Terms of use: https://f10.com.br/termos-de-uso
* Privacy policy: https://f10.com.br/politica-de-privacidade

= Brevo Transactional Email API =

When Brevo notifications are enabled, the plugin sends lead information to Brevo to generate a transactional email for the recipient configured by the administrator. The transmitted data may include:

* name;
* phone number;
* WhatsApp number;
* email address;
* school or company name;
* product or interest;
* visitor notes;
* capture page URL;
* referrer URL;
* source and subsource;
* UTM parameters;
* lead creation date.

Transmission occurs after a visitor submits the form and may occur again during a manual or automatic retry when a previous request failed.

A Brevo account, API key, and authorized sender address are required.

* Service website: https://www.brevo.com/
* Terms of use: https://www.brevo.com/legal/termsofuse/
* Privacy policy: https://www.brevo.com/legal/privacypolicy/

== Privacy ==

The plugin stores submitted lead data in the WordPress database. Site administrators are responsible for providing an appropriate privacy notice and establishing a lawful basis for collecting and processing personal data.

The visitor's IP address is not stored as plain text. The plugin creates an HMAC hash of the address for temporary abuse-prevention controls.

Post-conversion action events are stored locally in the WordPress database. The plugin records the action type, first trigger time, and trigger count; it does not add third-party analytics or telemetry.

The option to delete the plugin table during uninstallation is disabled by default. Data is removed only when an administrator explicitly enables that setting before uninstalling the plugin.

== Frequently Asked Questions ==

= Can a lead be lost when an external API is unavailable? =

The lead is stored in WordPress before the external request is attempted. Failed integrations can be retried manually or through the automatic retry process.

= Is Brevo required? =

No. Brevo notifications are optional and remain disabled until an administrator enables and configures them.

= Is an F10 Software account required? =

An F10 Software account and valid credentials are required only for sending leads to the F10 API. The local form and database storage can operate according to the plugin configuration without that integration.

= Does the plugin capture UTM parameters? =

Yes. The plugin records UTM Source, UTM Medium, UTM Campaign, UTM Term, and UTM Content when they are available in the capture URL.

= Can more than one form be used? =

Yes. Create multiple entries in **F10 Leads > Forms** and use the generated `id` shortcode for each campaign, page, or downloadable material.

= Can form fields be customized? =

Yes. Every saved form has independent enabled, required, optional, and frontend-label settings for name, course, phone, WhatsApp, email, school or company, and notes.

= Does the plugin automatically send usage telemetry? =

No. The plugin does not include usage telemetry, advertising trackers, or affiliate tracking.

== Changelog ==

= 1.2.0 =

* Added a Forms submenu with list, create, edit, duplicate, activate, deactivate, and delete workflows.
* Moved title, description, button text, success message, fields, labels, and required rules into each form.
* Moved downloads and destination links into each form, with Media Library upload and selection.
* Replaced the separate Post-conversion menu with Forms.
* Added Form and Post-conversion tabs to the Appearance screen.
* Added configurable post-conversion colors, spacing, radius, typography, button style, icon, and shadow.
* Migrates the previous global field and post-conversion settings into the main form without changing existing shortcodes.
* Added form names and identifiers to lead details and CSV exports.

= 1.1.0 =

* Added an Appearance submenu with four presets and responsive desktop/mobile controls.
* Added live previews for form appearance and post-conversion content.
* Added configurable post-conversion downloads and links.
* Added Media Library selection for downloadable files.
* Added click tracking for downloads and links, including timestamps and counters in lead details, lists, and CSV exports.
* Added automatic database migration for post-conversion tracking fields.
* Fixed an extra SQL pagination argument in the lead repository.

= 1.0.7 =

* Validates the F10 business response instead of relying only on the HTTP status.
* Treats a response as successful only when `incluidos.digitacao` is greater than zero and no `nao_incluidas` errors are present.
* Marks the overall lead as failed when the primary F10 integration fails, even if the Brevo notification succeeds.
* Reconciles previously stored false-positive F10 successes during the plugin update.

= 1.0.6 =

* Fixed form submissions incorrectly requesting `/[object HTMLInputElement]` instead of WordPress `admin-ajax.php`.
* The JavaScript now reads the literal form action attribute, avoiding collisions with the hidden `action` field.
* Added masked previews for saved F10 JWT tokens and Brevo API keys in the settings screen.

= 1.0.5 =

* Fixed a fatal error when rendering required configurable form fields inside the block editor and REST API autosaves.
* Replaced an invalid required() function call with the native HTML required attribute.

= 1.0.4 =

* Updated the F10 integration to use the fixed official endpoint and flat JSON payload.
* Added configurable frontend fields and custom labels.
* Added phone and notes storage with an automatic database migration.
* Added contextual help for the F10 token, unit ID, source, and media settings.

= 1.0.3 =

* Replaced dynamically assembled SQL with prepared queries and identifier placeholders.
* Added object caching for individual lead lookups and explicit cache invalidation.
* Removed direct PHP file operations from CSV export.
* Added spreadsheet formula-injection protection to CSV cells.
* Removed nonce scanner warnings from administrative query handling and AJAX helpers.
* Updated the uninstall query to use a prepared table identifier.
* Rewrote the WordPress.org readme in standard English.
* Removed duplicated pagination markup and duplicated lead detail fields.

= 1.0.2 =

* Standardized the public plugin name as F10 Lead Capture.
* Added the GPL license headers.
* Removed external update headers for WordPress.org compatibility.
* Documented external services and transmitted data.
* Removed hidden files from the distribution package.

= 1.0.1 =

* Improved public metadata and documentation for F10 Software, school CRM, lead capture, and Brevo.

= 1.0.0 =

* Initial public release.
* Added shortcode form and local lead storage.
* Added optional F10 Software and Brevo integrations.
* Added administrative history, CSV export, and retry processing.
