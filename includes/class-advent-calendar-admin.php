<?php
class Advent_Calendar_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_advent_calendar_save', array($this, 'ajax_save_calendar'));
        add_action('wp_ajax_advent_calendar_delete', array($this, 'ajax_delete_calendar'));
        add_action('wp_ajax_advent_calendar_save_door', array($this, 'ajax_save_door'));
        add_action('wp_ajax_advent_calendar_get_door', array($this, 'ajax_get_door'));
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
    }
    
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'advent-calendar') === false) {
            return;
        }
        
        wp_enqueue_style('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/admin.css', array(), ADVENT_CALENDAR_VERSION);
        wp_enqueue_style('wp-color-picker');
        
        wp_enqueue_script('advent-calendar-admin', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'wp-color-picker'), ADVENT_CALENDAR_VERSION, true);
        wp_enqueue_media();
        
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
    
    public function ajax_save_calendar() {
        check_ajax_referer('advent_calendar_nonce', 'nonce');
        
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
                'message' => 'Kalendarz zapisany pomyślnie!',
                'redirect' => !isset($_POST['id']) ? admin_url('admin.php?page=advent-calendar-new&calendar_id=' . $calendar_id) : false
            ));
        } else {
            wp_send_json_error('Błąd podczas zapisywania kalendarza');
        }
    }
    
    public function ajax_delete_calendar() {
        check_ajax_referer('advent_calendar_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $calendar_id = intval($_POST['calendar_id']);
        $result = Advent_Calendar::delete_calendar($calendar_id);
        
        if ($result) {
            wp_send_json_success('Kalendarz usunięty pomyślnie!');
        } else {
            wp_send_json_error('Błąd podczas usuwania kalendarza');
        }
    }
    
    public function ajax_save_door() {
        check_ajax_referer('advent_calendar_nonce', 'nonce');
        
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
                'message' => 'Drzwi zapisane pomyślnie!'
            ));
        } else {
            wp_send_json_error('Błąd podczas zapisywania drzwi');
        }
    }
    
    public function ajax_get_door() {
        check_ajax_referer('advent_calendar_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień');
        }
        
        $door_id = intval($_POST['door_id']);
        $door = Advent_Calendar::get_door($door_id);
        
        if ($door) {
            wp_send_json_success($door);
        } else {
            wp_send_json_error('Drzwi nie znalezione');
        }
    }
}
?>
