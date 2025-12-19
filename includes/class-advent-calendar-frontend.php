<?php
class Advent_Calendar_Frontend {
    
    public function __construct() {
        add_shortcode('advent_calendar', array($this, 'calendar_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('advent-calendar-frontend', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/frontend.css', array(), ADVENT_CALENDAR_VERSION);
        wp_enqueue_style('advent-calendar-animations', ADVENT_CALENDAR_PLUGIN_URL . 'assets/css/animations.css', array(), ADVENT_CALENDAR_VERSION);
        
        wp_enqueue_script('advent-calendar-frontend', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), ADVENT_CALENDAR_VERSION, true);
        wp_enqueue_script('advent-calendar-effects', ADVENT_CALENDAR_PLUGIN_URL . 'assets/js/effects.js', array('jquery'), ADVENT_CALENDAR_VERSION, true);
        
        wp_localize_script('advent-calendar-frontend', 'adventCalendar', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('advent_calendar_nonce')
        ));
    }
    
    public function calendar_shortcode($atts) {
        // Ustaw domyślne atrybuty
        $atts = shortcode_atts(array(
            'id' => 1,
            'columns' => 6,
            'theme' => 'christmas',
            'show_stats' => 'false'
        ), $atts);
        
        $calendar_id = intval($atts['id']);
        
        // Pobierz kalendarz
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        
        if (!$calendar) {
            return '<p class="advent-error">Błąd: Kalendarz #' . $calendar_id . ' nie istnieje.</p>';
        }
        
        // Pobierz drzwi dla tego kalendarza
        $doors = Advent_Calendar::get_calendar_doors($calendar_id);
        
        // Pobierz ustawienia
        $settings = json_decode($calendar->settings, true);
        if (empty($settings) || !is_array($settings)) {
            $settings = array();
        }
        
        // Ustal kolumny i wiersze
        $columns = isset($settings['columns']) ? intval($settings['columns']) : intval($atts['columns']);
        $rows = isset($settings['rows']) ? intval($settings['rows']) : 4;
        $total_doors = $columns * $rows;
        
        // Ustal motyw
        $theme = isset($settings['theme']) ? $settings['theme'] : $atts['theme'];
        
        // BUFFER OUTPUT
        ob_start();
        
        // Załaduj szablon - UWAGA: używamy domyślnego szablonu, nie motywów!
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-frontend.php';
        
        return ob_get_clean();
    }
}
?>
