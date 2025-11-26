<?php

class Advent_Calendar_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_calendar', array($this, 'save_calendar_ajax'));
        add_action('wp_ajax_delete_calendar', array($this, 'delete_calendar_ajax'));
        add_action('wp_ajax_save_door', array($this, 'save_door_ajax'));
        add_action('wp_ajax_get_door', array($this, 'get_door_ajax'));
        add_action('wp_ajax_get_calendar_stats', array($this, 'get_calendar_stats_ajax'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Kalendarz Adwentowy',
            'Kalendarz Adwentowy',
            'manage_options',
            'advent-calendar',
            array($this, 'main_admin_page'),
            'dashicons-calendar-alt',
            30
        );
        
        add_submenu_page(
            'advent-calendar',
            'Wszystkie Kalendarze',
            'Wszystkie Kalendarze',
            'manage_options',
            'advent-calendar',
            array($this, 'main_admin_page')
        );
        
        add_submenu_page(
            'advent-calendar',
            'Dodaj Nowy',
            'Dodaj Nowy',
            'manage_options',
            'advent-calendar-new',
            array($this, 'calendar_editor_page')
        );
        
        add_submenu_page(
            'advent-calendar',
            'Statystyki',
            'Statystyki',
            'manage_options',
            'advent-calendar-stats',
            array($this, 'statistics_page')
        );
        
        add_submenu_page(
            'advent-calendar',
            'Eksport/Import',
            'Eksport/Import',
            'manage_options',
            'advent-calendar-export',
            array($this, 'export_import_page')
        );
    }
    
    public function enqueue_scripts($hook) {
        $pages = array(
            'toplevel_page_advent-calendar',
            'advent-calendar_page_advent-calendar-new',
            'advent-calendar_page_advent-calendar-stats',
            'advent-calendar_page_advent-calendar-export'
        );
        
        if (in_array($hook, $pages)) {
            wp_enqueue_style('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/admin.css', array(), ADVENT_CALENDAR_VERSION);
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_style('advent-calendar-color-picker', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/color-picker.css', array(), ADVENT_CALENDAR_VERSION);
            
            wp_enqueue_script('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker', 'jquery-ui-sortable'), ADVENT_CALENDAR_VERSION, true);
            
            if ($hook === 'advent-calendar_page_advent-calendar-stats') {
                wp_enqueue_script('chart-js', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/chart.min.js', array(), '3.9.1', true);
            }
            
            wp_enqueue_media();
            
            wp_localize_script('advent-calendar-admin', 'adventCalendarAdmin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('advent_calendar_admin_nonce'),
                'translations' => array(
                    'deleteConfirm' => __('Czy na pewno chcesz usunąć ten kalendarz?', 'advent-calendar'),
                    'saving' => __('Zapisywanie...', 'advent-calendar'),
                    'saved' => __('Zapisano!', 'advent-calendar')
                )
            ));
        }
    }
    
    public function main_admin_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-admin.php';
    }
    
    public function calendar_editor_page() {
        $calendar_id = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : 0;
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/door-editor.php';
    }
    
    public function statistics_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/statistics.php';
    }
    
    public function export_import_page() {
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/export-import.php';
    }
    
    public function save_calendar_ajax() {
        check_ajax_referer('advent_calendar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $data = array(
            'id' => isset($_POST['id']) ? intval($_POST['id']) : null,
            'title' => sanitize_text_field($_POST['title']),
            'settings' => array(
                'columns' => intval($_POST['columns']),
                'rows' => intval($_POST['rows']),
                'start_date' => sanitize_text_field($_POST['start_date']),
                'end_date' => sanitize_text_field($_POST['end_date']),
                'theme' => sanitize_text_field($_POST['theme']),
                'default_animation' => sanitize_text_field($_POST['default_animation']),
                'snow_effect' => isset($_POST['snow_effect']) && $_POST['snow_effect'] == '1',
                'confetti_effect' => isset($_POST['confetti_effect']) && $_POST['confetti_effect'] == '1',
                'enable_stats' => isset($_POST['enable_stats']) && $_POST['enable_stats'] == '1'
            )
        );
        
        $calendar_id = Advent_Calendar::save_calendar($data);
        
        if ($calendar_id) {
            wp_send_json_success(array(
                'id' => $calendar_id,
                'message' => __('Kalendarz zapisany pomyślnie!', 'advent-calendar')
            ));
        } else {
            wp_send_json_error(__('Błąd podczas zapisywania kalendarza', 'advent-calendar'));
        }
    }
    
    public function delete_calendar_ajax() {
        check_ajax_referer('advent_calendar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $calendar_id = intval($_POST['calendar_id']);
        $result = Advent_Calendar::delete_calendar($calendar_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Kalendarz usunięty pomyślnie!', 'advent-calendar')
            ));
        } else {
            wp_send_json_error(__('Błąd podczas usuwania kalendarza', 'advent-calendar'));
        }
    }
    
    public function save_door_ajax() {
        check_ajax_referer('advent_calendar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $data = array(
            'id' => isset($_POST['door_id']) ? intval($_POST['door_id']) : null,
            'calendar_id' => intval($_POST['calendar_id']),
            'door_number' => intval($_POST['door_number']),
            'title' => sanitize_text_field($_POST['title']),
            'content' => wp_kses_post($_POST['content']),
            'image_url' => esc_url_raw($_POST['image_url']),
            'link_url' => esc_url_raw($_POST['link_url']),
            'door_type' => sanitize_text_field($_POST['door_type']),
            'animation' => sanitize_text_field($_POST['animation']),
            'styles' => array(),
            'custom_css' => sanitize_textarea_field($_POST['custom_css']),
            'unlock_date' => sanitize_text_field($_POST['unlock_date'])
        );
        
        $door_id = Advent_Calendar::save_door($data);
        
        if ($door_id) {
            wp_send_json_success(array(
                'id' => $door_id,
                'message' => __('Drzwi zapisane pomyślnie!', 'advent-calendar')
            ));
        } else {
            wp_send_json_error(__('Błąd podczas zapisywania drzwi', 'advent-calendar'));
        }
    }
    
    public function get_door_ajax() {
        check_ajax_referer('advent_calendar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $door_id = intval($_POST['door_id']);
        $door = Advent_Calendar::get_door($door_id);
        
        if ($door) {
            $door->styles = $door->styles ? json_decode($door->styles, true) : array();
            wp_send_json_success($door);
        } else {
            wp_send_json_error(__('Drzwi nie znalezione', 'advent-calendar'));
        }
    }
    
    public function get_calendar_stats_ajax() {
        check_ajax_referer('advent_calendar_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $calendar_id = intval($_POST['calendar_id']);
        $stats = $this->get_calendar_statistics($calendar_id);
        
        wp_send_json_success($stats);
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
        
        return array(
            'total_opens' => $total_opens ?: 0,
            'unique_visitors' => $unique_visitors ?: 0,
            'popular_doors' => $popular_doors ?: array()
        );
    }
}
?>
