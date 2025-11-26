<?php
/**
 * Plugin Name: Super Kalendarz Adwentowy
 * Plugin URI: https://twoja-strona.pl
 * Description: Dodaj piękny, interaktywny kalendarz adwentowy na swojej stronie WordPress
 * Version: 2.0.1
 * Author: Szymon Koscikiewicz
 * License: GPL v2 or later
 * Text Domain: advent-calendar
 */

// Zabezpieczenie przed bezpośrednim dostępem
if (!defined('ABSPATH')) {
    exit;
}

// Definicje stałych
define('ADVENT_CALENDAR_VERSION', '2.0.1');
define('ADVENT_CALENDAR_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ADVENT_CALENDAR_PLUGIN_PATH', plugin_dir_path(__FILE__));

// Najpierw ładujemy zależności
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-admin.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-frontend.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-ajax.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-export-import.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-statistics.php';
require_once ADVENT_CALENDAR_PLUGIN_PATH . 'includes/class-advent-calendar-styles.php';

// Główna klasa wtyczki
class Advent_Calendar_Plugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        load_plugin_textdomain('advent-calendar', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Rejestracja hooków
        if (is_admin()) {
            new Advent_Calendar_Admin();
        } else {
            new Advent_Calendar_Frontend();
        }
        
        new Advent_Calendar_Ajax();
        new Advent_Calendar_Export_Import();
        new Advent_Calendar_Statistics();
        new Advent_Calendar_Styles();
        
        // Rejestracja shortcode
        add_shortcode('advent_calendar', array($this, 'calendar_shortcode'));
    }
    
    public function activate() {
        // Tworzenie tabel w bazie danych
        Advent_Calendar::create_tables();
        
        // Domyślne ustawienia
        add_option('advent_calendar_default_settings', array(
            'columns' => 6,
            'rows' => 4,
            'animation' => 'fade',
            'theme' => 'christmas',
            'start_date' => date('Y-12-01'),
            'end_date' => date('Y-12-24'),
            'enable_stats' => true,
            'snow_effect' => true
        ));
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    public function calendar_shortcode($atts) {
        $frontend = new Advent_Calendar_Frontend();
        return $frontend->calendar_shortcode($atts);
    }
}

// Inicjalizacja wtyczki
Advent_Calendar_Plugin::get_instance();
?>
