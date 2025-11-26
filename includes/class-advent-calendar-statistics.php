<?php
class Advent_Calendar_Statistics {
    
    public function __construct() {
        add_action('wp_ajax_get_calendar_stats', array($this, 'get_calendar_stats_ajax'));
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
        
        $total_opens = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats WHERE calendar_id = %d",
            $calendar_id
        ));
        
        $unique_visitors = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_ip) FROM {$wpdb->prefix}advent_calendar_stats WHERE calendar_id = %d",
            $calendar_id
        ));
        
        $popular_doors = $wpdb->get_results($wpdb->prepare(
            "SELECT d.door_number, d.title, COUNT(s.id) as open_count 
             FROM {$wpdb->prefix}advent_calendar_stats s 
             JOIN {$wpdb->prefix}advent_calendar_doors d ON s.door_id = d.id 
             WHERE s.calendar_id = %d 
             GROUP BY s.door_id 
             ORDER BY open_count DESC 
             LIMIT 5",
            $calendar_id
        ));
        
        $daily_opens = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE(opened_at) as date, COUNT(*) as count 
             FROM {$wpdb->prefix}advent_calendar_stats 
             WHERE calendar_id = %d 
             GROUP BY DATE(opened_at) 
             ORDER BY date",
            $calendar_id
        ));
        
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
