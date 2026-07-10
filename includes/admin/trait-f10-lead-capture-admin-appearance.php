<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Appearance_Trait
{
    public function sanitize_appearance(array $input): array
    {
        $defaults = F10_Lead_Capture_Config::appearance_defaults();
        $presets = F10_Lead_Capture_Config::appearance_presets();
        $preset = isset($input['preset']) ? sanitize_key((string) $input['preset']) : 'classic_f10';

        if (!isset($presets[$preset])) {
            $preset = 'classic_f10';
        }

        return array(
            'preset' => $preset,
            'form_max_width' => $this->sanitize_range($input, 'form_max_width', 320, 1600, (int) $defaults['form_max_width']),
            'alignment' => $this->sanitize_choice($input, 'alignment', array('left', 'center', 'full'), $defaults['alignment']),
            'desktop_columns' => $this->sanitize_choice($input, 'desktop_columns', array('1', '2'), $defaults['desktop_columns']),
            'mobile_columns' => $this->sanitize_choice($input, 'mobile_columns', array('1', '2'), $defaults['mobile_columns']),
            'padding_desktop' => $this->sanitize_range($input, 'padding_desktop', 0, 100, (int) $defaults['padding_desktop']),
            'padding_mobile' => $this->sanitize_range($input, 'padding_mobile', 0, 64, (int) $defaults['padding_mobile']),
            'field_gap' => $this->sanitize_range($input, 'field_gap', 0, 48, (int) $defaults['field_gap']),
            'form_background' => $this->sanitize_hex_setting($input, 'form_background', $defaults['form_background']),
            'form_border_color' => $this->sanitize_hex_setting($input, 'form_border_color', $defaults['form_border_color']),
            'form_border_width' => $this->sanitize_range($input, 'form_border_width', 0, 8, (int) $defaults['form_border_width']),
            'form_radius' => $this->sanitize_range($input, 'form_radius', 0, 60, (int) $defaults['form_radius']),
            'form_text_color' => $this->sanitize_hex_setting($input, 'form_text_color', $defaults['form_text_color']),
            'title_color' => $this->sanitize_hex_setting($input, 'title_color', $defaults['title_color']),
            'description_color' => $this->sanitize_hex_setting($input, 'description_color', $defaults['description_color']),
            'field_background' => $this->sanitize_hex_setting($input, 'field_background', $defaults['field_background']),
            'field_border_color' => $this->sanitize_hex_setting($input, 'field_border_color', $defaults['field_border_color']),
            'field_text_color' => $this->sanitize_hex_setting($input, 'field_text_color', $defaults['field_text_color']),
            'field_radius' => $this->sanitize_range($input, 'field_radius', 0, 40, (int) $defaults['field_radius']),
            'button_background' => $this->sanitize_hex_setting($input, 'button_background', $defaults['button_background']),
            'button_hover_background' => $this->sanitize_hex_setting($input, 'button_hover_background', $defaults['button_hover_background']),
            'button_text_color' => $this->sanitize_hex_setting($input, 'button_text_color', $defaults['button_text_color']),
            'button_radius' => $this->sanitize_range($input, 'button_radius', 0, 40, (int) $defaults['button_radius']),
            'button_width' => $this->sanitize_choice($input, 'button_width', array('auto', 'full'), $defaults['button_width']),
            'title_size_desktop' => $this->sanitize_range($input, 'title_size_desktop', 18, 72, (int) $defaults['title_size_desktop']),
            'title_size_mobile' => $this->sanitize_range($input, 'title_size_mobile', 18, 56, (int) $defaults['title_size_mobile']),
            'description_size' => $this->sanitize_range($input, 'description_size', 12, 24, (int) $defaults['description_size']),
            'shadow' => $this->sanitize_choice($input, 'shadow', array('none', 'subtle', 'strong'), $defaults['shadow']),
        );
    }

    public function render_appearance_page(): void
    {
        $this->require_capability();
        $appearance = F10_Lead_Capture_Config::get_appearance();
        $presets = F10_Lead_Capture_Config::appearance_presets();
        ?>
        <div class="wrap f10-admin-page">
            <h1>Aparência do formulário</h1>
            <p>Escolha um modelo pronto e ajuste o formulário para desktop e mobile. As alterações são aplicadas a todos os shortcodes existentes.</p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php" data-f10-appearance-form>
                <?php settings_fields('f10_lead_capture_appearance_group'); ?>

                <div class="f10-admin-layout">
                    <div class="f10-admin-settings-column">
                        <section class="f10-admin-card">
                            <h2>Modelos prontos</h2>
                            <p class="description">Selecionar um modelo preenche os controles abaixo. Depois, qualquer detalhe pode ser personalizado.</p>
                            <div class="f10-preset-grid">
                                <?php foreach ($presets as $preset_key => $preset) : ?>
                                    <label class="f10-preset-card">
                                        <input type="radio" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[preset]" value="<?php echo esc_attr($preset_key); ?>" <?php checked($appearance['preset'], $preset_key); ?> data-f10-preset>
                                        <span class="f10-preset-card__swatches" aria-hidden="true">
                                            <span style="background:<?php echo esc_attr((string) $preset['settings']['form_background']); ?>"></span>
                                            <span style="background:<?php echo esc_attr((string) $preset['settings']['button_background']); ?>"></span>
                                            <span style="background:<?php echo esc_attr((string) $preset['settings']['title_color']); ?>"></span>
                                        </span>
                                        <strong><?php echo esc_html((string) $preset['label']); ?></strong>
                                        <small><?php echo esc_html((string) $preset['description']); ?></small>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Estrutura e responsividade</h2>
                            <div class="f10-control-grid">
                                <?php $this->render_number_control('Largura máxima', 'form_max_width', $appearance, 320, 1600, 'px'); ?>
                                <?php $this->render_select_control('Alinhamento', 'alignment', $appearance, array('left' => 'Esquerda', 'center' => 'Centralizado', 'full' => 'Largura total')); ?>
                                <?php $this->render_select_control('Colunas no desktop', 'desktop_columns', $appearance, array('1' => '1 coluna', '2' => '2 colunas')); ?>
                                <?php $this->render_select_control('Colunas no mobile', 'mobile_columns', $appearance, array('1' => '1 coluna', '2' => '2 colunas')); ?>
                                <?php $this->render_number_control('Espaçamento interno — desktop', 'padding_desktop', $appearance, 0, 100, 'px'); ?>
                                <?php $this->render_number_control('Espaçamento interno — mobile', 'padding_mobile', $appearance, 0, 64, 'px'); ?>
                                <?php $this->render_number_control('Espaço entre campos', 'field_gap', $appearance, 0, 48, 'px'); ?>
                                <?php $this->render_select_control('Largura do botão', 'button_width', $appearance, array('auto' => 'Automática', 'full' => 'Largura total')); ?>
                            </div>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Cores</h2>
                            <div class="f10-control-grid f10-control-grid--colors">
                                <?php $this->render_color_control('Fundo do formulário', 'form_background', $appearance); ?>
                                <?php $this->render_color_control('Borda do formulário', 'form_border_color', $appearance); ?>
                                <?php $this->render_color_control('Texto geral', 'form_text_color', $appearance); ?>
                                <?php $this->render_color_control('Título', 'title_color', $appearance); ?>
                                <?php $this->render_color_control('Descrição', 'description_color', $appearance); ?>
                                <?php $this->render_color_control('Fundo dos campos', 'field_background', $appearance); ?>
                                <?php $this->render_color_control('Borda dos campos', 'field_border_color', $appearance); ?>
                                <?php $this->render_color_control('Texto dos campos', 'field_text_color', $appearance); ?>
                                <?php $this->render_color_control('Botão', 'button_background', $appearance); ?>
                                <?php $this->render_color_control('Botão ao passar o mouse', 'button_hover_background', $appearance); ?>
                                <?php $this->render_color_control('Texto do botão', 'button_text_color', $appearance); ?>
                            </div>
                        </section>

                        <section class="f10-admin-card">
                            <h2>Acabamento e tipografia</h2>
                            <div class="f10-control-grid">
                                <?php $this->render_number_control('Espessura da borda', 'form_border_width', $appearance, 0, 8, 'px'); ?>
                                <?php $this->render_number_control('Arredondamento do formulário', 'form_radius', $appearance, 0, 60, 'px'); ?>
                                <?php $this->render_number_control('Arredondamento dos campos', 'field_radius', $appearance, 0, 40, 'px'); ?>
                                <?php $this->render_number_control('Arredondamento do botão', 'button_radius', $appearance, 0, 40, 'px'); ?>
                                <?php $this->render_number_control('Título no desktop', 'title_size_desktop', $appearance, 18, 72, 'px'); ?>
                                <?php $this->render_number_control('Título no mobile', 'title_size_mobile', $appearance, 18, 56, 'px'); ?>
                                <?php $this->render_number_control('Texto da descrição', 'description_size', $appearance, 12, 24, 'px'); ?>
                                <?php $this->render_select_control('Sombra', 'shadow', $appearance, array('none' => 'Sem sombra', 'subtle' => 'Suave', 'strong' => 'Destacada')); ?>
                            </div>
                        </section>

                        <?php submit_button('Salvar aparência'); ?>
                    </div>

                    <aside class="f10-admin-preview-column">
                        <div class="f10-preview-toolbar">
                            <strong>Pré-visualização</strong>
                            <div class="f10-device-switcher" role="group" aria-label="Tamanho da pré-visualização">
                                <button type="button" class="button is-active" data-f10-device="desktop">Desktop</button>
                                <button type="button" class="button" data-f10-device="mobile">Mobile</button>
                            </div>
                        </div>
                        <div class="f10-preview-stage" data-f10-preview-stage>
                            <div class="f10-lead-capture" data-f10-preview-form>
                                <div class="f10-lead-capture__header">
                                    <h2 class="f10-lead-capture__title">Fale com um especialista</h2>
                                    <p class="f10-lead-capture__description">Preencha seus dados e a equipe entrará em contato.</p>
                                </div>
                                <div class="f10-lead-capture__grid">
                                    <label class="f10-lead-capture__field"><span>Nome</span><input type="text" value="Jeferson Marques" readonly></label>
                                    <label class="f10-lead-capture__field"><span>WhatsApp</span><input type="text" value="(41) 99999-9999" readonly></label>
                                    <label class="f10-lead-capture__field"><span>E-mail</span><input type="text" value="contato@exemplo.com.br" readonly></label>
                                    <label class="f10-lead-capture__field"><span>Escola ou empresa</span><input type="text" value="Escola Exemplo" readonly></label>
                                </div>
                                <button class="f10-lead-capture__button" type="button">Quero saber mais</button>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
        <?php
    }

    private function render_color_control(string $label, string $key, array $appearance): void
    {
        ?>
        <label class="f10-control">
            <span><?php echo esc_html($label); ?></span>
            <span class="f10-color-control">
                <input type="color" value="<?php echo esc_attr((string) $appearance[$key]); ?>" data-f10-color-picker="<?php echo esc_attr($key); ?>">
                <input type="text" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $appearance[$key]); ?>" maxlength="7" data-f10-appearance-setting="<?php echo esc_attr($key); ?>">
            </span>
        </label>
        <?php
    }

    private function render_number_control(string $label, string $key, array $appearance, int $min, int $max, string $suffix): void
    {
        ?>
        <label class="f10-control">
            <span><?php echo esc_html($label); ?></span>
            <span class="f10-number-control">
                <input type="number" min="<?php echo esc_attr((string) $min); ?>" max="<?php echo esc_attr((string) $max); ?>" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $appearance[$key]); ?>" data-f10-appearance-setting="<?php echo esc_attr($key); ?>">
                <small><?php echo esc_html($suffix); ?></small>
            </span>
        </label>
        <?php
    }

    private function render_select_control(string $label, string $key, array $appearance, array $options): void
    {
        ?>
        <label class="f10-control">
            <span><?php echo esc_html($label); ?></span>
            <select name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" data-f10-appearance-setting="<?php echo esc_attr($key); ?>">
                <?php foreach ($options as $value => $option_label) : ?>
                    <option value="<?php echo esc_attr((string) $value); ?>" <?php selected((string) $appearance[$key], (string) $value); ?>><?php echo esc_html((string) $option_label); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

    private function sanitize_hex_setting(array $input, string $key, string $default): string
    {
        $value = isset($input[$key]) ? sanitize_hex_color((string) $input[$key]) : null;
        return is_string($value) && $value !== '' ? $value : $default;
    }

    private function sanitize_range(array $input, string $key, int $min, int $max, int $default): string
    {
        if (!isset($input[$key])) {
            return (string) $default;
        }

        return (string) max($min, min($max, absint($input[$key])));
    }

    private function sanitize_choice(array $input, string $key, array $allowed, string $default): string
    {
        $value = isset($input[$key]) ? sanitize_key((string) $input[$key]) : $default;
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
