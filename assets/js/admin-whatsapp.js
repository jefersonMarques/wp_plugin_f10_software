(function () {
    'use strict';

    var form = document.querySelector('[data-f10leca-whatsapp-admin-form]');

    if (!form) {
        return;
    }

    function selectedTargetingMode() {
        var selected = form.querySelector('[data-f10leca-whatsapp-targeting]:checked');
        return selected ? selected.value : 'all';
    }

    function updateTargetingPanels() {
        var mode = selectedTargetingMode();

        form.querySelectorAll('[data-f10leca-whatsapp-target-panel]').forEach(function (panel) {
            panel.hidden = panel.getAttribute('data-f10leca-whatsapp-target-panel') !== mode;
        });
    }

    function initializeFilters() {
        form.querySelectorAll('[data-f10leca-option-filter]').forEach(function (filter) {
            filter.addEventListener('input', function () {
                var listName = filter.getAttribute('data-f10leca-option-filter');
                var list = form.querySelector('[data-f10leca-option-list="' + listName + '"]');
                var search = String(filter.value || '').trim().toLowerCase();

                if (!list) {
                    return;
                }

                list.querySelectorAll('[data-f10leca-option-item]').forEach(function (item) {
                    var label = String(item.getAttribute('data-label') || item.textContent || '').toLowerCase();
                    item.hidden = search !== '' && label.indexOf(search) === -1;
                });
            });
        });
    }

    function initializeChecklists() {
        form.querySelectorAll('[data-f10leca-option-item]').forEach(function (item) {
            var checkbox = item.querySelector('input[type="checkbox"]');

            if (!checkbox) {
                return;
            }

            function updateState() {
                item.classList.toggle('is-selected', checkbox.checked);
            }

            checkbox.addEventListener('change', updateState);
            updateState();
        });
    }

    function updateSchedulePanel() {
        var toggle = form.querySelector('[data-f10leca-whatsapp-schedule-toggle]');
        var panel = form.querySelector('[data-f10leca-whatsapp-schedule-panel]');

        if (toggle && panel) {
            panel.hidden = !toggle.checked;
        }
    }

    function updatePreview() {
        var widget = form.querySelector('[data-f10leca-whatsapp-preview-widget]');
        var position = form.querySelector('[data-f10leca-whatsapp-preview-position]');
        var design = form.querySelector('[data-f10leca-whatsapp-preview-design]');
        var color = form.querySelector('[data-f10leca-whatsapp-preview-color]');
        var badge = form.querySelector('[data-f10leca-whatsapp-preview-badge]');
        var badgeOutput = form.querySelector('[data-f10leca-whatsapp-preview-badge-output]');
        var title = form.querySelector('[data-f10leca-whatsapp-preview-title]');
        var titleOutput = form.querySelector('[data-f10leca-whatsapp-preview-title-output]');
        var description = form.querySelector('[data-f10leca-whatsapp-preview-description]');
        var descriptionOutput = form.querySelector('[data-f10leca-whatsapp-preview-description-output]');
        var button = form.querySelector('[data-f10leca-whatsapp-preview-button]');
        var buttonOutput = form.querySelector('[data-f10leca-whatsapp-preview-button-output]');

        if (!widget) {
            return;
        }

        widget.classList.remove(
            'f10leca-whatsapp-admin-preview__widget--left',
            'f10leca-whatsapp-admin-preview__widget--right',
            'f10leca-whatsapp-widget--static',
            'f10leca-whatsapp-widget--pulse',
            'f10leca-whatsapp-widget--radar',
            'f10leca-whatsapp-widget--attention'
        );
        widget.classList.add(
            'f10leca-whatsapp-admin-preview__widget--' + (position && position.value === 'left' ? 'left' : 'right'),
            'f10leca-whatsapp-widget--' + (design ? design.value : 'pulse')
        );

        if (color) {
            widget.style.setProperty('--f10leca-whatsapp-color', color.value || '#25D366');
        }

        if (badgeOutput && badge) {
            badgeOutput.textContent = badge.value || 'Estamos online';
        }

        if (titleOutput && title) {
            titleOutput.textContent = title.value || 'Fale com nossa escola';
        }

        if (descriptionOutput && description) {
            descriptionOutput.textContent = description.value || '';
        }

        if (buttonOutput && button) {
            buttonOutput.textContent = button.value || 'Continuar no WhatsApp';

            if (color) {
                buttonOutput.style.backgroundColor = color.value || '#25D366';
            }
        }
    }

    form.querySelectorAll('[data-f10leca-whatsapp-targeting]').forEach(function (radio) {
        radio.addEventListener('change', updateTargetingPanels);
    });

    var scheduleToggle = form.querySelector('[data-f10leca-whatsapp-schedule-toggle]');

    if (scheduleToggle) {
        scheduleToggle.addEventListener('change', updateSchedulePanel);
    }

    form.querySelectorAll(
        '[data-f10leca-whatsapp-preview-position], [data-f10leca-whatsapp-preview-design], ' +
        '[data-f10leca-whatsapp-preview-color], [data-f10leca-whatsapp-preview-badge], ' +
        '[data-f10leca-whatsapp-preview-title], [data-f10leca-whatsapp-preview-description], ' +
        '[data-f10leca-whatsapp-preview-button]'
    ).forEach(function (field) {
        field.addEventListener('input', updatePreview);
        field.addEventListener('change', updatePreview);
    });

    initializeFilters();
    initializeChecklists();
    updateTargetingPanels();
    updateSchedulePanel();
    updatePreview();
})();
