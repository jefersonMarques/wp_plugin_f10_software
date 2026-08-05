(function () {
    'use strict';

    var form = document.querySelector('[data-f10leca-appearance-form]');
    if (!form) { return; }

    var preview = form.querySelector('[data-f10leca-preview-form]');
    var stage = form.querySelector('[data-f10leca-preview-stage]');
    var variableMap = {
        form_max_width: ['--f10leca-form-max-width', 'px'], desktop_columns: ['--f10leca-desktop-columns', ''], mobile_columns: ['--f10leca-mobile-columns', ''],
        padding_desktop: ['--f10leca-padding-desktop', 'px'], padding_mobile: ['--f10leca-padding-mobile', 'px'], field_gap: ['--f10leca-field-gap', 'px'],
        form_background: ['--f10leca-form-background', ''], form_border_color: ['--f10leca-form-border-color', ''], form_border_width: ['--f10leca-form-border-width', 'px'],
        form_radius: ['--f10leca-form-radius', 'px'], form_text_color: ['--f10leca-form-text-color', ''], title_color: ['--f10leca-title-color', ''],
        description_color: ['--f10leca-description-color', ''], field_background: ['--f10leca-field-background', ''], field_border_color: ['--f10leca-field-border-color', ''],
        field_text_color: ['--f10leca-field-text-color', ''], field_radius: ['--f10leca-field-radius', 'px'], button_background: ['--f10leca-button-background', ''],
        button_hover_background: ['--f10leca-button-hover-background', ''], button_text_color: ['--f10leca-button-text-color', ''], button_radius: ['--f10leca-button-radius', 'px'],
        title_size_desktop: ['--f10leca-title-size-desktop', 'px'], title_size_mobile: ['--f10leca-title-size-mobile', 'px'], description_size: ['--f10leca-description-size', 'px'],
        conversion_background: ['--f10leca-conversion-background', ''], conversion_border_color: ['--f10leca-conversion-border-color', ''],
        conversion_border_width: ['--f10leca-conversion-border-width', 'px'], conversion_radius: ['--f10leca-conversion-radius', 'px'], conversion_padding: ['--f10leca-conversion-padding', 'px'],
        conversion_title_color: ['--f10leca-conversion-title-color', ''], conversion_description_color: ['--f10leca-conversion-description-color', ''],
        conversion_icon_color: ['--f10leca-conversion-icon-color', ''], conversion_button_background: ['--f10leca-conversion-button-background', ''],
        conversion_button_hover_background: ['--f10leca-conversion-button-hover-background', ''], conversion_button_text_color: ['--f10leca-conversion-button-text-color', ''],
        conversion_button_radius: ['--f10leca-conversion-button-radius', 'px'], conversion_title_size: ['--f10leca-conversion-title-size', 'px']
    };

    function fields() { return form.querySelectorAll('[data-f10leca-appearance-setting]'); }
    function valueOf(key) { var field = form.querySelector('[data-f10leca-appearance-setting="' + key + '"]'); return field ? field.value : ''; }
    function shadowValue(key) {
        var value = valueOf(key);
        if (value === 'none') { return 'none'; }
        if (value === 'strong') { return '0 20px 55px rgba(16,24,40,.20)'; }
        return '0 12px 32px rgba(16,24,40,.08)';
    }

    function applyPreview() {
        if (!preview) { return; }
        fields().forEach(function (field) {
            var key = field.getAttribute('data-f10leca-appearance-setting');
            var mapping = variableMap[key];
            if (mapping) { preview.style.setProperty(mapping[0], String(field.value || '') + mapping[1]); }
        });
        preview.style.setProperty('--f10leca-conversion-shadow', shadowValue('conversion_shadow'));
        preview.style.setProperty('--f10leca-conversion-button-width', valueOf('conversion_button_width') === 'full' ? '100%' : 'auto');

        var alignment = valueOf('alignment') || 'center';
        var buttonWidth = valueOf('button_width') || 'auto';
        var shadow = valueOf('shadow') || 'subtle';
        preview.classList.remove('f10leca--align-left','f10leca--align-center','f10leca--align-full','f10leca--button-full','f10leca--shadow-none','f10leca--shadow-subtle','f10leca--shadow-strong');
        preview.classList.add('f10leca--align-' + alignment);
        preview.classList.add('f10leca--shadow-' + shadow);
        if (buttonWidth === 'full') { preview.classList.add('f10leca--button-full'); }
    }

    function setValue(key, value) {
        var field = form.querySelector('[data-f10leca-appearance-setting="' + key + '"]');
        if (!field) { return; }
        field.value = value;
        var picker = form.querySelector('[data-f10leca-color-picker="' + key + '"]');
        if (picker) { picker.value = value; }
    }

    function applyPreset(presetKey) {
        var presets = window.F10LECAAppearance && window.F10LECAAppearance.presets ? window.F10LECAAppearance.presets : {};
        var preset = presets[presetKey];
        if (!preset || !preset.settings) { return; }
        Object.keys(preset.settings).forEach(function (key) { if (key !== 'preset') { setValue(key, preset.settings[key]); } });
        applyPreview();
    }

    form.addEventListener('input', function (event) {
        var target = event.target;
        if (target.matches('[data-f10leca-color-picker]')) { setValue(target.getAttribute('data-f10leca-color-picker'), target.value); }
        applyPreview();
    });
    form.querySelectorAll('[data-f10leca-preset]').forEach(function (radio) {
        radio.addEventListener('change', function () { if (radio.checked) { applyPreset(radio.value); } });
    });
    document.querySelectorAll('[data-f10leca-device]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('[data-f10leca-device]').forEach(function (item) { item.classList.remove('is-active'); });
            button.classList.add('is-active');
            if (stage) { stage.classList.toggle('is-mobile', button.getAttribute('data-f10leca-device') === 'mobile'); }
        });
    });
    applyPreview();
})();
