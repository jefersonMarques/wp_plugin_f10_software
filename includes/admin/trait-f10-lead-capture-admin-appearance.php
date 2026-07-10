<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_Appearance_Trait
{
    public function sanitize_appearance(array $input): array
    {
        $defaults = F10_Lead_Capture_Config::appearance_defaults();
        $current = F10_Lead_Capture_Config::get_appearance();
        $value = wp_parse_args($current, $defaults);
        $presets = F10_Lead_Capture_Config::appearance_presets();

        if (isset($input['preset'])) {
            $preset = sanitize_key((string) $input['preset']);
            $value['preset'] = isset($presets[$preset]) ? $preset : 'classic_f10';
        }

        $ranges = array(
            'form_max_width' => array(320, 1600), 'padding_desktop' => array(0, 100),
            'padding_mobile' => array(0, 64), 'field_gap' => array(0, 48),
            'form_border_width' => array(0, 8), 'form_radius' => array(0, 60),
            'field_radius' => array(0, 40), 'button_radius' => array(0, 40),
            'title_size_desktop' => array(18, 72), 'title_size_mobile' => array(18, 56),
            'description_size' => array(12, 24), 'conversion_border_width' => array(0, 8),
            'conversion_radius' => array(0, 60), 'conversion_padding' => array(0, 64),
            'conversion_button_radius' => array(0, 40), 'conversion_title_size' => array(16, 48),
        );
        foreach ($ranges as $key => $limits) {
            if (isset($input[$key])) {
                $value[$key] = (string) max($limits[0], min($limits[1], absint($input[$key])));
            }
        }

        $choices = array(
            'alignment' => array('left', 'center', 'full'), 'desktop_columns' => array('1', '2'),
            'mobile_columns' => array('1', '2'), 'button_width' => array('auto', 'full'),
            'shadow' => array('none', 'subtle', 'strong'), 'conversion_button_width' => array('auto', 'full'),
            'conversion_shadow' => array('none', 'subtle', 'strong'),
        );
        foreach ($choices as $key => $allowed) {
            if (!isset($input[$key])) { continue; }
            $choice = sanitize_key((string) $input[$key]);
            $value[$key] = in_array($choice, $allowed, true) ? $choice : $defaults[$key];
        }

        $colors = array(
            'form_background', 'form_border_color', 'form_text_color', 'title_color', 'description_color',
            'field_background', 'field_border_color', 'field_text_color', 'button_background',
            'button_hover_background', 'button_text_color', 'conversion_background', 'conversion_border_color',
            'conversion_title_color', 'conversion_description_color', 'conversion_icon_color',
            'conversion_button_background', 'conversion_button_hover_background', 'conversion_button_text_color',
        );
        foreach ($colors as $key) {
            if (!isset($input[$key])) { continue; }
            $color = sanitize_hex_color((string) $input[$key]);
            $value[$key] = is_string($color) && $color !== '' ? $color : $defaults[$key];
        }

        return wp_parse_args($value, $defaults);
    }

    public function render_appearance_page(): void
    {
        $this->require_capability();
        $appearance = F10_Lead_Capture_Config::get_appearance();
        $presets = F10_Lead_Capture_Config::appearance_presets();
        $tab = sanitize_key($this->query_text('tab', 30));
        $tab = $tab === 'conversion' ? 'conversion' : 'form';
        ?>
        <div class="wrap f10-admin-page">
            <h1>Aparência</h1>
            <p>Personalize o formulário e o conteúdo mostrado após a conversão. As alterações são aplicadas a todos os formulários.</p>
            <?php settings_errors(); ?>

            <nav class="nav-tab-wrapper f10-appearance-tabs">
                <a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-appearance&tab=form')); ?>" class="nav-tab <?php echo $tab === 'form' ? 'nav-tab-active' : ''; ?>">Formulário</a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-appearance&tab=conversion')); ?>" class="nav-tab <?php echo $tab === 'conversion' ? 'nav-tab-active' : ''; ?>">Pós-conversão</a>
            </nav>

            <form method="post" action="options.php" data-f10-appearance-form data-f10-appearance-tab="<?php echo esc_attr($tab); ?>">
                <?php settings_fields('f10_lead_capture_appearance_group'); ?>

                <?php if ($tab === 'form') : ?>
                    <div class="f10-admin-layout">
                        <div class="f10-admin-settings-column">
                            <section class="f10-admin-card">
                                <h2>Modelos prontos</h2><p class="description">Escolha um modelo e personalize os detalhes abaixo.</p>
                                <div class="f10-preset-grid">
                                    <?php foreach ($presets as $preset_key => $preset) : ?>
                                        <label class="f10-preset-card">
                                            <input type="radio" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[preset]" value="<?php echo esc_attr($preset_key); ?>" <?php checked($appearance['preset'], $preset_key); ?> data-f10-preset>
                                            <span class="f10-preset-card__swatches" aria-hidden="true"><span style="background:<?php echo esc_attr((string) $preset['settings']['form_background']); ?>"></span><span style="background:<?php echo esc_attr((string) $preset['settings']['button_background']); ?>"></span><span style="background:<?php echo esc_attr((string) $preset['settings']['title_color']); ?>"></span></span>
                                            <strong><?php echo esc_html((string) $preset['label']); ?></strong><small><?php echo esc_html((string) $preset['description']); ?></small>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                            <section class="f10-admin-card"><h2>Estrutura e responsividade</h2><div class="f10-control-grid">
                                <?php $this->render_number_control('Largura máxima', 'form_max_width', $appearance, 320, 1600, 'px'); ?>
                                <?php $this->render_select_control('Alinhamento', 'alignment', $appearance, array('left' => 'Esquerda', 'center' => 'Centralizado', 'full' => 'Largura total')); ?>
                                <?php $this->render_select_control('Colunas no desktop', 'desktop_columns', $appearance, array('1' => '1 coluna', '2' => '2 colunas')); ?>
                                <?php $this->render_select_control('Colunas no mobile', 'mobile_columns', $appearance, array('1' => '1 coluna', '2' => '2 colunas')); ?>
                                <?php $this->render_number_control('Espaçamento interno — desktop', 'padding_desktop', $appearance, 0, 100, 'px'); ?>
                                <?php $this->render_number_control('Espaçamento interno — mobile', 'padding_mobile', $appearance, 0, 64, 'px'); ?>
                                <?php $this->render_number_control('Espaço entre campos', 'field_gap', $appearance, 0, 48, 'px'); ?>
                                <?php $this->render_select_control('Largura do botão', 'button_width', $appearance, array('auto' => 'Automática', 'full' => 'Largura total')); ?>
                            </div></section>
                            <section class="f10-admin-card"><h2>Cores</h2><div class="f10-control-grid f10-control-grid--colors">
                                <?php $this->render_color_control('Fundo do formulário', 'form_background', $appearance); ?><?php $this->render_color_control('Borda do formulário', 'form_border_color', $appearance); ?><?php $this->render_color_control('Texto geral', 'form_text_color', $appearance); ?><?php $this->render_color_control('Título', 'title_color', $appearance); ?><?php $this->render_color_control('Descrição', 'description_color', $appearance); ?><?php $this->render_color_control('Fundo dos campos', 'field_background', $appearance); ?><?php $this->render_color_control('Borda dos campos', 'field_border_color', $appearance); ?><?php $this->render_color_control('Texto dos campos', 'field_text_color', $appearance); ?><?php $this->render_color_control('Botão', 'button_background', $appearance); ?><?php $this->render_color_control('Botão ao passar o mouse', 'button_hover_background', $appearance); ?><?php $this->render_color_control('Texto do botão', 'button_text_color', $appearance); ?>
                            </div></section>
                            <section class="f10-admin-card"><h2>Acabamento e tipografia</h2><div class="f10-control-grid">
                                <?php $this->render_number_control('Espessura da borda', 'form_border_width', $appearance, 0, 8, 'px'); ?><?php $this->render_number_control('Arredondamento do formulário', 'form_radius', $appearance, 0, 60, 'px'); ?><?php $this->render_number_control('Arredondamento dos campos', 'field_radius', $appearance, 0, 40, 'px'); ?><?php $this->render_number_control('Arredondamento do botão', 'button_radius', $appearance, 0, 40, 'px'); ?><?php $this->render_number_control('Título no desktop', 'title_size_desktop', $appearance, 18, 72, 'px'); ?><?php $this->render_number_control('Título no mobile', 'title_size_mobile', $appearance, 18, 56, 'px'); ?><?php $this->render_number_control('Texto da descrição', 'description_size', $appearance, 12, 24, 'px'); ?><?php $this->render_select_control('Sombra', 'shadow', $appearance, array('none' => 'Sem sombra', 'subtle' => 'Suave', 'strong' => 'Destacada')); ?>
                            </div></section>
                            <?php submit_button('Salvar aparência do formulário'); ?>
                        </div>
                        <aside class="f10-admin-preview-column"><?php $this->render_form_appearance_preview(); ?></aside>
                    </div>
                <?php else : ?>
                    <div class="f10-admin-layout f10-admin-layout--conversion">
                        <div class="f10-admin-settings-column">
                            <section class="f10-admin-card"><h2>Caixa de pós-conversão</h2><div class="f10-control-grid">
                                <?php $this->render_number_control('Espaçamento interno', 'conversion_padding', $appearance, 0, 64, 'px'); ?><?php $this->render_number_control('Espessura da borda', 'conversion_border_width', $appearance, 0, 8, 'px'); ?><?php $this->render_number_control('Arredondamento', 'conversion_radius', $appearance, 0, 60, 'px'); ?><?php $this->render_number_control('Tamanho do título', 'conversion_title_size', $appearance, 16, 48, 'px'); ?><?php $this->render_select_control('Sombra', 'conversion_shadow', $appearance, array('none' => 'Sem sombra', 'subtle' => 'Suave', 'strong' => 'Destacada')); ?><?php $this->render_select_control('Largura do botão', 'conversion_button_width', $appearance, array('auto' => 'Automática', 'full' => 'Largura total')); ?><?php $this->render_number_control('Arredondamento do botão', 'conversion_button_radius', $appearance, 0, 40, 'px'); ?>
                            </div></section>
                            <section class="f10-admin-card"><h2>Cores</h2><div class="f10-control-grid f10-control-grid--colors">
                                <?php $this->render_color_control('Fundo', 'conversion_background', $appearance); ?><?php $this->render_color_control('Borda', 'conversion_border_color', $appearance); ?><?php $this->render_color_control('Título', 'conversion_title_color', $appearance); ?><?php $this->render_color_control('Descrição', 'conversion_description_color', $appearance); ?><?php $this->render_color_control('Ícone', 'conversion_icon_color', $appearance); ?><?php $this->render_color_control('Botão', 'conversion_button_background', $appearance); ?><?php $this->render_color_control('Botão ao passar o mouse', 'conversion_button_hover_background', $appearance); ?><?php $this->render_color_control('Texto do botão', 'conversion_button_text_color', $appearance); ?>
                            </div></section>
                            <p class="description">Os textos e o destino da pós-conversão são configurados individualmente em <a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-forms')); ?>">Formulários</a>.</p>
                            <?php submit_button('Salvar aparência da pós-conversão'); ?>
                        </div>
                        <aside class="f10-admin-preview-column"><?php $this->render_conversion_appearance_preview(); ?></aside>
                    </div>
                <?php endif; ?>
            </form>
        </div>
        <?php
    }

    private function render_form_appearance_preview(): void
    {
        ?>
        <div class="f10-preview-toolbar"><strong>Pré-visualização</strong><div class="f10-device-switcher" role="group" aria-label="Tamanho da pré-visualização"><button type="button" class="button is-active" data-f10-device="desktop">Desktop</button><button type="button" class="button" data-f10-device="mobile">Mobile</button></div></div>
        <div class="f10-preview-stage" data-f10-preview-stage><div class="f10-lead-capture" data-f10-preview-form><div class="f10-lead-capture__header"><h2 class="f10-lead-capture__title">Fale com um especialista</h2><p class="f10-lead-capture__description">Preencha seus dados e a equipe entrará em contato.</p></div><div class="f10-lead-capture__grid"><label class="f10-lead-capture__field"><span>Nome</span><input type="text" value="Jeferson Marques" readonly></label><label class="f10-lead-capture__field"><span>WhatsApp</span><input type="text" value="(41) 99999-9999" readonly></label><label class="f10-lead-capture__field"><span>E-mail</span><input type="text" value="contato@exemplo.com.br" readonly></label><label class="f10-lead-capture__field"><span>Escola ou empresa</span><input type="text" value="Escola Exemplo" readonly></label></div><button class="f10-lead-capture__button" type="button">Quero saber mais</button></div></div>
        <?php
    }

    private function render_conversion_appearance_preview(): void
    {
        ?>
        <div class="f10-preview-toolbar"><strong>Pré-visualização</strong></div><div class="f10-preview-stage f10-preview-stage--conversion" data-f10-preview-stage><div class="f10-lead-capture f10-lead-capture--align-center f10-preview-conversion-shell" data-f10-preview-form><div class="f10-lead-capture__conversion is-visible"><span class="f10-lead-capture__conversion-icon dashicons dashicons-yes-alt" aria-hidden="true"></span><h3 class="f10-lead-capture__conversion-title">Seu conteúdo está pronto</h3><p class="f10-lead-capture__conversion-description">Clique no botão abaixo para continuar.</p><a class="f10-lead-capture__conversion-button" href="#">Baixar material</a></div></div></div>
        <?php
    }

    private function render_color_control(string $label, string $key, array $appearance): void
    {
        ?><label class="f10-control"><span><?php echo esc_html($label); ?></span><span class="f10-color-control"><input type="color" value="<?php echo esc_attr((string) $appearance[$key]); ?>" data-f10-color-picker="<?php echo esc_attr($key); ?>"><input type="text" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $appearance[$key]); ?>" maxlength="7" data-f10-appearance-setting="<?php echo esc_attr($key); ?>"></span></label><?php
    }

    private function render_number_control(string $label, string $key, array $appearance, int $min, int $max, string $suffix): void
    {
        ?><label class="f10-control"><span><?php echo esc_html($label); ?></span><span class="f10-number-control"><input type="number" min="<?php echo esc_attr((string) $min); ?>" max="<?php echo esc_attr((string) $max); ?>" name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr((string) $appearance[$key]); ?>" data-f10-appearance-setting="<?php echo esc_attr($key); ?>"><small><?php echo esc_html($suffix); ?></small></span></label><?php
    }

    private function render_select_control(string $label, string $key, array $appearance, array $options): void
    {
        ?><label class="f10-control"><span><?php echo esc_html($label); ?></span><select name="<?php echo esc_attr(self::APPEARANCE_OPTION); ?>[<?php echo esc_attr($key); ?>]" data-f10-appearance-setting="<?php echo esc_attr($key); ?>"><?php foreach ($options as $value => $option_label) : ?><option value="<?php echo esc_attr((string) $value); ?>" <?php selected((string) $appearance[$key], (string) $value); ?>><?php echo esc_html((string) $option_label); ?></option><?php endforeach; ?></select></label><?php
    }
}
