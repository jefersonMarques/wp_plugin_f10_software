<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_WhatsApp_Editor_Fields_Trait
{
    private function render_whatsapp_service_section(array $widget): void
    {
        ?>
        <section class="f10-admin-card">
            <h2>Atendimento</h2>
            <div class="f10-control-grid">
                <label class="f10-control">
                    <span>Nome interno</span>
                    <input type="text" name="f10_whatsapp[name]" value="<?php echo esc_attr($widget['name']); ?>" maxlength="190" required data-f10-whatsapp-preview-name>
                </label>
                <label class="f10-control">
                    <span>Identificador</span>
                    <input type="text" name="f10_whatsapp[id]" value="<?php echo esc_attr($widget['id']); ?>" maxlength="100" placeholder="atendimento-comercial">
                </label>
                <label class="f10-control">
                    <span>Número do WhatsApp</span>
                    <input type="tel" name="f10_whatsapp[phone]" value="<?php echo esc_attr($this->whatsapp_display_phone($widget['phone'])); ?>" maxlength="20" inputmode="tel" placeholder="(00) 00000-0000" required>
                    <small>O código do Brasil +55 será aplicado automaticamente.</small>
                </label>
                <label class="f10-checkbox-control">
                    <input type="checkbox" name="f10_whatsapp[active]" value="1" <?php checked($widget['active'], '1'); ?>>
                    <span>Atendimento ativo</span>
                </label>
            </div>
        </section>
        <?php
    }

    private function render_whatsapp_targeting_section(array $widget, array $content_options, array $category_options): void
    {
        $targeting_modes = array(
            'all' => 'Site todo',
            'specific' => 'Páginas e conteúdos específicos',
            'categories' => 'Posts de categorias selecionadas',
        );
        ?>
        <section class="f10-admin-card">
            <h2>Onde mostrar</h2>
            <div class="f10-whatsapp-targeting-options">
                <?php foreach ($targeting_modes as $value => $label) : ?>
                    <label>
                        <input type="radio" name="f10_whatsapp[targeting_mode]" value="<?php echo esc_attr($value); ?>" <?php checked($widget['targeting_mode'], $value); ?> data-f10-whatsapp-targeting>
                        <span><?php echo esc_html($label); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <?php $this->render_whatsapp_content_selector('content', 'Páginas e conteúdos', 'f10_whatsapp[content_ids][]', $content_options, $widget['content_ids'], 'specific'); ?>
            <?php $this->render_whatsapp_category_selector($category_options, $widget['category_ids']); ?>
            <?php $this->render_whatsapp_content_selector('excluded', 'Não mostrar nestas páginas', 'f10_whatsapp[excluded_content_ids][]', $content_options, $widget['excluded_content_ids'], ''); ?>
        </section>
        <?php
    }

    private function render_whatsapp_content_selector(
        string $key,
        string $label,
        string $name,
        array $options,
        array $selected_ids,
        string $target_panel
    ): void {
        ?>
        <div
            class="f10-whatsapp-selector"
            <?php if ($target_panel !== '') : ?>data-f10-whatsapp-target-panel="<?php echo esc_attr($target_panel); ?>"<?php endif; ?>
        >
            <label>
                <span><?php echo esc_html($label); ?></span>
                <input type="search" placeholder="Digite parte do título" data-f10-option-filter="<?php echo esc_attr($key); ?>">
            </label>
            <select name="<?php echo esc_attr($name); ?>" multiple size="7" data-f10-option-list="<?php echo esc_attr($key); ?>">
                <?php foreach ($options as $option) : ?>
                    <option value="<?php echo esc_attr((string) $option['id']); ?>" data-label="<?php echo esc_attr(strtolower($option['label'])); ?>" <?php selected(in_array($option['id'], $selected_ids, true)); ?>>
                        <?php echo esc_html($option['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small>Use Ctrl ou Command para selecionar mais de um item.</small>
        </div>
        <?php
    }

    private function render_whatsapp_category_selector(array $options, array $selected_ids): void
    {
        ?>
        <div class="f10-whatsapp-selector" data-f10-whatsapp-target-panel="categories">
            <label>
                <span>Categorias</span>
                <input type="search" placeholder="Digite parte do nome" data-f10-option-filter="categories">
            </label>
            <select name="f10_whatsapp[category_ids][]" multiple size="7" data-f10-option-list="categories">
                <?php foreach ($options as $option) : ?>
                    <option value="<?php echo esc_attr((string) $option['id']); ?>" data-label="<?php echo esc_attr(strtolower($option['label'])); ?>" <?php selected(in_array($option['id'], $selected_ids, true)); ?>>
                        <?php echo esc_html($option['label']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    private function render_whatsapp_appearance_section(array $widget): void
    {
        ?>
        <section class="f10-admin-card">
            <h2>Aparência</h2>
            <div class="f10-control-grid">
                <label class="f10-control">
                    <span>Posição</span>
                    <select name="f10_whatsapp[position]" data-f10-whatsapp-preview-position>
                        <option value="right" <?php selected($widget['position'], 'right'); ?>>Direita</option>
                        <option value="left" <?php selected($widget['position'], 'left'); ?>>Esquerda</option>
                    </select>
                </label>
                <label class="f10-control">
                    <span>Design</span>
                    <select name="f10_whatsapp[design]" data-f10-whatsapp-preview-design>
                        <option value="static" <?php selected($widget['design'], 'static'); ?>>Padrão</option>
                        <option value="pulse" <?php selected($widget['design'], 'pulse'); ?>>Pulsante suave</option>
                        <option value="radar" <?php selected($widget['design'], 'radar'); ?>>Radar</option>
                        <option value="attention" <?php selected($widget['design'], 'attention'); ?>>Atenção</option>
                    </select>
                </label>
                <label class="f10-control f10-whatsapp-color-control">
                    <span>Cor</span>
                    <input type="color" name="f10_whatsapp[color]" value="<?php echo esc_attr($widget['color']); ?>" data-f10-whatsapp-preview-color>
                </label>
                <label class="f10-control">
                    <span>Aparecer após</span>
                    <select name="f10_whatsapp[delay_seconds]">
                        <?php for ($second = 0; $second <= 5; $second++) : ?>
                            <option value="<?php echo esc_attr((string) $second); ?>" <?php selected((int) $widget['delay_seconds'], $second); ?>>
                                <?php echo esc_html($second === 0 ? 'Imediatamente' : $second . ' segundo(s)'); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label class="f10-control">
                    <span>Badge online</span>
                    <input type="text" name="f10_whatsapp[badge_online]" value="<?php echo esc_attr($widget['badge_online']); ?>" maxlength="80" data-f10-whatsapp-preview-badge>
                </label>
                <label class="f10-control">
                    <span>Badge offline</span>
                    <input type="text" name="f10_whatsapp[badge_offline]" value="<?php echo esc_attr($widget['badge_offline']); ?>" maxlength="80">
                </label>
                <label class="f10-checkbox-control">
                    <input type="checkbox" name="f10_whatsapp[show_desktop]" value="1" <?php checked($widget['show_desktop'], '1'); ?>>
                    <span>Mostrar no computador</span>
                </label>
                <label class="f10-checkbox-control">
                    <input type="checkbox" name="f10_whatsapp[show_mobile]" value="1" <?php checked($widget['show_mobile'], '1'); ?>>
                    <span>Mostrar no celular</span>
                </label>
            </div>
        </section>
        <?php
    }
}
