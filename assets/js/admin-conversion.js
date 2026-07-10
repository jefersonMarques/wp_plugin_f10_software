(function () {
    'use strict';

    var form = document.querySelector('[data-f10-conversion-form]');

    if (!form) {
        return;
    }

    var enabled = form.querySelector('[data-f10-conversion-enabled]');
    var settings = form.querySelector('[data-f10-conversion-settings]');
    var fileUrl = form.querySelector('[data-f10-file-url]');
    var fileId = form.querySelector('[data-f10-file-id]');
    var mediaFrame = null;

    function selectedType() {
        var selected = form.querySelector('[data-f10-conversion-type]:checked');
        return selected ? selected.value : 'download';
    }

    function selectedBehavior() {
        var selected = form.querySelector('[data-f10-conversion-behavior]');
        return selected ? selected.value : 'button';
    }

    function updateVisibility() {
        settings.hidden = !enabled.checked;

        form.querySelectorAll('[data-f10-source]').forEach(function (section) {
            section.hidden = section.getAttribute('data-f10-source') !== selectedType();
        });

        var delayControl = form.querySelector('[data-f10-delay-control]');
        if (delayControl) {
            delayControl.hidden = selectedBehavior() !== 'automatic';
        }

        updatePreview();
    }

    function updatePreview() {
        var title = form.querySelector('[data-f10-conversion-input="title"]');
        var description = form.querySelector('[data-f10-conversion-input="description"]');
        var label = form.querySelector('[data-f10-conversion-input="label"]');
        var preview = form.querySelector('[data-f10-conversion-preview]');

        if (!preview) {
            return;
        }

        preview.style.opacity = enabled.checked ? '1' : '.45';
        preview.querySelector('[data-f10-preview-title]').textContent = title && title.value ? title.value : 'Seu conteúdo está pronto';
        preview.querySelector('[data-f10-preview-description]').textContent = description && description.value ? description.value : 'Clique no botão abaixo para continuar.';
        preview.querySelector('[data-f10-preview-label]').textContent = label && label.value ? label.value : 'Continuar';
        preview.querySelector('[data-f10-preview-meta]').textContent = selectedType() === 'download'
            ? 'O clique será registrado como download.'
            : 'O clique será registrado como acesso ao link.';
    }

    enabled.addEventListener('change', updateVisibility);
    form.querySelectorAll('[data-f10-conversion-type]').forEach(function (field) {
        field.addEventListener('change', updateVisibility);
    });
    form.querySelector('[data-f10-conversion-behavior]').addEventListener('change', updateVisibility);
    form.addEventListener('input', updatePreview);

    var selectFile = form.querySelector('[data-f10-select-file]');
    var clearFile = form.querySelector('[data-f10-clear-file]');

    if (fileUrl) {
        fileUrl.addEventListener('input', function () {
            fileId.value = '';
        });
    }

    if (selectFile) {
        selectFile.addEventListener('click', function () {
            if (mediaFrame) {
                mediaFrame.open();
                return;
            }

            mediaFrame = window.wp.media({
                title: 'Selecionar material para download',
                button: { text: 'Usar este arquivo' },
                multiple: false
            });

            mediaFrame.on('select', function () {
                var attachment = mediaFrame.state().get('selection').first().toJSON();
                fileId.value = attachment.id || '';
                fileUrl.value = attachment.url || '';
                updatePreview();
            });

            mediaFrame.open();
        });
    }

    if (clearFile) {
        clearFile.addEventListener('click', function () {
            fileId.value = '';
            fileUrl.value = '';
        });
    }

    updateVisibility();
})();
