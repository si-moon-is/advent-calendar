<?php
class Advent_Calendar_Export_Import {
    
    public function __construct() {
        add_action('admin_post_export_calendar', array($this, 'export_calendar'));
        add_action('admin_post_import_calendar', array($this, 'import_calendar'));
    }
    
    public function export_calendar() {
        if (!current_user_can('manage_options')) {
            wp_die('Brak uprawnień');
        }
        
        $calendar_id = intval($_GET['calendar_id']);
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
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Pragma: no-cache');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
    
    public function import_calendar() {
        if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['_wpnonce'], 'import_calendar')) {
            wp_die('Brak uprawnień lub błąd bezpieczeństwa');
        }
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            wp_die('Błąd podczas przesyłania pliku');
        }
        
        $file_content = file_get_contents($_FILES['import_file']['tmp_name']);
        $import_data = json_decode($file_content, true);
        
        if (!$import_data || !isset($import_data['calendar'])) {
            wp_die('Nieprawidłowy format pliku');
        }
        
        $new_calendar_id = Advent_Calendar::save_calendar(array(
            'title' => $import_data['calendar']->title . ' (Import)',
            'settings' => json_decode($import_data['calendar']->settings, true)
        ));
        
        if (isset($import_data['doors'])) {
            foreach ($import_data['doors'] as $door) {
                Advent_Calendar::save_door(array(
                    'calendar_id' => $new_calendar_id,
                    'door_number' => $door->door_number,
                    'title' => $door->title,
                    'content' => $door->content,
                    'image_url' => $door->image_url,
                    'link_url' => $door->link_url,
                    'door_type' => $door->door_type,
                    'animation' => $door->animation,
                    'styles' => json_decode($door->styles, true),
                    'custom_css' => $door->custom_css,
                    'unlock_date' => $door->unlock_date
                ));
            }
        }
        
        if (isset($import_data['styles'])) {
            foreach ($import_data['styles'] as $style) {
                Advent_Calendar_Styles::save_style(array(
                    'calendar_id' => $new_calendar_id,
                    'style_name' => $style->style_name,
                    'styles_data' => $style->styles_data,
                    'custom_css' => $style->custom_css
                ));
            }
        }
        
        wp_redirect(admin_url('admin.php?page=advent-calendar&import=success'));
        exit;
    }
}
?>
