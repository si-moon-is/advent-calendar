<?php

class Advent_Calendar_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_open_door', array($this, 'open_door'));
        add_action('wp_ajax_nopriv_open_door', array($this, 'open_door'));
        add_action('wp_ajax_get_door_content', array($this, 'get_door_content'));
        add_action('wp_ajax_nopriv_get_door_content', array($this, 'get_door_content'));
    }
    
    public function open_door() {
        if (!wp_verify_nonce($_POST['nonce'], 'advent_calendar_nonce')) {
            wp_send_json_error('Błąd bezpieczeństwa');
        }
        
        $door_id = intval($_POST['door_id']);
        $calendar_id = intval($_POST['calendar_id']);
        
        $door = Advent_Calendar::get_door($door_id);
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        
        if (!$door || !$calendar) {
            wp_send_json_error('Nie znaleziono drzwi lub kalendarza');
        }
        
        $settings = json_decode($calendar->settings, true);
        
        // Sprawdź czy drzwi można otworzyć
        if (!Advent_Calendar::can_unlock_door($door->door_number, $settings)) {
            wp_send_json_error('Te drzwi nie są jeszcze dostępne');
        }
        
        // Zapisz otwarcie
        Advent_Calendar::log_door_open($door_id, $calendar_id);
        
        // Efekty specjalne
        $effects = array();
        if ($settings['snow_effect'] ?? false) {
            $effects[] = 'snow';
        }
        if ($settings['confetti_effect'] ?? false) {
            $effects[] = 'confetti';
        }
        
        wp_send_json_success(array(
            'content' => $this->get_door_display_content($door),
            'animation' => $door->animation,
            'effects' => $effects,
            'door_type' => $door->door_type,
            'link_url' => $door->link_url
        ));
    }
    
    public function get_door_content() {
        if (!wp_verify_nonce($_POST['nonce'], 'advent_calendar_nonce')) {
            wp_send_json_error('Błąd bezpieczeństwa');
        }
        
        $door_id = intval($_POST['door_id']);
        $door = Advent_Calendar::get_door($door_id);
        
        if ($door) {
            wp_send_json_success(array(
                'content' => $this->get_door_display_content($door)
            ));
        } else {
            wp_send_json_error('Drzwi nie znalezione');
        }
    }
    
    private function get_door_display_content($door) {
        $content = '';
        
        if ($door->image_url) {
            $content .= '<div class="door-image"><img src="' . esc_url($door->image_url) . '" alt="' . esc_attr($door->title) . '"></div>';
        }
        
        if ($door->title) {
            $content .= '<h3 class="door-title">' . esc_html($door->title) . '</h3>';
        }
        
        if ($door->content) {
            $content .= '<div class="door-content">' . wp_kses_post($door->content) . '</div>';
        }
        
        return $content;
    }
}
?>
