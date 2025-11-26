<?php

class Advent_Calendar_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_open_door', array($this, 'open_door'));
        add_action('wp_ajax_nopriv_open_door', array($this, 'open_door'));
    }
    
    public function open_door() {
        if (!wp_verify_nonce($_POST['nonce'], 'advent_calendar_nonce')) {
            wp_send_json_error('Błąd bezpieczeństwa');
        }
        
        $door_id = intval($_POST['door_id']);
        $calendar_id = intval($_POST['calendar_id']);
        
        // Sprawdź czy drzwi można otworzyć
        // Zapisz stan otwarcia
        // Zwróć zawartość
        
        wp_send_json_success(array(
            'content' => 'Zawartość drzwi...',
            'animation' => 'confetti'
        ));
    }
}
?>
