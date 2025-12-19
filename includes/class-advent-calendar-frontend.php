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
        
        $calendar_id = intval($atts['id']);
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        
        if (!$calendar) {
            return '<p class="advent-calendar-error">Kalendarz nie znaleziony [ID: ' . $calendar_id . ']</p>';
        }
        
        $settings = json_decode($calendar->settings, true);
        if (!$settings) {
            $settings = array();
        }
        
        // WAŻNE: Pobierz dane które potrzebne są w szablonie
        $doors = Advent_Calendar::get_calendar_doors($calendar_id);
        $columns = isset($settings['columns']) ? intval($settings['columns']) : intval($atts['columns']);
        $rows = isset($settings['rows']) ? intval($settings['rows']) : 4;
        $theme = isset($settings['theme']) ? $settings['theme'] : $atts['theme'];
        $total_doors = $columns * $rows;
        
        // Upewnij się że motyw istnieje
        $theme_paths = array(
            ADVENT_CALENDAR_PLUGIN_PATH . "templates/themes/{$theme}/calendar-frontend.php",
            ADVENT_CALENDAR_PLUGIN_PATH . "templates/thems/{$theme}/calendar-frontend.php"
        );
        
        $theme_template = '';
        foreach ($theme_paths as $path) {
            if (file_exists($path)) {
                $theme_template = $path;
                break;
            }
        }
        
        // Jeśli nie znaleziono motywu, użyj domyślnego
        if (empty($theme_template)) {
            $theme_template = ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-frontend.php';
        }
        
        // BUFFER OUTPUT - PRZEKAŻ WSZYSTKIE POTRZEBNE ZMIENNE
        ob_start();
        
        // Załaduj szablon i przekaż zmienne przez extract() lub bezpośrednio
        // Lepiej użyć include z globalnymi zmiennymi:
        
        // Zdefiniuj zmienne globalne dla szablonu
        $advent_calendar_data = array(
            'calendar_id' => $calendar_id,
            'calendar' => $calendar,
            'doors' => $doors,
            'settings' => $settings,
            'columns' => $columns,
            'rows' => $rows,
            'theme' => $theme,
            'total_doors' => $total_doors,
            'atts' => $atts
        );
        
        // Użyj funkcji pomocniczej do renderowania
        echo $this->render_template($theme_template, $advent_calendar_data);
        
        return ob_get_clean();
    }
    
    /**
     * Renderuje szablon z przekazanymi danymi
     */
    private function render_template($template_path, $data = array()) {
        if (!file_exists($template_path)) {
            return '<p class="advent-calendar-error">Błąd: Szablon nie istnieje: ' . basename($template_path) . '</p>';
        }
        
        // Ekstrakcja zmiennych dla szablonu
        extract($data, EXTR_SKIP);
        
        // Rozpocznij buforowanie
        ob_start();
        
        // Dołącz szablon - wszystkie zmienne z $data będą dostępne
        include $template_path;
        
        // Zwróć zawartość bufora
        return ob_get_clean();
    }
}
?>
