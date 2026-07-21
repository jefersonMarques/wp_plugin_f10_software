<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_WhatsApp_Lead_Labels_Trait
{
    private function lead_form_name(string $form_id): string
    {
        if (strpos($form_id, 'whatsapp-') === 0) {
            $widget_id = substr($form_id, strlen('whatsapp-'));
            $widget = F10_Lead_Capture_WhatsApp_Config::get_widget($widget_id);

            return is_array($widget)
                ? 'WhatsApp: ' . (string) $widget['name']
                : 'WhatsApp removido ou legado';
        }

        $form = F10_Lead_Capture_Config::get_form($form_id);

        return is_array($form) ? (string) $form['name'] : 'Formulário removido ou legado';
    }

    private function render_conversion_status(array $lead): void
    {
        $type = (string) ($lead['conversion_type'] ?? 'none');
        $status = (string) ($lead['conversion_status'] ?? 'none');
        $count = (int) ($lead['conversion_count'] ?? 0);

        if ($type === 'none') {
            echo '<span style="color:#646970">—</span>';
            return;
        }

        if ($status === 'completed') {
            $label = $type === 'download' ? 'Baixou' : ($type === 'whatsapp' ? 'Abriu WhatsApp' : 'Acessou');
            printf(
                '<span style="display:inline-block;padding:4px 8px;border-radius:999px;color:#067647;background:#ecfdf3;font-weight:600">%s%s</span>',
                esc_html($label),
                $count > 1 ? esc_html(' (' . $count . 'x)') : ''
            );
            return;
        }

        $label = $type === 'download' ? 'Download pendente' : ($type === 'whatsapp' ? 'WhatsApp pendente' : 'Acesso pendente');
        printf(
            '<span style="display:inline-block;padding:4px 8px;border-radius:999px;color:#b54708;background:#fffaeb;font-weight:600">%s</span>',
            esc_html($label)
        );
    }

    private function conversion_type_label(string $type): string
    {
        return array(
            'download' => 'Download de arquivo',
            'link' => 'Abertura de link',
            'whatsapp' => 'Abertura do WhatsApp',
            'none' => 'Nenhuma',
        )[$type] ?? $type;
    }

    private function conversion_status_label(string $status, string $type): string
    {
        if ($status === 'completed') {
            return $type === 'download'
                ? 'Download acionado'
                : ($type === 'whatsapp' ? 'WhatsApp aberto' : 'Link acessado');
        }

        if ($status === 'pending') {
            return 'Aguardando acionamento';
        }

        return 'Sem ação configurada';
    }
}
