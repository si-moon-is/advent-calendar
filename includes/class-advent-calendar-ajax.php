<?php
class Advent_Calendar_Ajax {
    public function __construct() {
        // Akcje tylko dla zalogowanych użytkowników (admin)
        add_action('wp_ajax_save_door_content', array($this, 'save_door_content'));
        add_action('wp_ajax_get_door_statistics', array($this, 'get_door_statistics'));
        add_action('wp_ajax_save_calendar_styles', array($this, 'save_calendar_styles'));
        add_action('wp_ajax_advent_calendar_save', array($this, 'save_calendar'));
        add_action('wp_ajax_advent_calendar_delete', array($this, 'delete_calendar'));
        add_action('wp_ajax_advent_calendar_save_door', array($this, 'save_door'));
        add_action('wp_ajax_advent_calendar_get_door', array($this, 'get_door'));
        add_action('wp_ajax_get_calendar_stats', array($this, 'get_calendar_stats'));
        
        // Akcje dla wszystkich użytkowników
        add_action('wp_ajax_open_door', array($this, 'open_door'));
        add_action('wp_ajax_nopriv_open_door', array($this, 'open_door'));
        add_action('wp_ajax_get_door_content', array($this, 'get_door_content'));
        add_action('wp_ajax_nopriv_get_door_content', array($this, 'get_door_content'));
    }

    /**
     * Zapisz zawartość drzwi
     */
    public function save_door_content() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        // Sprawdzenie uprawnień
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post(wp_unslash($_POST['content'])) : '';

        // Walidacja door_id
        if ($door_id < 1) {
            wp_send_json_error('Nieprawidłowy ID drzwi');
        }

        // Pobierz drzwi i zaktualizuj zawartość
        $door = Advent_Calendar::get_door($door_id);
        if (!$door) {
            wp_send_json_error('Drzwi nie znalezione');
        }

        // Zaktualizuj zawartość drzwi
        $result = Advent_Calendar::save_door(array(
            'id' => $door_id,
            'calendar_id' => $door->calendar_id,
            'door_number' => $door->door_number,
            'content' => $content
        ));

