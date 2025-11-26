<?php
// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Usuwanie tabel
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advent_calendars");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}advent_calendar_doors");

// Usuwanie opcji
delete_option('advent_calendar_default_settings');
delete_option('advent_calendar_version');
