=== F10 Lead Capture ===
Contributors: rafamarques, f10software
Tags: lead capture, contact form, whatsapp, crm, school management
Requires at least: 6.2
Requires PHP: 7.4
Tested up to: 7.0
Stable tag: 1.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create lead forms and floating WhatsApp capture widgets, store contacts locally, and optionally integrate with F10 Software and Brevo.

== Description ==

F10 Lead Capture provides form and floating WhatsApp lead capture management for WordPress pages, posts, landing pages, and educational marketing campaigns.

Floating WhatsApp widgets can target the whole site, selected content, or post categories. Visitors first submit their name and WhatsApp number, the lead is stored locally, and then the configured WhatsApp conversation can be opened.

== Main features ==

* Multiple independently configured forms.
* Floating WhatsApp lead capture widgets with school-oriented defaults.
* Whole-site, selected-content, or post-category targeting.
* Searchable checkbox lists for pages, content, and categories.
* Left or right position, visual effects, color, badge, and appearance delay.
* Weekly opening hours with online and offline behavior.
* Optional F10 Software API integration.
* Optional Brevo transactional email notification.
* UTM, page URL, and referrer capture.

== Installation ==

1. Upload the plugin ZIP through the WordPress Plugins screen.
2. Activate F10 Lead Capture.
3. Open F10 Leads > Settings and configure the optional integrations.
4. Open F10 Leads > Forms to configure shortcode forms.
5. Open F10 Leads > WhatsApp to add a floating WhatsApp lead capture widget.

== Shortcode ==

`[f10_lead_form]`

`[f10_lead_form id="ebook-school-management"]`

== External services ==

No lead data is sent to an optional external integration until an administrator enables and configures it.

= F10 Software API =

When enabled, the plugin sends lead information to:

`https://nuvem.f10.com.br/fx-api/digitacao`

* Service website: https://f10.com.br/
* Terms: https://f10.com.br/termos-de-uso
* Privacy: https://f10.com.br/politica-de-privacidade

= Brevo Transactional Email API =

When enabled, the plugin sends lead information to Brevo to create a transactional email.

* Service website: https://www.brevo.com/
* Terms: https://www.brevo.com/legal/termsofuse/
* Privacy: https://www.brevo.com/legal/privacypolicy/

= WhatsApp =

When a visitor submits the floating capture form, the plugin builds a `https://wa.me/` URL with the configured destination number and message.

* Service website: https://www.whatsapp.com/
* Terms: https://www.whatsapp.com/legal/terms-of-service
* Privacy: https://www.whatsapp.com/legal/privacy-policy

== Privacy ==

The plugin stores submitted lead information in the WordPress database. IP addresses are stored only as HMAC hashes for abuse prevention. The floating WhatsApp feature may store the visitor name, WhatsApp number, and expiration timestamp in browser local storage for seven days after a successful submission.

== Changelog ==

= 1.3.3 =

* Fixes the footer render order so the floating WhatsApp markup exists before its script executes.
* Defers the WhatsApp script as an additional compatibility safeguard for themes and cache plugins.

= 1.3.2 =

* Replaces multiple-selection boxes with searchable checkbox lists.
* Highlights selected pages, content, and categories.
* Centers the WhatsApp icon inside the floating button and preview.

= 1.3.1 =

* Fixes alignment and field sizing in the WhatsApp administration editor.

= 1.3.0 =

* Adds configurable floating WhatsApp lead capture widgets.

= 1.2.3 =

* Aligns the plugin text domain with the WordPress.org slug `f10-captura-de-leads`.

= 1.0.0 =

* Initial release.
