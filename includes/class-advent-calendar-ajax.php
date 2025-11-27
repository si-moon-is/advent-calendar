<?php
class Advent_Calendar_Ajax {
    public function __construct() {
        add_action('wp_ajax_save_door_content', array($this, 'save_door_content'));
        add_action('wp_ajax_get_door_statistics', array($this, 'get_door_statistics'));
        add_action('wp_ajax_save_calendar_styles', array($this, 'save_calendar_styles'));
    }

    public function save_door_content() {
        // DODANE: Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_die('Security check failed', 403);
        }

        // DODANE: Sprawdzenie uprawnień
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized', 401);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if ($door_id < 1 || $door_id > 24) {
            wp_send_json_error('Invalid door ID');
        }

        // Zapisz dane...
        update_option('advent_door_' . $door_id, $content);

        wp_send_json_success('Door content saved');
    }

    public function get_door_statistics() {
        // DODANE: Weryfikacja nonce i uprawnień
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_die('Security check failed', 403);
        }

        global $wpdb;
        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        
        // POPRAWIONE: Zabezpieczenie przed SQL Injection
        $results = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats WHERE door_id = %d",
            $door_id
        ));

        wp_send_json_success(['count' => $results]);
    }
}
?>
