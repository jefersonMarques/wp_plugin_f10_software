(function () {
    'use strict';

    var form = document.querySelector('[data-f10-appearance-form]');
    var preview = document.querySelector('[data-f10-preview-form]');
    var stage = document.querySelector('[data-f10-preview-stage]');

    if (!form || !preview || !stage) {
        return;
    }

    var variableMap = {
        form_max_width: ['--f10-form-max-width', 'px'],
        desktop_columns: ['--f10-desktop-columns', ''],
        mobile_columns: ['--f10-mobile-columns', ''],
        padding_desktop: ['--f10-padding-desktop', 'px'],
        padding_mobile: ['--f10-padding-mobile', 'px'],
        field_gap: ['--f10-field-gap', 'px'],
        form_background: ['--f10-form-background', ''],
        form_border_color: ['--f10-form-border-color', ''],
        form_border_width: ['--f10-form-border-width', 'px'],
        form_radius: ['--f10-form-radius', 'px'],
        form_text_color: ['--f10-form-text-color', ''],
        title_color: ['--f10-title-color', ''],
        description_color: ['--f10-description-color', ''],
        field_background: ['--f10-field-background', ''],
        field_border_color: ['--f10-field-border-color', ''],
        field_text_color: ['--f10-field-text-color', ''],
        field_radius: ['--f10-field-radius', 'px'],
        button_background: ['--f10-button-background', ''],
        button_hover_background: ['--f10-button-hover-background', ''],
        button_text_color: ['--f10-button-text-color', ''],
        button_radius: ['--f10-button-radius', 'px'],
        title_size_desktop: ['--f10-title-size-desktop', 'px'],
        title_size_mobile: ['--f10-title-size-mobile', 'px'],
        description_size: ['--f10-description-size', 'px']
    };

    function fields() {
        return form.querySelectorAll('[data-f10-appearance-setting]');
    }

    function applyPreview() {
        fields().forEach(function (field) {
            var key = field.getAttribute('data-f10-appearance-setting');
            var mapping = variableMap[key];

            if (mapping) {
                preview.style.setProperty(mapping[0], String(field.value || '') + mapping[1]);
            }
        });

        var alignment = valueOf('alignment');
        var buttonWidth = valueOf('button_width');
        var shadow = valueOf('shadow');

        preview.classList.remove(
            'f10-lead-capture--align-left',
            'f10-lead-capture--align-center',
            'f10-lead-capture--align-full',
            'f10-lead-capture--button-full',
            'f10-lead-capture--shadow-none',
            'f10-lead-capture--shadow-subtle',
            'f10-lead-capture--shadow-strong'
        );

        preview.classList.add('f10-lead-capture--align-' + alignment);
        preview.classList.add('f10-lead-capture--shadow-' + shadow);

        if (buttonWidth === 'full') {
            preview.classList.add('f10-lead-capture--button-full');
        }
    }

    function valueOf(key) {
        var field = form.querySelector('[data-f10-appearance-setting="' + key + '"]');
        return field ? field.value : '';
    }

    function setValue(key, value) {
        var field = form.querySelector('[data-f10-appearance-setting="' + key + '"]');

        if (!field) {
            return;
        }

        field.value = value;

        var picker = form.querySelector('[data-f10-color-picker="' + key + '"]');
        if (picker) {
            picker.value = value;
        }
    }

    function applyPreset(presetKey) {
        var presets = window.F10LeadAppearance && window.F10LeadAppearance.presets
            ? window.F10LeadAppearance.presets
            : {};
        var preset = presets[presetKey];

        if (!preset || !preset.settings) {
            return;
        }

        Object.keys(preset.settings).forEach(function (key) {
            if (key !== 'preset') {
                setValue(key, preset.settings[key]);
            }
        });

        applyPreview();
    }

    form.addEventListener('input', function (event) {
        var target = event.target;

        if (target.matches('[data-f10-color-picker]')) {
            setValue(target.getAttribute('data-f10-color-picker'), target.value);
        }

        applyPreview();
    });

    form.querySelectorAll('[data-f10-preset]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked) {
                applyPreset(radio.value);
            }
        });
    });

    document.querySelectorAll('[data-f10-device]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('[data-f10-device]').forEach(function (item) {
                item.classList.remove('is-active');
            });
            button.classList.add('is-active');
            stage.classList.toggle('is-mobile', button.getAttribute('data-f10-device') === 'mobile');
        });
    });

    applyPreview();
})();
