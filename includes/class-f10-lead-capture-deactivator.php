<?php

if (!defined('ABSPATH')) {
    exit;
}

final class F10_Lead_Capture_Deactivator
{
    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('f10_lead_capture_retry_event');
    }
}
