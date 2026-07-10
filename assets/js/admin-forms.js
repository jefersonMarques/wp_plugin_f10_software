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

    document.querySelectorAll('[data-f10-copy-shortcode]').forEach(function (button) {
        button.addEventListener('click', function () {
            copyText(button.getAttribute('data-f10-copy-shortcode') || '', button);
        });
    });

    var editor = document.querySelector('[data-f10-form-editor]');

    if (!editor) {
        return;
    }

    function selectedType() {
        var selected = editor.querySelector('[data-f10-conversion-type]:checked');
        return selected ? selected.value : 'none';
    }

    function updateConversionVisibility() {
        var type = selectedType();
        var settings = editor.querySelector('[data-f10-conversion-settings]');

        if (settings) {
            settings.hidden = type === 'none';
        }

        editor.querySelectorAll('[data-f10-source]').forEach(function (section) {
            section.hidden = section.getAttribute('data-f10-source') !== type;
        });

        var behavior = editor.querySelector('[data-f10-conversion-behavior]');
        var delay = editor.querySelector('[data-f10-delay-control]');
        if (delay) {
            delay.hidden = !behavior || behavior.value !== 'automatic';
        }
    }

    function updateFieldRows() {
        editor.querySelectorAll('[data-f10-field-row]').forEach(function (row) {
            var enabled = row.querySelector('[data-f10-field-enabled]');
            var required = row.querySelector('[data-f10-field-required]');

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
            title: '[data-f10-preview-title]',
            description: '[data-f10-preview-description]',
            button: '[data-f10-preview-button]'
        };

        Object.keys(mappings).forEach(function (key) {
            var input = editor.querySelector('[data-f10-form-preview="' + key + '"]');
            var target = editor.querySelector(mappings[key]);
            if (input && target) {
                target.textContent = input.value || '';
                target.hidden = input.value === '';
            }
        });

        var idInput = editor.querySelector('input[name="f10_form[id]"]');
        var shortcode = editor.querySelector('[data-f10-editor-shortcode]');
        if (idInput && shortcode) {
            var id = String(idInput.value || 'identificador').trim().toLowerCase().replace(/[^a-z0-9_-]+/g, '-').replace(/^-+|-+$/g, '');
            shortcode.textContent = '[f10_lead_form id="' + (id || 'identificador') + '"]';
        }
    }

    var selectButton = editor.querySelector('[data-f10-select-file]');
    var clearButton = editor.querySelector('[data-f10-clear-file]');
    var fileUrl = editor.querySelector('[data-f10-file-url]');
    var fileId = editor.querySelector('[data-f10-file-id]');
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
        if (event.target.matches('[data-f10-conversion-type], [data-f10-conversion-behavior]')) {
            updateConversionVisibility();
        }
        if (event.target.matches('[data-f10-field-enabled]')) {
            updateFieldRows();
        }
        updatePreview();
    });

    editor.addEventListener('input', updatePreview);
    updateConversionVisibility();
    updateFieldRows();
    updatePreview();
})();
