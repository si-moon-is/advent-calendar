<?php

class Advent_Calendar {
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'advent_calendars';
        $doors_table = $wpdb->prefix . 'advent_calendar_doors';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            settings text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        $sql2 = "CREATE TABLE $doors_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calendar_id mediumint(9) NOT NULL,
            door_number smallint NOT NULL,
            content text,
            image_url varchar(255),
            link_url varchar(255),
            door_type varchar(50) DEFAULT 'modal',
            animation varchar(50),
            styles text,
            unlock_date date NOT NULL,
            is_open tinyint(1) DEFAULT 0,
            PRIMARY KEY (id),
            KEY calendar_id (calendar_id),
            KEY unlock_date (unlock_date)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        dbDelta($sql2);
    }
    
    public static function get_calendar($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendars WHERE id = %d", $id
        ));
    }
    
    public static function get_calendar_doors($calendar_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_doors WHERE calendar_id = %d ORDER BY door_number", $calendar_id
        ));
    }
    
    public static function can_unlock_door($door_number, $calendar_settings) {
        $current_date = current_time('Y-m-d');
        $start_date = $calendar_settings['start_date'] ?? date('Y-12-01');
        
        $door_date = date('Y-m-d', strtotime($start_date . ' + ' . ($door_number - 1) . ' days'));
        
        return $current_date >= $door_date;
    }
}
?>
