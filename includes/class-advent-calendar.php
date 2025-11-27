<?php
class Advent_Calendar {
    
    public static function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'advent_calendars';
        $doors_table = $wpdb->prefix . 'advent_calendar_doors';
        $stats_table = $wpdb->prefix . 'advent_calendar_stats';
        $styles_table = $wpdb->prefix . 'advent_calendar_styles';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            settings text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        $sql2 = "CREATE TABLE $doors_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calendar_id mediumint(9) NOT NULL,
            door_number smallint NOT NULL,
            title varchar(255),
            content longtext,
            image_url varchar(255),
            link_url varchar(255),
            door_type varchar(50) DEFAULT 'modal',
            animation varchar(50),
            styles text,
            custom_css text,
            unlock_date date NOT NULL,
            is_open tinyint(1) DEFAULT 0,
            open_count mediumint(9) DEFAULT 0,
            PRIMARY KEY (id),
            KEY calendar_id (calendar_id),
            KEY unlock_date (unlock_date)
        ) $charset_collate;";
        
        $sql3 = "CREATE TABLE $stats_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calendar_id mediumint(9) NOT NULL,
            door_id mediumint(9) NOT NULL,
            user_ip varchar(45),
            user_agent text,
            user_session varchar(64) NOT NULL,
            opened_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY calendar_id (calendar_id),
            KEY door_id (door_id),
            KEY user_session (user_session),
            KEY opened_at (opened_at)
        ) $charset_collate;";
        
        $sql4 = "CREATE TABLE $styles_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            calendar_id mediumint(9) NOT NULL,
            style_name varchar(100) NOT NULL,
            styles_data text NOT NULL,
            custom_css text,
            is_default tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY calendar_id (calendar_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql);
        dbDelta($sql2);
        dbDelta($sql3);
        dbDelta($sql4);
        
        return true;
    }

      
    public static function get_calendars() {
        global $wpdb;
        $table = $wpdb->prefix . 'advent_calendars';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC");
    }
    
    public static function get_calendar($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'advent_calendars';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_calendar_doors($calendar_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'advent_calendar_doors';
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE calendar_id = %d ORDER BY door_number", $calendar_id));
    }
    
    public static function get_door($door_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'advent_calendar_doors';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $door_id));
    }
    
    public static function save_calendar($data) {
        global $wpdb;
        
        error_log('ADVENT CALENDAR: Starting save_calendar with data: ' . print_r($data, true));
        
        $table = $wpdb->prefix . 'advent_calendars';
        
        // Check if table exists
        $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) == $table;
        if (!$table_exists) {
            error_log('ADVENT CALENDAR ERROR: Table does not exist: ' . $table);
            return false;
        }
        
        // Validate required fields
        if (empty($data['title'])) {
            error_log('ADVENT CALENDAR ERROR: Title is empty');
            return false;
        }
        
        // Prepare settings with validation
        $settings = isset($data['settings']) ? $data['settings'] : array();
        
        // Sanitize settings
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
        
        $sanitized_settings = array();
        foreach ($allowed_settings as $key => $type) {
            if (isset($settings[$key])) {
                switch ($type) {
                    case 'int':
                        $sanitized_settings[$key] = intval($settings[$key]);
                        break;
                    case 'bool':
                        $sanitized_settings[$key] = (bool)$settings[$key];
                        break;
                    case 'date':
                        $sanitized_settings[$key] = sanitize_text_field($settings[$key]);
                        break;
                    default:
                        $sanitized_settings[$key] = sanitize_text_field($settings[$key]);
                }
            }
        }
        
        $settings_json = json_encode($sanitized_settings);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('ADVENT CALENDAR ERROR: JSON encode failed: ' . json_last_error_msg());
            return false;
        }
        
        // Prepare data for database with validation
        $db_data = array(
            'title' => sanitize_text_field($data['title']),
            'settings' => $settings_json,
            'updated_at' => current_time('mysql')
        );
        
        $format = array('%s', '%s', '%s');
        
        if (isset($data['id']) && !empty($data['id'])) {
            // UPDATE existing calendar
            $calendar_id = intval($data['id']);
            error_log('ADVENT CALENDAR: Updating calendar ID: ' . $calendar_id);
            
            $where = array('id' => $calendar_id);
            $where_format = array('%d');
            
            $result = $wpdb->update($table, $db_data, $where, $format, $where_format);
            
            error_log('ADVENT CALENDAR: Update result: ' . var_export($result, true));
            error_log('ADVENT CALENDAR: Last error: ' . $wpdb->last_error);
            error_log('ADVENT CALENDAR: Last query: ' . $wpdb->last_query);
            
            if ($result === false) {
                error_log('ADVENT CALENDAR ERROR: Update failed completely');
                return false;
            }
            
            return $calendar_id;
        } else {
            // INSERT new calendar
            error_log('ADVENT CALENDAR: Inserting new calendar');
            $db_data['created_at'] = current_time('mysql');
            $format[] = '%s';
            
            $result = $wpdb->insert($table, $db_data, $format);
            
            error_log('ADVENT CALENDAR: Insert result: ' . var_export($result, true));
            error_log('ADVENT CALENDAR: Insert ID: ' . $wpdb->insert_id);
            error_log('ADVENT CALENDAR: Last error: ' . $wpdb->last_error);
            error_log('ADVENT CALENDAR: Last query: ' . $wpdb->last_query);
            
            if ($result === false) {
                error_log('ADVENT CALENDAR ERROR: Insert failed');
                return false;
            }
            
            return $wpdb->insert_id;
        }
    }

    public static function save_door($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'advent_calendar_doors';
        
        // Validate required fields
        if (empty($data['calendar_id']) || empty($data['door_number'])) {
            return false;
        }
        
        // Sanitize and validate input
        $door_data = array(
            'calendar_id' => intval($data['calendar_id']),
            'door_number' => intval($data['door_number']),
            'title' => sanitize_text_field($data['title'] ?? ''),
            'content' => wp_kses_post($data['content'] ?? ''),
            'image_url' => esc_url_raw($data['image_url'] ?? ''),
            'link_url' => esc_url_raw($data['link_url'] ?? ''),
            'door_type' => in_array($data['door_type'] ?? 'modal', ['modal', 'link']) ? $data['door_type'] : 'modal',
            'animation' => sanitize_text_field($data['animation'] ?? 'fade'),
            'styles' => json_encode($data['styles'] ?? array()),
            'custom_css' => sanitize_textarea_field($data['custom_css'] ?? ''),
            'unlock_date' => sanitize_text_field($data['unlock_date'] ?? date('Y-m-d'))
        );
        
        $format = array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s');
        
        if (isset($data['id']) && !empty($data['id'])) {
            $result = $wpdb->update(
                $table,
                $door_data,
                array('id' => intval($data['id'])),
                $format,
                array('%d')
            );
            
            return $result !== false ? intval($data['id']) : false;
        } else {
            $result = $wpdb->insert($table, $door_data, $format);
            return $result ? $wpdb->insert_id : false;
        }
    }

    public static function can_unlock_door($door_number, $calendar_settings) {
        $current_date = current_time('Y-m-d');
        $start_date = $calendar_settings['start_date'] ?? date('Y-12-01');
        
        // Walidacja daty rozpoczęcia
        if (!strtotime($start_date)) {
            $start_date = date('Y-12-01');
        }
        
        // Oblicz datę odblokowania dla konkretnych drzwi
        $door_date = date('Y-m-d', strtotime($start_date . ' + ' . ($door_number - 1) . ' days'));
        
        // Sprawdź czy obecna data jest równa lub późniejsza od daty odblokowania
        return $current_date >= $door_date;
    }
    
    public static function has_user_opened_door_with_session($door_id, $user_session) {
        global $wpdb;
        
        $opened = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}advent_calendar_stats 
             WHERE door_id = %d AND user_session = %s",
            $door_id, $user_session
        ));
        
        return $opened > 0;
    }

    /**
     * Zapisuje otwarcie drzwi z konkretną sesją
     */
    public static function log_door_open_with_session($door_id, $calendar_id, $user_session) {
        global $wpdb;
        
        // Zwiększ globalny licznik
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}advent_calendar_doors 
             SET open_count = open_count + 1 
             WHERE id = %d",
            $door_id
        ));
        
        // Zapisz otwarcie dla tego użytkownika
        $result = $wpdb->insert(
            $wpdb->prefix . 'advent_calendar_stats',
            array(
                'calendar_id' => $calendar_id,
                'door_id' => $door_id,
                'user_ip' => self::get_user_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'user_session' => $user_session,
                'opened_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    private static function get_user_ip() {
        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
        }
        
        // Validate IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $ip = '0.0.0.0';
        }
        
        return $ip;
    }

    private static function get_user_session() {
        $cookie_name = 'advent_calendar_user_session';
        
        // Sprawdź czy mamy user_session z AJAX
        if (isset($_POST['user_session']) && !empty($_POST['user_session'])) {
            $session = sanitize_text_field(wp_unslash($_POST['user_session']));
            // Zapisz też w cookie na przyszłość
            setcookie($cookie_name, $session, time() + (365 * DAY_IN_SECONDS), '/', '', is_ssl(), true);
            return $session;
        }
        
        // Sprawdź cookie
        if (isset($_COOKIE[$cookie_name]) && !empty($_COOKIE[$cookie_name])) {
            return sanitize_text_field(wp_unslash($_COOKIE[$cookie_name]));
        }
        
        // Sprawdź localStorage przez JavaScript (dodamy później)
        if (isset($_POST['local_storage_session']) && !empty($_POST['local_storage_session'])) {
            $session = sanitize_text_field(wp_unslash($_POST['local_storage_session']));
            setcookie($cookie_name, $session, time() + (365 * DAY_IN_SECONDS), '/', '', is_ssl(), true);
            return $session;
        }
        
        // Utwórz nową sesję
        $new_session = 'user_' . uniqid() . '_' . time();
        setcookie($cookie_name, $new_session, time() + (365 * DAY_IN_SECONDS), '/', '', is_ssl(), true);
        return $new_session;
    }
}
?>
