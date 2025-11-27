<?php
class Advent_Calendar_Ajax {
    public function __construct() {
        add_action('wp_ajax_save_door_content', array($this, 'save_door_content'));
        add_action('wp_ajax_nopriv_save_door_content', array($this, 'save_door_content'));
        add_action('wp_ajax_get_door_statistics', array($this, 'get_door_statistics'));
        add_action('wp_ajax_nopriv_get_door_statistics', array($this, 'get_door_statistics'));
        add_action('wp_ajax_save_calendar_styles', array($this, 'save_calendar_styles'));
        add_action('wp_ajax_nopriv_save_calendar_styles', array($this, 'save_calendar_styles'));
        add_action('wp_ajax_open_door', array($this, 'open_door'));
        add_action('wp_ajax_nopriv_open_door', array($this, 'open_door'));
        add_action('wp_ajax_get_door_content', array($this, 'get_door_content'));
        add_action('wp_ajax_nopriv_get_door_content', array($this, 'get_door_content'));
    }

    public function save_door_content() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed', 403);
        }

        // Sprawdzenie uprawnień
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 401);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

        // Walidacja door_id
        if ($door_id < 1 || $door_id > 24) {
            wp_send_json_error('Invalid door ID');
        }

        // Zapisz dane...
        update_option('advent_door_' . $door_id, $content);

        wp_send_json_success('Door content saved');
    }

    public function get_door_statistics() {
        // Weryfikacja nonce i uprawnień
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 401);
        }

        global $wpdb;
        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        
        // Zabezpieczenie przed SQL Injection
        $results = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats WHERE door_id = %d",
            $door_id
        ));

        wp_send_json_success(['count' => intval($results)]);
    }

    public function open_door() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed', 403);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        $calendar_id = isset($_POST['calendar_id']) ? intval($_POST['calendar_id']) : 0;
        $user_session = isset($_POST['user_session']) ? sanitize_text_field(wp_unslash($_POST['user_session'])) : '';

        // Walidacja danych wejściowych
        if ($door_id <= 0 || $calendar_id <= 0) {
            wp_send_json_error('Invalid door or calendar ID');
        }

        // Sprawdź czy drzwi istnieją
        $door = Advent_Calendar::get_door($door_id);
        if (!$door) {
            wp_send_json_error('Door not found');
        }

        // Sprawdź czy użytkownik może otworzyć drzwi
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        if (!$calendar) {
            wp_send_json_error('Calendar not found');
        }

        $settings = json_decode($calendar->settings, true);
        if (!Advent_Calendar::can_unlock_door($door->door_number, $settings)) {
            wp_send_json_error('Door cannot be opened yet');
        }

        // Sprawdź czy użytkownik już otworzył te drzwi
        if (Advent_Calendar::has_user_opened_door_with_session($door_id, $user_session)) {
            wp_send_json_error('Door already opened');
        }

        // Zapisz otwarcie
        $result = Advent_Calendar::log_door_open_with_session($door_id, $calendar_id, $user_session);
        
        if ($result) {
            $door_data = array(
                'content' => $door->content,
                'title' => $door->title,
                'image_url' => $door->image_url,
                'link_url' => $door->link_url,
                'door_type' => $door->door_type,
                'animation' => $door->animation,
                'effects' => array('confetti')
            );
            
            wp_send_json_success($door_data);
        } else {
            wp_send_json_error('Failed to log door opening');
        }
    }

    public function get_door_content() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Security check failed', 403);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        
        if ($door_id <= 0) {
            wp_send_json_error('Invalid door ID');
        }

        $door = Advent_Calendar::get_door($door_id);
        if (!$door) {
            wp_send_json_error('Door not found');
        }

        $content = array(
            'content' => $door->content,
            'title' => $door->title,
            'image_url' => $door->image_url
        );

        wp_send_json_success($content);
    }

    public function save_calendar_styles() {
        // Weryfikacja nonce i uprawnień
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_send_json_error('Security check failed', 403);
        }

        $calendar_id = isset($_POST['calendar_id']) ? intval($_POST['calendar_id']) : 0;
        $styles = isset($_POST['styles']) ? wp_unslash($_POST['styles']) : array();

        // Walidacja i sanitization danych
        $sanitized_styles = array();
        if (is_array($styles)) {
            foreach ($styles as $key => $value) {
                $sanitized_styles[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }

        // Zapisz style...
        $result = Advent_Calendar_Styles::save_style(array(
            'calendar_id' => $calendar_id,
            'styles_data' => $sanitized_styles
        ));

        if ($result) {
            wp_send_json_success('Styles saved successfully');
        } else {
            wp_send_json_error('Failed to save styles');
        }
    }
}
?>