        if ($result) {
            wp_send_json_success('Zawartość drzwi zapisana pomyślnie');
        } else {
            wp_send_json_error('Błąd podczas zapisywania zawartości drzwi');
        }
    }

    /**
     * Pobierz statystyki drzwi
     */
    public function get_door_statistics() {
        // Weryfikacja nonce i uprawnień
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        global $wpdb;
        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        
        if ($door_id <= 0) {
            wp_send_json_error('Nieprawidłowy ID drzwi');
        }

        // Zabezpieczenie przed SQL Injection
        $results = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats WHERE door_id = %d",
            $door_id
        ));

        wp_send_json_success(['count' => intval($results)]);
    }

    /**
     * Otwórz drzwi (dla użytkowników)
     */
    public function open_door() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        $calendar_id = isset($_POST['calendar_id']) ? intval($_POST['calendar_id']) : 0;
        $user_session = isset($_POST['user_session']) ? sanitize_text_field(wp_unslash($_POST['user_session'])) : '';

        // Walidacja danych wejściowych
        if ($door_id <= 0 || $calendar_id <= 0) {
            wp_send_json_error('Nieprawidłowy ID drzwi lub kalendarza');
        }

        if (empty($user_session)) {
            wp_send_json_error('Brak sesji użytkownika');
        }

        // Sprawdź czy drzwi istnieją
        $door = Advent_Calendar::get_door($door_id);
        if (!$door) {
            wp_send_json_error('Drzwi nie znalezione');
        }

        // Sprawdź czy kalendarz istnieje
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        if (!$calendar) {
            wp_send_json_error('Kalendarz nie znaleziony');
        }

        $settings = json_decode($calendar->settings, true);
        
        // Sprawdź czy użytkownik może otworzyć drzwi
        if (!Advent_Calendar::can_unlock_door($door->door_number, $settings)) {
            wp_send_json_error('Te drzwi nie mogą być jeszcze otwarte');
        }

        // Sprawdź czy użytkownik już otworzył te drzwi
        if (Advent_Calendar::has_user_opened_door_with_session($door_id, $user_session)) {
            // Zwróć zawartość, ale nie zapisuj ponownie
            $door_data = array(
                'content' => $door->content,
                'title' => $door->title,
                'image_url' => $door->image_url,
                'link_url' => $door->link_url,
                'door_type' => $door->door_type,
                'animation' => $door->animation,
                'effects' => array('confetti'),
                'already_opened' => true
            );
            
            wp_send_json_success($door_data);
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
                'effects' => array('confetti'),
                'already_opened' => false
            );
            
            wp_send_json_success($door_data);
        } else {
            wp_send_json_error('Błąd podczas otwierania drzwi');
        }
    }

    /**
     * Pobierz zawartość drzwi
     */
    public function get_door_content() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        $door_id = isset($_POST['door_id']) ? intval($_POST['door_id']) : 0;
        
        if ($door_id <= 0) {
            wp_send_json_error('Nieprawidłowy ID drzwi');
        }

        $door = Advent_Calendar::get_door($door_id);
        if (!$door) {
            wp_send_json_error('Drzwi nie znalezione');
        }

        $content = array(
            'content' => $door->content,
            'title' => $door->title,
            'image_url' => $door->image_url,
            'link_url' => $door->link_url,
            'door_type' => $door->door_type
        );

        wp_send_json_success($content);
    }

    /**
     * Zapisz style kalendarza
     */
    public function save_calendar_styles() {
        // Weryfikacja nonce i uprawnień
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false) || !current_user_can('manage_options')) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        $calendar_id = isset($_POST['calendar_id']) ? intval($_POST['calendar_id']) : 0;
        $styles = isset($_POST['styles']) ? wp_unslash($_POST['styles']) : array();

        if ($calendar_id <= 0) {
            wp_send_json_error('Nieprawidłowy ID kalendarza');
        }

        // Walidacja i sanitization danych
        $sanitized_styles = array();
        if (is_array($styles)) {
            foreach ($styles as $key => $value) {
                $sanitized_styles[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }

        // Zapisz style
        $result = Advent_Calendar_Styles::save_style(array(
            'calendar_id' => $calendar_id,
            'styles_data' => $sanitized_styles
        ));

        if ($result) {
            wp_send_json_success('Style zapisane pomyślnie');
        } else {
            wp_send_json_error('Błąd podczas zapisywania stylów');
        }
    }

    /**
     * Zapisz kalendarz (dla admina)
     */
    public function save_calendar() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        // Sprawdzenie uprawnień
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        // Sprawdź wymagane pola
        if (empty($_POST['title'])) {
            wp_send_json_error('Nazwa kalendarza jest wymagana');
        }

        // Przygotuj dane
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'settings' => array(
                'columns' => isset($_POST['columns']) ? intval($_POST['columns']) : 6,
                'rows' => isset($_POST['rows']) ? intval($_POST['rows']) : 4,
                'start_date' => isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : date('Y-12-01'),
                'end_date' => isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : date('Y-12-24'),
                'theme' => isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : 'christmas',
                'default_animation' => isset($_POST['default_animation']) ? sanitize_text_field($_POST['default_animation']) : 'fade',
                'snow_effect' => isset($_POST['snow_effect']) && $_POST['snow_effect'] == '1',
                'confetti_effect' => isset($_POST['confetti_effect']) && $_POST['confetti_effect'] == '1',
                'enable_stats' => isset($_POST['enable_stats']) && $_POST['enable_stats'] == '1'
            )
        );

        // Sprawdź czy to aktualizacja czy nowy kalendarz
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $data['id'] = intval($_POST['id']);
        }

        // Zapisz kalendarz
        $calendar_id = Advent_Calendar::save_calendar($data);

        if ($calendar_id) {
            $response = array(
                'id' => $calendar_id,
                'message' => 'Kalendarz zapisany pomyślnie!'
            );

            // Jeśli to nowy kalendarz, dodaj przekierowanie
            if (!isset($_POST['id'])) {
                $response['redirect'] = admin_url('admin.php?page=advent-calendar-new&calendar_id=' . $calendar_id);
            }

            wp_send_json_success($response);
        } else {
            wp_send_json_error('Błąd podczas zapisywania kalendarza');
        }
    }

    /**
     * Usuń kalendarz (dla admina)
     */
    public function delete_calendar() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        $calendar_id = intval($_POST['calendar_id']);
        $result = Advent_Calendar::delete_calendar($calendar_id);

        if ($result) {
            wp_send_json_success('Kalendarz usunięty pomyślnie!');
        } else {
            wp_send_json_error('Błąd podczas usuwania kalendarza');
        }
    }

    /**
     * Zapisz drzwi (dla admina)
     */
    public function save_door() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        if (empty($_POST['calendar_id']) || empty($_POST['door_number'])) {
            wp_send_json_error('ID kalendarza i numer drzwi są wymagane');
        }

        $data = array(
            'calendar_id' => intval($_POST['calendar_id']),
            'door_number' => intval($_POST['door_number']),
            'title' => sanitize_text_field($_POST['title'] ?? ''),
            'content' => wp_kses_post($_POST['content'] ?? ''),
            'image_url' => esc_url_raw($_POST['image_url'] ?? ''),
            'link_url' => esc_url_raw($_POST['link_url'] ?? ''),
            'door_type' => sanitize_text_field($_POST['door_type'] ?? 'modal'),
            'animation' => sanitize_text_field($_POST['animation'] ?? 'fade'),
            'styles' => array(),
            'custom_css' => sanitize_textarea_field($_POST['custom_css'] ?? ''),
            'unlock_date' => sanitize_text_field($_POST['unlock_date'] ?? '')
        );

        if (isset($_POST['door_id']) && !empty($_POST['door_id'])) {
            $data['id'] = intval($_POST['door_id']);
        }

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

    /**
     * Pobierz dane drzwi (dla admina)
     */
    public function get_door() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        $door_id = intval($_POST['door_id']);
        $door = Advent_Calendar::get_door($door_id);

        if ($door) {
            wp_send_json_success($door);
        } else {
            wp_send_json_error('Drzwi nie znalezione');
        }
    }

    /**
     * Pobierz statystyki kalendarza
     */
    public function get_calendar_stats() {
        // Weryfikacja nonce
        if (!check_ajax_referer('advent_calendar_nonce', 'nonce', false)) {
            wp_send_json_error('Błąd bezpieczeństwa (nonce)', 403);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Brak uprawnień', 401);
        }

        $calendar_id = intval($_POST['calendar_id']);
        $stats = $this->get_calendar_statistics($calendar_id);

        wp_send_json_success($stats);
    }

    /**
     * Pomocnicza metoda do pobierania statystyk
     */
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
