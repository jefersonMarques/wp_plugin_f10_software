(function () {
    'use strict';

    function copyText(text, button) {
        if (!text) {
            return;
        }

        var done = function () {
            var original = button.textContent;
            button.textContent = 'Copiado';
            window.setTimeout(function () {
                button.textContent = original;
            }, 1200);
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function () {});
            return;
        }

        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        textarea.remove();
        done();
    }

    document.querySelectorAll('[data-f10leca-copy-shortcode]').forEach(function (button) {
        button.addEventListener('click', function () {
            copyText(button.getAttribute('data-f10leca-copy-shortcode') || '', button);
        });
    });

    var editor = document.querySelector('[data-f10leca-form-editor]');

    if (!editor) {
        return;
    }

    function selectedType() {
        var selected = editor.querySelector('[data-f10leca-conversion-type]:checked');
        return selected ? selected.value : 'none';
    }

    function updateConversionVisibility() {
        var type = selectedType();
        var settings = editor.querySelector('[data-f10leca-conversion-settings]');

        if (settings) {
            settings.hidden = type === 'none';
        }

        editor.querySelectorAll('[data-f10leca-source]').forEach(function (section) {
            section.hidden = section.getAttribute('data-f10leca-source') !== type;
        });

        var behavior = editor.querySelector('[data-f10leca-conversion-behavior]');
        var delay = editor.querySelector('[data-f10leca-delay-control]');
        if (delay) {
            delay.hidden = !behavior || behavior.value !== 'automatic';
        }
    }

    function updateFieldRows() {
        editor.querySelectorAll('[data-f10leca-field-row]').forEach(function (row) {
            var enabled = row.querySelector('[data-f10leca-field-enabled]');
            var required = row.querySelector('[data-f10leca-field-required]');

            if (!enabled || !required) {
                return;
            }

            required.disabled = !enabled.checked;
            if (!enabled.checked) {
                required.checked = false;
            }
        });
    }

    function updatePreview() {
        var mappings = {
            title: '[data-f10leca-preview-title]',
            description: '[data-f10leca-preview-description]',
            button: '[data-f10leca-preview-button]'
        };

        Object.keys(mappings).forEach(function (key) {
            var input = editor.querySelector('[data-f10leca-form-preview="' + key + '"]');
            var target = editor.querySelector(mappings[key]);
            if (input && target) {
                target.textContent = input.value || '';
                target.hidden = input.value === '';
            }
        });

        var idInput = editor.querySelector('input[name="f10leca_form[id]"]');
        var shortcode = editor.querySelector('[data-f10leca-editor-shortcode]');
        if (idInput && shortcode) {
            var id = String(idInput.value || 'identificador').trim().toLowerCase().replace(/[^a-z0-9_-]+/g, '-').replace(/^-+|-+$/g, '');
            shortcode.textContent = '[f10leca_lead_form id="' + (id || 'identificador') + '"]';
        }
    }

    var selectButton = editor.querySelector('[data-f10leca-select-file]');
    var clearButton = editor.querySelector('[data-f10leca-clear-file]');
    var fileUrl = editor.querySelector('[data-f10leca-file-url]');
    var fileId = editor.querySelector('[data-f10leca-file-id]');
    var mediaFrame = null;

    if (selectButton && window.wp && wp.media) {
        selectButton.addEventListener('click', function () {
            if (!mediaFrame) {
                mediaFrame = wp.media({
                    title: 'Selecionar arquivo para download',
                    button: { text: 'Usar este arquivo' },
                    multiple: false
                });

                mediaFrame.on('select', function () {
                    var attachment = mediaFrame.state().get('selection').first().toJSON();
                    if (fileUrl) {
                        fileUrl.value = attachment.url || '';
                    }
                    if (fileId) {
                        fileId.value = String(attachment.id || 0);
                    }
                });
            }

            mediaFrame.open();
        });
    }

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            if (fileUrl) {
                fileUrl.value = '';
            }
            if (fileId) {
                fileId.value = '0';
            }
        });
    }

    editor.addEventListener('change', function (event) {
        if (event.target.matches('[data-f10leca-conversion-type], [data-f10leca-conversion-behavior]')) {
            updateConversionVisibility();
        }
        if (event.target.matches('[data-f10leca-field-enabled]')) {
            updateFieldRows();
        }
        updatePreview();
    });

    editor.addEventListener('input', updatePreview);
    updateConversionVisibility();
    updateFieldRows();
    updatePreview();
})();
