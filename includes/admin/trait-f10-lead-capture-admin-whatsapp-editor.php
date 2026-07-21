<?php

if (!defined('ABSPATH')) {
    exit;
}

trait F10_Lead_Capture_Admin_WhatsApp_Editor_Trait
{
    private function render_whatsapp_editor(string $widget_id): void
    {
        $widgets = F10_Lead_Capture_WhatsApp_Config::get_widgets();
        $is_new = $widget_id === '';
        $widget = $is_new
            ? F10_Lead_Capture_WhatsApp_Config::default_widget()
            : ($widgets[$widget_id] ?? null);

        if (!is_array($widget)) {
            echo '<div class="wrap"><h1>WhatsApp não encontrado</h1></div>';
            return;
        }

        if ($is_new) {
            $widget['id'] = '';
        }

        $content_options = $this->whatsapp_content_options();
        $category_options = $this->whatsapp_category_options();
        ?>
        <div class="wrap f10-admin-page">
            <h1><?php echo esc_html($is_new ? 'Adicionar WhatsApp' : 'Editar WhatsApp'); ?></h1>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=f10-lead-whatsapp')); ?>">← Voltar para a lista</a></p>
            <?php $this->render_whatsapp_notice(); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-f10-whatsapp-admin-form>
                <input type="hidden" name="action" value="f10_lead_capture_save_whatsapp">
                <input type="hidden" name="f10_whatsapp[original_id]" value="<?php echo esc_attr($is_new ? '' : $widget['id']); ?>">
                <?php wp_nonce_field('f10_lead_capture_save_whatsapp'); ?>

                <div class="f10-whatsapp-admin-layout">
                    <div class="f10-whatsapp-admin-settings">
                        <?php $this->render_whatsapp_service_section($widget); ?>
                        <?php $this->render_whatsapp_targeting_section($widget, $content_options, $category_options); ?>
                        <?php $this->render_whatsapp_appearance_section($widget); ?>
                        <?php $this->render_whatsapp_form_section($widget); ?>
                        <?php $this->render_whatsapp_schedule_section($widget); ?>
                        <?php submit_button($is_new ? 'Adicionar WhatsApp' : 'Salvar WhatsApp'); ?>
                    </div>
                    <?php $this->render_whatsapp_preview($widget); ?>
                </div>
            </form>
        </div>
        <?php
    }

    private function whatsapp_content_options(): array
    {
        $post_types = get_post_types(array('public' => true), 'names');
        unset($post_types['attachment']);
        $posts = get_posts(
            array(
                'post_type' => array_values($post_types),
                'post_status' => 'publish',
                'numberposts' => 500,
                'orderby' => 'title',
                'order' => 'ASC',
                'suppress_filters' => false,
            )
        );
        $options = array();

        foreach ($posts as $post) {
            $type_object = get_post_type_object($post->post_type);
            $type_label = $type_object && isset($type_object->labels->singular_name)
                ? (string) $type_object->labels->singular_name
                : (string) $post->post_type;
            $options[] = array(
                'id' => (int) $post->ID,
                'label' => $type_label . ': ' . get_the_title($post),
            );
        }

        return $options;
    }

    private function whatsapp_category_options(): array
    {
        $terms = get_terms(
            array(
                'taxonomy' => 'category',
                'hide_empty' => false,
                'number' => 500,
                'orderby' => 'name',
                'order' => 'ASC',
            )
        );
        $options = array();

        if (is_wp_error($terms)) {
            return $options;
        }

        foreach ($terms as $term) {
            $options[] = array(
                'id' => (int) $term->term_id,
                'label' => (string) $term->name,
            );
        }

        return $options;
    }
}
