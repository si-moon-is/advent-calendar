<?php
class Advent_Calendar_Debug {
    
    public static function log($message, $data = null) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            if ($data !== null) {
                error_log('Advent Calendar: ' . $message . ' - ' . print_r($data, true));
            } else {
                error_log('Advent Calendar: ' . $message);
            }
        }
    }
    
    public static function check_theme_images($theme) {
        $images_path = ADVENT_CALENDAR_PLUGIN_PATH . "templates/thems/{$theme}/images/";
        
        if (!is_dir($images_path)) {
            self::log("Theme images directory not found: {$images_path}");
            return false;
        }
        
        $images = glob($images_path . 'door-*.png');
        self::log("Found " . count($images) . " door images for theme {$theme}");
        
        return count($images) > 0;
    }
}
?>
