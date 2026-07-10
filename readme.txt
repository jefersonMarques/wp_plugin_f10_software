=== F10 Lead Capture ===
Contributors: f10software
Tags: lead capture, contact form, crm, brevo, school management
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Capture WordPress leads, store them locally, and optionally send them to F10 Software and Brevo.

== Description ==

F10 Lead Capture turns WordPress pages, posts, and landing pages into reliable lead-generation points.

The plugin provides a responsive shortcode form with configurable fields for name, course or interest, phone, WhatsApp, email, school or company, and notes. Administrators can enable each field independently and change the label displayed on the frontend.

Every lead is stored in a dedicated WordPress database table before any external request is made. This local-first workflow reduces the risk of losing contacts when an external API or email provider is temporarily unavailable.

Administrators can review, filter, export, delete, and resend leads from the WordPress dashboard. The plugin can also record the capture page, referrer, campaign source, and UTM parameters associated with each conversion.

The integration with F10 Software is optional and requires a valid JWT token, unit ID, source, and media supplied or configured with the F10 team. The F10 endpoint and API type are defined by the plugin. Email notifications through Brevo are also optional.

F10 Software provides solutions for school management, educational CRM, student enrollment, commercial service, finance, academic operations, and communication with students and families.

* F10 Software: https://f10.com.br/
* F10 School CRM: https://f10.com.br/solucoes/crm-escolar
* Request a demonstration: https://f10.com.br/contato
* School management content: https://blog.f10.com.br/

= Main features =

* Responsive lead form available through a shortcode.
* Configurable name, course, phone, WhatsApp, email, school or company, and notes fields.
* Each field can be enabled or disabled and can have a custom frontend label.
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

== Installation ==

1. Upload the `f10-lead-capture` directory to `/wp-content/plugins/`, or install the ZIP file through the WordPress Plugins screen.
2. Activate **F10 Lead Capture**.
3. Open **F10 Leads > Settings** in the WordPress dashboard.
4. Configure the F10 JWT token, unit ID, source, and media values when the F10 integration is required. The API endpoint is fixed by the plugin.
5. Enable Brevo notifications only when needed, then provide the API key, recipient address, and an authorized sender address.
6. Add `[f10_lead_form]` to a WordPress Shortcode block.

== Shortcode ==

Basic usage:

`[f10_lead_form]`

Customized usage:

`[f10_lead_form title="Request a demonstration" button="Contact me" product="School management software" source="F10 Blog" sub_source="Article"]`

Available attributes:

* `title`: form heading.
* `description`: supporting text displayed below the heading.
* `button`: submit button label.
* `product`: product or interest sent with the lead.
* `form_id`: internal form identifier.
* `source`: descriptive lead source.
* `sub_source`: descriptive lead subsource.
* `show_institution`: use `yes` or `no` to show or hide the institution field.
* `redirect_url`: validated URL used after a successful submission.

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

Yes. The shortcode can be added multiple times with different `form_id`, `product`, `source`, and `sub_source` values.

= Can form fields be customized? =

Yes. Administrators can enable or disable the name, course, phone, WhatsApp, email, school or company, and notes fields. Each enabled field can also use a custom frontend label without changing the technical key sent to F10 Software.

= Does the plugin automatically send usage telemetry? =

No. The plugin does not include usage telemetry, advertising trackers, or affiliate tracking.

== Changelog ==

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
