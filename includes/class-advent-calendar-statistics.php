<?php
class Advent_Calendar_Statistics {
    
    public function __construct() {
        add_action('wp_ajax_get_calendar_stats', array($this, 'get_calendar_stats_ajax'));
        add_action('wp_ajax_export_stats', array($this, 'export_stats'));
    }
    
    public function get_calendar_stats_ajax() {
        check_ajax_referer('advent_calendar_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $calendar_id = intval($_POST['calendar_id']);
        $stats = $this->get_calendar_statistics($calendar_id);
        
        wp_send_json_success($stats);
    }
    
    public function get_calendar_statistics($calendar_id) {
    global $wpdb;
    
    error_log("Getting statistics for calendar: " . $calendar_id);
    
    $total_opens = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats WHERE calendar_id = %d",
        $calendar_id
    ));
    
    error_log("Total opens: " . $total_opens);
    
    $unique_visitors = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT user_ip) FROM {$wpdb->prefix}advent_calendar_stats WHERE calendar_id = %d",
        $calendar_id
    ));
    
    error_log("Unique visitors: " . $unique_visitors);
    
    // POPRAWIONE ZAPYTANIE - upewnij się że door_id pasuje
    $popular_doors = $wpdb->get_results($wpdb->prepare(
        "SELECT d.door_number, d.title, COUNT(s.id) as open_count 
         FROM {$wpdb->prefix}advent_calendar_stats s 
         JOIN {$wpdb->prefix}advent_calendar_doors d ON s.door_id = d.id 
         WHERE s.calendar_id = %d 
         GROUP BY d.door_number, d.title 
         ORDER BY open_count DESC 
         LIMIT 5",
        $calendar_id
    ));
    
    error_log("Popular doors: " . print_r($popular_doors, true));
    
    $daily_opens = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(opened_at) as date, COUNT(*) as count 
         FROM {$wpdb->prefix}advent_calendar_stats 
         WHERE calendar_id = %d 
         GROUP BY DATE(opened_at) 
         ORDER BY date",
        $calendar_id
    ));
    
    error_log("Daily opens: " . print_r($daily_opens, true));
    
    return array(
        'total_opens' => $total_opens ?: 0,
        'unique_visitors' => $unique_visitors ?: 0,
        'popular_doors' => $popular_doors ?: array(),
        'daily_opens' => $daily_opens ?: array()
    );
}
    
    public function get_door_statistics($door_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_stats 
             WHERE door_id = %d 
             ORDER BY opened_at DESC",
            $door_id
        ));
    }
}
?>
