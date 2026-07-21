(function () {
    'use strict';

    var config = window.F10LeadWhatsApp || null;
    var root = document.querySelector('[data-f10-whatsapp-widget]');

    if (!config || !config.widget || !root || root.dataset.f10FormModeReady === '1') {
        return;
    }

    root.dataset.f10FormModeReady = '1';

    var widget = config.widget;
    var mode = ['always', 'smart', 'never'].indexOf(String(widget.form_display_mode || 'smart')) !== -1
        ? String(widget.form_display_mode || 'smart')
        : 'smart';

    if (mode === 'smart') {
        return;
    }

    var form = root.querySelector('[data-f10-whatsapp-form]');
    var overlay = root.querySelector('[data-f10-whatsapp-overlay]');
    var dialog = root.querySelector('[data-f10-whatsapp-dialog]');

    function storageKey() {
        return 'f10LeadWhatsApp:' + String(widget.id || 'default');
    }

    function storedVisitor() {
        try {
            var raw = window.localStorage.getItem(storageKey());
            var stored = raw ? JSON.parse(raw) : null;

            if (!stored || Number(stored.expiresAt || 0) <= Math.floor(Date.now() / 1000)) {
                window.localStorage.removeItem(storageKey());
                return null;
            }

            return stored;
        } catch (error) {
            return null;
        }
    }

    function normalizePhone(value) {
        var digits = String(value || '').replace(/\D/g, '');

        if (digits.length >= 10 && digits.length <= 11) {
            digits = '55' + digits;
        }

        return digits;
    }

    function formatPhone(value) {
        var digits = String(value || '').replace(/\D/g, '');

        if (digits.indexOf('55') === 0 && digits.length > 11) {
            digits = digits.slice(2);
        }

        digits = digits.slice(0, 11);

        if (digits.length <= 2) {
            return digits;
        }

        if (digits.length <= 6) {
            return '(' + digits.slice(0, 2) + ') ' + digits.slice(2);
        }

        if (digits.length <= 10) {
            return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 6) + '-' + digits.slice(6);
        }

        return '(' + digits.slice(0, 2) + ') ' + digits.slice(2, 7) + '-' + digits.slice(7);
    }

    function populateMetadata() {
        if (!form) {
            return;
        }

        var currentUrl = new URL(window.location.href);
        var mappings = {
            '[data-f10-whatsapp-page-url]': window.location.href,
            '[data-f10-whatsapp-referrer-url]': document.referrer,
            '[data-f10-whatsapp-page-title]': document.title || ''
        };

        Object.keys(mappings).forEach(function (selector) {
            var field = form.querySelector(selector);

            if (field) {
                field.value = mappings[selector];
            }
        });

        form.querySelectorAll('[data-f10-whatsapp-utm]').forEach(function (field) {
            field.value = currentUrl.searchParams.get(field.getAttribute('data-f10-whatsapp-utm')) || '';
        });

        var loadedAt = form.querySelector('[name="form_loaded_at"]');

        if (loadedAt) {
            loadedAt.value = String(Math.floor(Date.now() / 1000));
        }
    }

    function prefill(visitor) {
        if (!form || !visitor) {
            return;
        }

        var nameField = form.querySelector('[name="name"]');
        var whatsappField = form.querySelector('[name="whatsapp"]');

        if (nameField) {
            nameField.value = String(visitor.name || '');
        }

        if (whatsappField) {
            whatsappField.value = formatPhone(visitor.whatsapp || '');
        }
    }

    function openForm() {
        if (!overlay || !dialog) {
            return;
        }

        populateMetadata();
        prefill(storedVisitor());
        overlay.hidden = false;
        overlay.classList.add('is-visible');
        document.documentElement.classList.add('f10-whatsapp-modal-open');

        window.setTimeout(function () {
            var firstInput = dialog.querySelector('input:not([type="hidden"]):not([tabindex="-1"])');

            if (firstInput) {
                firstInput.focus();
            }
        }, 30);
    }

    function directUrl() {
        var currentUrl = new URL(window.location.href);
        var replacements = {
            '{name}': '',
            '{visitor_whatsapp}': '',
            '{site_name}': String(config.siteName || ''),
            '{page_title}': String(document.title || ''),
            '{page_url}': window.location.href,
            '{utm_source}': currentUrl.searchParams.get('utm_source') || '',
            '{utm_campaign}': currentUrl.searchParams.get('utm_campaign') || ''
        };
        var text = String(widget.message_template || '');

        Object.keys(replacements).forEach(function (placeholder) {
            text = text.split(placeholder).join(replacements[placeholder]);
        });

        var phone = normalizePhone(widget.phone);
        return phone ? 'https://wa.me/' + encodeURIComponent(phone) + '?text=' + encodeURIComponent(text.trim()) : '';
    }

    function intercept(event) {
        event.preventDefault();
        event.stopImmediatePropagation();

        if (mode === 'never') {
            var url = directUrl();

            if (url) {
                window.location.assign(url);
            }
            return;
        }

        openForm();
    }

    root.querySelectorAll('[data-f10-whatsapp-trigger], [data-f10-whatsapp-badge]').forEach(function (element) {
        element.addEventListener('click', intercept, true);
    });
})();
