<?php
class Advent_Calendar_Export_Import {
    
    public function __construct() {
        add_action('admin_post_export_calendar', array($this, 'export_calendar'));
        add_action('admin_post_import_calendar', array($this, 'import_calendar'));
    }
    
    public function export_calendar() {
        // Sprawdź uprawnienia
        if (!current_user_can('manage_options')) {
            wp_die('Brak uprawnień');
        }
        
        // Sprawdź nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'advent_calendar_export')) {
            wp_die('Błąd bezpieczeństwa (nonce)');
        }
        
        $calendar_id = isset($_GET['calendar_id']) ? intval($_GET['calendar_id']) : 0;
        $calendar = Advent_Calendar::get_calendar($calendar_id);
        
        if (!$calendar) {
            wp_die('Kalendarz nie znaleziony');
        }
        
        $doors = Advent_Calendar::get_calendar_doors($calendar_id);
        $styles = Advent_Calendar_Styles::get_calendar_styles($calendar_id);
        
        $export_data = array(
            'calendar' => $calendar,
            'doors' => $doors,
            'styles' => $styles,
            'export_version' => ADVENT_CALENDAR_VERSION,
            'export_date' => current_time('mysql')
        );
        
        $filename = 'advent-calendar-' . $calendar_id . '-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=' . sanitize_file_name($filename));
        header('Pragma: no-cache');
        
        echo wp_json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    public function import_calendar() {
        // Sprawdź uprawnienia i nonce
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'import_calendar')) {
            wp_die('Brak uprawnień lub błąd bezpieczeństwa');
        }
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die('Błąd podczas przesyłania pliku');
        }
        
        // Sprawdź typ pliku
        $file_type = wp_check_filetype($_FILES['import_file']['name']);
        if ($file_type['ext'] !== 'json') {
            wp_die('Nieprawidłowy typ pliku. Oczekiwano pliku JSON.');
        }
        
        // Sprawdź rozmiar pliku (max 10MB)
        if ($_FILES['import_file']['size'] > 10 * 1024 * 1024) {
            wp_die('Plik jest zbyt duży. Maksymalny rozmiar to 10MB.');
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        
        // Sprawdź czy plik jest poprawnym JSON
        $import_data = json_decode($file_content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_die('Nieprawidłowy format pliku JSON: ' . json_last_error_msg());
        }
        
        if (!$import_data || !isset($import_data['calendar'])) {
            wp_die('Nieprawidłowy format pliku - brak danych kalendarza');
        }
        
        // Walidacja danych kalendarza
        if (!isset($import_data['calendar']->title) || empty($import_data['calendar']->title)) {
            wp_die('Nieprawidłowe dane kalendarza - brak tytułu');
        }
        
        $new_calendar_id = Advent_Calendar::save_calendar(array(
            'title' => sanitize_text_field($import_data['calendar']->title) . ' (Import)',
            'settings' => $this->sanitize_imported_settings($import_data['calendar']->settings)
        ));
        
        if (!$new_calendar_id) {
            wp_die('Błąd podczas tworzenia kalendarza');
        }
        
        // Import drzwi z walidacją
        if (isset($import_data['doors']) && is_array($import_data['doors'])) {
            foreach ($import_data['doors'] as $door) {
                if (!isset($door->door_number) || $door->door_number < 1 || $door->door_number > 24) {
                    continue; // Pomijaj nieprawidłowe drzwi
                }
                
                Advent_Calendar::save_door(array(
                    'calendar_id' => $new_calendar_id,
                    'door_number' => intval($door->door_number),
                    'title' => sanitize_text_field($door->title ?? ''),
                    'content' => wp_kses_post($door->content ?? ''),
                    'image_url' => esc_url_raw($door->image_url ?? ''),
                    'link_url' => esc_url_raw($door->link_url ?? ''),
                    'door_type' => in_array($door->door_type ?? 'modal', ['modal', 'link']) ? $door->door_type : 'modal',
                    'animation' => sanitize_text_field($door->animation ?? 'fade'),
                    'styles' => $this->sanitize_imported_styles($door->styles ?? ''),
                    'custom_css' => sanitize_textarea_field($door->custom_css ?? ''),
                    'unlock_date' => sanitize_text_field($door->unlock_date ?? date('Y-m-d'))
                ));
            }
        }
        
        // Import stylów z walidacją
        if (isset($import_data['styles']) && is_array($import_data['styles'])) {
            foreach ($import_data['styles'] as $style) {
                if (!isset($style->style_name) || empty($style->style_name)) {
                    continue;
                }
                
                Advent_Calendar_Styles::save_style(array(
                    'calendar_id' => $new_calendar_id,
                    'style_name' => sanitize_text_field($style->style_name),
                    'styles_data' => $this->sanitize_imported_styles($style->styles_data),
                    'custom_css' => sanitize_textarea_field($style->custom_css ?? '')
                ));
            }
        }
        
        wp_safe_redirect(admin_url('admin.php?page=advent-calendar&import=success'));
        exit;
    }
    
    private function sanitize_imported_settings($settings) {
        if (is_string($settings)) {
            $settings = json_decode($settings, true);
        }
        
        if (!is_array($settings)) {
            return array();
        }
        
        $allowed_settings = array(
            'columns' => 'int',
            'rows' => 'int',
            'start_date' => 'date',
            'end_date' => 'date',
            'theme' => 'text',
            'default_animation' => 'text',
            'snow_effect' => 'bool',
            'confetti_effect' => 'bool',
            'enable_stats' => 'bool'
        );
        
        $sanitized = array();
        foreach ($allowed_settings as $key => $type) {
            if (isset($settings[$key])) {
                switch ($type) {
                    case 'int':
                        $sanitized[$key] = intval($settings[$key]);
                        break;
                    case 'bool':
                        $sanitized[$key] = (bool)$settings[$key];
                        break;
                    case 'date':
                        $sanitized[$key] = sanitize_text_field($settings[$key]);
                        break;
                    default:
                        $sanitized[$key] = sanitize_text_field($settings[$key]);
                }
            }
        }
        
        return $sanitized;
    }
    
    private function sanitize_imported_styles($styles) {
        if (is_string($styles)) {
            $styles = json_decode($styles, true);
        }
        
        if (!is_array($styles)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($styles as $key => $value) {
            $sanitized[sanitize_text_field($key)] = sanitize_text_field($value);
        }
        
        return $sanitized;
    }
}
?>
