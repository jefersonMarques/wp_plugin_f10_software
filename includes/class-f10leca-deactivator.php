<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10LECA_Deactivator
{
    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('f10leca_retry_event');
    }
}
