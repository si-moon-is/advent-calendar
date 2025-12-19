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
    $atts = shortcode_atts(array(
        'id' => 1,
        'columns' => 6,
        'theme' => 'christmas',
        'show_stats' => 'false'
    ), $atts);
    
    $calendar = Advent_Calendar::get_calendar($atts['id']);
    if (!$calendar) {
        return '<p>Kalendarz nie znaleziony</p>';
    }
    
    $settings = json_decode($calendar->settings, true);
    
    // Użyj motywu z ustawień kalendarza, jeśli nie podano w shortcode
    $theme = !empty($settings['theme']) ? $settings['theme'] : $atts['theme'];
    
    // Sprawdź czy plik motywu istnieje (poprawiona ścieżka)
    $theme_template = ADVENT_CALENDAR_PLUGIN_PATH . "templates/themes/{$theme}/calendar-frontend.php";
    
    // Jeśli plik motywu nie istnieje, użyj poprawnej alternatywnej ścieżki
    if (!file_exists($theme_template)) {
        // Sprawdź starą ścieżkę z literówką
        $theme_template = ADVENT_CALENDAR_PLUGIN_PATH . "templates/thems/{$theme}/calendar-frontend.php";
    }
    
    // Jeśli nadal nie istnieje, użyj domyślnego
    if (!file_exists($theme_template)) {
        $theme_template = ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-frontend.php';
    }
    
    // Przekaż wszystkie potrzebne dane do szablonu
    ob_start();
    
    // Zdefiniuj zmienne dla szablonu
    $calendar_id = $atts['id'];
    $doors = Advent_Calendar::get_calendar_doors($calendar_id);
    $columns = isset($settings['columns']) ? intval($settings['columns']) : $atts['columns'];
    $rows = isset($settings['rows']) ? intval($settings['rows']) : 4;
    $total_doors = $columns * $rows;
    
    // Dołącz szablon
    include $theme_template;
    
    return ob_get_clean();
}
}
?>
