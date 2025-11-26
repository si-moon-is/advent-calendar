<?php

class Advent_Calendar_Frontend {
    
    public function __construct() {
        add_shortcode('advent_calendar', array($this, 'calendar_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_custom_css'));
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
    
    public function add_custom_css() {
        global $post;
        if ($post && has_shortcode($post->post_content, 'advent_calendar')) {
            $calendars = Advent_Calendar::get_calendars();
            foreach ($calendars as $calendar) {
                $styles = Advent_Calendar_Styles::get_calendar_styles($calendar->id);
                foreach ($styles as $style) {
                    echo '<style>' . esc_html($style->custom_css) . '</style>';
                }
            }
        }
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
        $doors = Advent_Calendar::get_calendar_doors($atts['id']);
        
        ob_start();
        include ADVENT_CALENDAR_PLUGIN_PATH . 'templates/calendar-frontend.php';
        return ob_get_clean();
    }
}
?>
