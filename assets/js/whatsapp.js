(function () {
    'use strict';

    var config = window.F10LeadWhatsApp || null;
    var root = document.querySelector('[data-f10-whatsapp-widget]');

    if (!config || !config.widget || !root) {
        return;
    }

    var widget = config.widget;
    var trigger = root.querySelector('[data-f10-whatsapp-trigger]');
    var badge = root.querySelector('[data-f10-whatsapp-badge]');
    var overlay = root.querySelector('[data-f10-whatsapp-overlay]');
    var dialog = root.querySelector('[data-f10-whatsapp-dialog]');
    var closeButton = root.querySelector('[data-f10-whatsapp-close]');
    var form = root.querySelector('[data-f10-whatsapp-form]');
    var messageElement = root.querySelector('[data-f10-whatsapp-message]');
    var descriptionElement = root.querySelector('[data-f10-whatsapp-description]');
    var submitButton = root.querySelector('[data-f10-whatsapp-submit]');
    var lastFocusedElement = null;
    var state = resolveScheduleState();

    function formatPhone(value) {
        var digits = String(value || '').replace(/\D/g, '').slice(0, 13);

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

    function normalizePhone(value) {
        var digits = String(value || '').replace(/\D/g, '');

        if (digits.length >= 10 && digits.length <= 11) {
            digits = '55' + digits;
        }

        return digits;
    }

    function resolveScheduleState() {
        if (String(widget.schedule_enabled) !== '1') {
            return { online: true, behavior: 'open' };
        }

        try {
            var formatter = new Intl.DateTimeFormat('en-US', {
                timeZone: config.timezone || undefined,
                weekday: 'short',
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23'
            });
            var parts = formatter.formatToParts(new Date());
            var values = {};

            parts.forEach(function (part) {
                values[part.type] = part.value;
            });

            var weekdays = {
                Mon: '1',
                Tue: '2',
                Wed: '3',
                Thu: '4',
                Fri: '5',
                Sat: '6',
                Sun: '7'
            };
            var day = widget.schedule && widget.schedule[weekdays[values.weekday]];
            var online = false;

            if (day && String(day.enabled) === '1') {
                var current = (Number(values.hour) * 60) + Number(values.minute);
                var startParts = String(day.start || '08:00').split(':');
                var endParts = String(day.end || '18:00').split(':');
                var start = (Number(startParts[0]) * 60) + Number(startParts[1]);
                var end = (Number(endParts[0]) * 60) + Number(endParts[1]);

                online = start <= end
                    ? current >= start && current <= end
                    : current >= start || current <= end;
            }

            return {
                online: online,
                behavior: online ? 'open' : String(widget.outside_behavior || 'open')
            };
        } catch (error) {
            return {
                online: Boolean(config.serverOnline),
                behavior: config.serverOnline ? 'open' : String(widget.outside_behavior || 'open')
            };
        }
    }

    function isDeviceAllowed() {
        var isMobile = window.matchMedia('(max-width: 767px)').matches;

        return isMobile
            ? String(widget.show_mobile) === '1'
            : String(widget.show_desktop) === '1';
    }

    function populateMetadata() {
        if (!form) {
            return;
        }

        var currentUrl = new URL(window.location.href);
        var pageUrl = form.querySelector('[data-f10-whatsapp-page-url]');
        var referrerUrl = form.querySelector('[data-f10-whatsapp-referrer-url]');
        var pageTitle = form.querySelector('[data-f10-whatsapp-page-title]');

        if (pageUrl) {
            pageUrl.value = window.location.href;
        }

        if (referrerUrl) {
            referrerUrl.value = document.referrer;
        }

        if (pageTitle) {
            pageTitle.value = document.title || '';
        }

        form.querySelectorAll('[data-f10-whatsapp-utm]').forEach(function (field) {
            field.value = currentUrl.searchParams.get(field.getAttribute('data-f10-whatsapp-utm')) || '';
        });
    }

    function setMessage(message, type) {
        if (!messageElement) {
            return;
        }

        messageElement.textContent = message || '';
        messageElement.classList.remove(
            'f10-whatsapp-widget__message--error',
            'f10-whatsapp-widget__message--success'
        );

        if (type) {
            messageElement.classList.add('f10-whatsapp-widget__message--' + type);
        }
    }

    function setSubmitting(isSubmitting) {
        if (!submitButton) {
            return;
        }

        submitButton.disabled = isSubmitting;
        submitButton.classList.toggle('is-loading', isSubmitting);
    }

    function getStorageKey() {
        return 'f10LeadWhatsApp:' + String(widget.id || 'default');
    }

    function getStoredVisitor() {
        try {
            var raw = window.localStorage.getItem(getStorageKey());
            var stored = raw ? JSON.parse(raw) : null;

            if (!stored || Number(stored.expiresAt || 0) <= Math.floor(Date.now() / 1000)) {
                window.localStorage.removeItem(getStorageKey());
                return null;
            }

            return stored;
        } catch (error) {
            return null;
        }
    }

    function storeVisitor(data) {
        try {
            window.localStorage.setItem(getStorageKey(), JSON.stringify(data));
        } catch (error) {
            return;
        }
    }

    function interpolateMessage(visitor) {
        var currentUrl = new URL(window.location.href);
        var replacements = {
            '{name}': String(visitor.name || ''),
            '{visitor_whatsapp}': String(visitor.whatsapp || ''),
            '{site_name}': String(config.siteName || ''),
            '{page_title}': String(document.title || ''),
            '{page_url}': window.location.href,
            '{utm_source}': currentUrl.searchParams.get('utm_source') || '',
            '{utm_campaign}': currentUrl.searchParams.get('utm_campaign') || ''
        };
        var template = String(widget.message_template || '');

        Object.keys(replacements).forEach(function (placeholder) {
            template = template.split(placeholder).join(replacements[placeholder]);
        });

        return template.trim();
    }

    function buildWhatsAppUrl(visitor) {
        var phone = normalizePhone(widget.phone);
        var text = interpolateMessage(visitor);

        return phone ? 'https://wa.me/' + encodeURIComponent(phone) + '?text=' + encodeURIComponent(text) : '';
    }

    function openWhatsApp(url) {
        if (!url) {
            return;
        }

        window.location.assign(url);
    }

    function openDialog() {
        if (!overlay || !dialog) {
            return;
        }

        lastFocusedElement = document.activeElement;
        overlay.hidden = false;
        overlay.classList.add('is-visible');
        document.documentElement.classList.add('f10-whatsapp-modal-open');
        populateMetadata();

        window.setTimeout(function () {
            var firstInput = dialog.querySelector('input:not([type="hidden"]):not([tabindex="-1"])');

            if (firstInput) {
                firstInput.focus();
            }
        }, 30);
    }

    function closeDialog() {
        if (!overlay) {
            return;
        }

        overlay.classList.remove('is-visible');
        overlay.hidden = true;
        document.documentElement.classList.remove('f10-whatsapp-modal-open');

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    }

    function handleTrigger() {
        state = resolveScheduleState();

        if (!state.online && state.behavior === 'hide') {
            return;
        }

        var stored = getStoredVisitor();

        if (stored && (state.online || state.behavior === 'open')) {
            openWhatsApp(buildWhatsAppUrl(stored));
            return;
        }

        openDialog();
    }

    function trackWhatsAppOpen(payload) {
        if (!payload || !payload.trackEndpoint || !payload.leadId || !payload.token) {
            return;
        }

        var data = new FormData();
        data.append('action', 'f10_lead_capture_track_whatsapp');
        data.append('lead_id', String(payload.leadId));
        data.append('token', String(payload.token));

        if (navigator.sendBeacon) {
            navigator.sendBeacon(payload.trackEndpoint, data);
            return;
        }

        fetch(payload.trackEndpoint, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {});
    }

    async function submitForm(event) {
        event.preventDefault();

        if (!form || !form.reportValidity()) {
            return;
        }

        setMessage('', '');
        setSubmitting(true);
        populateMetadata();

        try {
            var response = await fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json'
                }
            });
            var payload = await response.json().catch(function () {
                return null;
            });

            if (!response.ok || !payload || !payload.success) {
                var errorMessage = payload && payload.data && payload.data.message
                    ? payload.data.message
                    : 'Não foi possível enviar seus dados. Tente novamente.';
                setMessage(errorMessage, 'error');
                return;
            }

            var data = payload.data || {};
            setMessage(data.message || 'Dados registrados com sucesso.', 'success');

            if (data.reuse) {
                storeVisitor(data.reuse);
            }

            if (data.shouldOpen && data.whatsappUrl) {
                trackWhatsAppOpen(data);
                window.setTimeout(function () {
                    openWhatsApp(data.whatsappUrl);
                }, 250);
                return;
            }

            window.setTimeout(closeDialog, 1200);
        } catch (error) {
            setMessage('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
        } finally {
            setSubmitting(false);

            var loadedAt = form.querySelector('[name="form_loaded_at"]');

            if (loadedAt) {
                loadedAt.value = String(Math.floor(Date.now() / 1000));
            }
        }
    }

    function trapFocus(event) {
        if (event.key !== 'Tab' || !dialog || !overlay || overlay.hidden) {
            return;
        }

        var focusable = Array.prototype.slice.call(
            dialog.querySelectorAll(
                'button:not([disabled]), input:not([disabled]):not([type="hidden"]):not([tabindex="-1"]), textarea:not([disabled]), select:not([disabled]), a[href]'
            )
        );

        if (!focusable.length) {
            return;
        }

        var first = focusable[0];
        var last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    }

    function initialize() {
        state = resolveScheduleState();

        if (!isDeviceAllowed() || (!state.online && state.behavior === 'hide')) {
            return;
        }

        if (badge) {
            badge.textContent = state.online
                ? String(widget.badge_online || 'Estamos online')
                : String(widget.badge_offline || 'Deixe seus dados');
        }

        if (descriptionElement) {
            descriptionElement.textContent = state.online
                ? String(widget.form_description || '')
                : String(widget.form_offline_description || widget.form_description || '');
        }

        var delay = Math.max(0, Math.min(5, Number(widget.delay_seconds || 0))) * 1000;

        window.setTimeout(function () {
            root.hidden = false;
            root.classList.add('is-visible');
        }, delay);

        if (trigger) {
            trigger.addEventListener('click', handleTrigger);
        }

        if (badge) {
            badge.addEventListener('click', handleTrigger);
        }

        if (closeButton) {
            closeButton.addEventListener('click', closeDialog);
        }

        if (overlay) {
            overlay.addEventListener('click', function (event) {
                if (event.target === overlay) {
                    closeDialog();
                }
            });
        }

        if (form) {
            form.addEventListener('submit', submitForm);

            form.querySelectorAll('[data-f10-whatsapp-phone]').forEach(function (field) {
                field.addEventListener('input', function (event) {
                    event.currentTarget.value = formatPhone(event.currentTarget.value);
                });
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && overlay && !overlay.hidden) {
                closeDialog();
                return;
            }

            trapFocus(event);
        });
    }

    initialize();
})();
