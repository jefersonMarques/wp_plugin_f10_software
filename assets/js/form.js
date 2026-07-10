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

    async function submitForm(form) {
        if (form.dataset.submitting === '1') {
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        setMessage(form, '', '');
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

            setMessage(form, successMessage, 'success');
            form.reset();
            populateMetadata(form);

            var loadedAt = form.querySelector('[name="form_loaded_at"]');
            if (loadedAt) {
                loadedAt.value = String(Math.floor(Date.now() / 1000));
            }

            if (payload.data && payload.data.redirectUrl) {
                window.setTimeout(function () {
                    window.location.assign(payload.data.redirectUrl);
                }, 700);
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