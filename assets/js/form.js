(function () {
    'use strict';

    function formatPhone(value) {
        var digits = String(value || '').replace(/\D/g, '').slice(0, 11);

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

    function setFieldValue(form, selector, value) {
        var field = form.querySelector(selector);

        if (field) {
            field.value = value || '';
        }
    }

    function populateMetadata(form) {
        var url = new URL(window.location.href);

        setFieldValue(form, '[data-f10-page-url]', window.location.href);
        setFieldValue(form, '[data-f10-referrer-url]', document.referrer);

        form.querySelectorAll('[data-f10-utm]').forEach(function (field) {
            field.value = url.searchParams.get(field.getAttribute('data-f10-utm')) || '';
        });
    }

    function setMessage(form, message, type) {
        var messageElement = form.querySelector('[data-f10-message]');

        if (!messageElement) {
            return;
        }

        messageElement.textContent = message || '';
        messageElement.classList.remove(
            'f10-lead-capture__message--success',
            'f10-lead-capture__message--error'
        );

        if (type) {
            messageElement.classList.add('f10-lead-capture__message--' + type);
        }
    }

    function setSubmitting(form, isSubmitting) {
        var button = form.querySelector('[data-f10-submit]');

        form.dataset.submitting = isSubmitting ? '1' : '0';

        if (button) {
            button.disabled = isSubmitting;
            button.classList.toggle('is-loading', isSubmitting);
        }
    }

    function trackConversion(action) {
        if (!action || !action.trackEndpoint || !action.leadId || !action.token) {
            return;
        }

        var data = new FormData();
        data.append('action', 'f10_track_conversion');
        data.append('lead_id', String(action.leadId));
        data.append('token', action.token);

        if (navigator.sendBeacon) {
            navigator.sendBeacon(action.trackEndpoint, data);
            return;
        }

        fetch(action.trackEndpoint, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {});
    }

    function openConversion(action, automatic) {
        trackConversion(action);

        if (action.type === 'download') {
            var link = document.createElement('a');
            link.href = action.url;
            link.rel = 'noopener noreferrer';
            link.download = '';

            if (!automatic && action.openNewTab) {
                link.target = '_blank';
            }

            document.body.appendChild(link);
            link.click();
            link.remove();
            return;
        }

        if (!automatic && action.openNewTab) {
            window.open(action.url, '_blank', 'noopener,noreferrer');
            return;
        }

        window.location.assign(action.url);
    }

    function setFormViewHidden(form, hidden) {
        var wrapper = form.closest('[data-f10-lead-container]');
        if (!wrapper) { return; }

        var elements = [
            wrapper.querySelector('.f10-lead-capture__header'),
            form.querySelector('.f10-lead-capture__grid'),
            form.querySelector('.f10-lead-capture__consent'),
            form.querySelector('[data-f10-submit]'),
            form.querySelector('[data-f10-message]')
        ];

        elements.forEach(function (element) {
            if (element) { element.hidden = hidden; }
        });
    }

    function resetConversionState(form) {
        var wrapper = form.closest('[data-f10-lead-container]');
        if (!wrapper) { return; }

        var container = form.querySelector('[data-f10-conversion]');

        wrapper.classList.remove('is-converted');
        setFormViewHidden(form, false);
        if (container) {
            container.hidden = true;
            container.classList.remove('is-visible');
            container.replaceChildren();
            container.style.marginTop = '';
        }
    }

    function renderConversion(form, action, successMessage) {
        var wrapper = form.closest('[data-f10-lead-container]');
        if (!wrapper) { return false; }

        var container = form.querySelector('[data-f10-conversion]');
        if (!container) { return false; }

        var hasAction = Boolean(action && action.url && (action.type === 'download' || action.type === 'link'));
        var titleText = hasAction && action.title ? action.title : 'Dados enviados com sucesso';
        var descriptionText = hasAction && action.description ? action.description : (successMessage || 'Recebemos seus dados com sucesso.');

        container.replaceChildren();

        var icon = document.createElement('span');
        icon.className = 'f10-lead-capture__conversion-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = hasAction && action.type === 'download' ? '↓' : '✓';
        container.appendChild(icon);

        if (titleText) {
            var title = document.createElement('h3');
            title.className = 'f10-lead-capture__conversion-title';
            title.textContent = titleText;
            container.appendChild(title);
        }

        if (descriptionText) {
            var description = document.createElement('p');
            description.className = 'f10-lead-capture__conversion-description';
            description.textContent = descriptionText;
            container.appendChild(description);
        }

        if (hasAction && action.behavior !== 'automatic') {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'f10-lead-capture__conversion-button';
            button.textContent = action.label || (action.type === 'download' ? 'Baixar material' : 'Acessar conteúdo');
            button.addEventListener('click', function () { openConversion(action, false); });
            container.appendChild(button);
        }

        setFormViewHidden(form, true);
        wrapper.classList.add('is-converted');
        container.style.marginTop = '0';
        container.hidden = false;
        container.classList.add('is-visible');
        try {
            container.focus({ preventScroll: true });
        } catch (error) {
            container.focus();
        }

        if (hasAction && action.behavior === 'automatic') {
            window.setTimeout(function () { openConversion(action, true); }, Number(action.delayMs || 0));
        }

        return true;
    }

    async function submitForm(form) {
        if (form.dataset.submitting === '1') {
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        setMessage(form, '', '');
        resetConversionState(form);
        setSubmitting(form, true);

        try {
            populateMetadata(form);

            var endpoint = form.getAttribute('action');

            if (!endpoint) {
                throw new Error('Endpoint do formulário não configurado.');
            }

            var response = await fetch(new URL(endpoint, window.location.href).toString(), {
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

                setMessage(form, errorMessage, 'error');
                return;
            }

            var successMessage = payload.data && payload.data.message
                ? payload.data.message
                : 'Dados recebidos com sucesso.';

            setMessage(form, '', '');
            if (!renderConversion(form, payload.data ? payload.data.conversionAction : null, successMessage)) {
                setMessage(form, successMessage, 'success');
            }
            form.reset();
            populateMetadata(form);

            var loadedAt = form.querySelector('[name="form_loaded_at"]');
            if (loadedAt) {
                loadedAt.value = String(Math.floor(Date.now() / 1000));
            }
        } catch (error) {
            setMessage(
                form,
                'Erro de conexão. Verifique sua internet e tente novamente.',
                'error'
            );
        } finally {
            setSubmitting(form, false);
        }
    }

    function initializeForm(form) {
        populateMetadata(form);

        form.querySelectorAll('[data-f10-phone]').forEach(function (phoneField) {
            phoneField.addEventListener('input', function (event) {
                event.currentTarget.value = formatPhone(event.currentTarget.value);
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            submitForm(form);
        });
    }

    function initialize() {
        document.querySelectorAll('[data-f10-lead-form]').forEach(initializeForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
})();
