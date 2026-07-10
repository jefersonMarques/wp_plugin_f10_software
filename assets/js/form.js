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

    function getFormElements(form) {
        var wrapper = form.closest('[data-f10-lead-container]');

        if (!wrapper) {
            return null;
        }

        var view = wrapper.querySelector('[data-f10-form-view]');
        var conversion = wrapper.querySelector('[data-f10-conversion]');

        if (!view) {
            view = document.createElement('div');
            view.className = 'f10-lead-capture__view';
            view.setAttribute('data-f10-form-view', '');
            wrapper.insertBefore(view, wrapper.firstChild);

            var header = wrapper.querySelector('.f10-lead-capture__header');
            if (header) {
                view.appendChild(header);
            }

            view.appendChild(form);
        }

        if (conversion && conversion.parentNode !== wrapper) {
            wrapper.appendChild(conversion);
        }

        return {
            wrapper: wrapper,
            view: view,
            conversion: conversion
        };
    }

    function setFormViewHidden(form, hidden) {
        var elements = getFormElements(form);

        if (!elements || !elements.view) {
            return;
        }

        elements.view.hidden = hidden;
        elements.view.setAttribute('aria-hidden', hidden ? 'true' : 'false');

        if (hidden) {
            elements.view.style.setProperty('display', 'none', 'important');
        } else {
            elements.view.style.removeProperty('display');
        }
    }

    function resetConversionState(form) {
        var elements = getFormElements(form);

        if (!elements) {
            return;
        }

        elements.wrapper.classList.remove('is-converted');
        setFormViewHidden(form, false);

        if (elements.conversion) {
            elements.conversion.hidden = true;
            elements.conversion.setAttribute('aria-hidden', 'true');
            elements.conversion.classList.remove('is-visible');
            elements.conversion.replaceChildren();
            elements.conversion.style.removeProperty('display');
        }
    }

    function renderConversion(form, action, successMessage) {
        var elements = getFormElements(form);

        if (!elements || !elements.view || !elements.conversion) {
            return false;
        }

        var container = elements.conversion;
        var hasAction = Boolean(action && action.url && (action.type === 'download' || action.type === 'link'));
        var titleText = hasAction && action.title ? action.title : 'Dados enviados com sucesso';
        var descriptionText = hasAction && action.description
            ? action.description
            : (successMessage || 'Recebemos seus dados com sucesso.');

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
        elements.wrapper.classList.add('is-converted');
        container.hidden = false;
        container.setAttribute('aria-hidden', 'false');
        container.classList.add('is-visible');
        container.style.setProperty('display', 'block', 'important');

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