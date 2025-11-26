<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$tables = array(
    'advent_calendars',
    'advent_calendar_doors',
    'advent_calendar_stats',
    'advent_calendar_styles'
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
}

delete_option('advent_calendar_default_settings');
delete_option('advent_calendar_version');
delete_option('advent_calendar_db_version');

$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%advent_calendar%'");
