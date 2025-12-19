<?php
class Advent_Calendar_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_advent_calendar_save', array($this, 'ajax_save_calendar'));
        add_action('wp_ajax_advent_calendar_delete', array($this, 'ajax_delete_calendar'));
        add_action('wp_ajax_advent_calendar_save_door', array($this, 'ajax_save_door'));
        add_action('wp_ajax_advent_calendar_get_door', array($this, 'ajax_get_door'));
        add_action('wp_ajax_get_calendar_stats', array($this, 'get_calendar_stats_ajax'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Kalendarz Adwentowy',
            'Kalendarz Adwentowy',
            'manage_options',
            'advent-calendar',
            array($this, 'main_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'advent-calendar',
            'Wszystkie Kalendarze',
            'Wszystkie Kalendarze',
            'manage_options',
            'advent-calendar',
            array($this, 'main_page')
        );
        
        add_submenu_page(
            'advent-calendar',
            'Dodaj Nowy',
            'Dodaj Nowy',
            'manage_options',
            'advent-calendar-new',
            array($this, 'editor_page')
        );
        
        add_submenu_page(
            'advent-calendar',
            'Statystyki',
            'Statystyki',
            'manage_options',
            'advent-calendar-stats',
            array($this, 'statistics_page')
        );
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'advent-calendar') === false) {
            return;
        }
        
        wp_enqueue_style('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/admin.css', array(), ADVENT_CALENDAR_VERSION);
        wp_enqueue_style('wp-color-picker');
        
        wp_enqueue_script('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), ADVENT_CALENDAR_VERSION, true);
        wp_enqueue_media();
        
        if ($hook === 'advent-calendar_page_advent-calendar-stats') {
            wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true);
        }
        
        wp_localize_script('advent-calendar-admin', 'adventCalendar', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('advent_calendar_nonce')
        ));
    }
    
    public function main_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-admin.php';
    }
    
    public function editor_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/door-editor.php';
    }
    
    public function statistics_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/statistics.php';
    }
   
   private function get_calendar_statistics($calendar_id) {
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
}
?>
