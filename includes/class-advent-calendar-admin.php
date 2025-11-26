<?php

class Advent_Calendar_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_calendar', array($this, 'save_calendar'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Kalendarz Adwentowy',
            'Kalendarz Adwentowy',
            'manage_options',
            'advent-calendar',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
    }
    
    public function enqueue_scripts($hook) {
        if ('toplevel_page_advent-calendar' !== $hook) {
            return;
        }
        
        wp_enqueue_style('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/admin.css', array(), ADVENT_CALENDAR_VERSION);
        wp_enqueue_script('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), ADVENT_CALENDAR_VERSION, true);
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_media();
    }
    
    public function admin_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-admin.php';
    }
    
    public function save_calendar() {
        // Zabezpieczenie nonce
        if (!wp_verify_nonce($_POST['nonce'], 'advent_calendar_nonce')) {
            wp_die('Błąd bezpieczeństwa');
        }
        
        // Tutaj logika zapisywania kalendarza
        // ...
        
        wp_send_json_success('Kalendarz zapisany!');
    }
}
?>
