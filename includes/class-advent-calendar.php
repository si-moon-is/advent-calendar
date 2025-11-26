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
            opened_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY calendar_id (calendar_id),
            KEY door_id (door_id),
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
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}advent_calendars ORDER BY created_at DESC");
    }
    
    public static function get_calendar($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendars WHERE id = %d", $id
        ));
    }
    
    public static function get_calendar_doors($calendar_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_doors WHERE calendar_id = %d ORDER BY door_number", $calendar_id
        ));
    }
    
    public static function get_door($door_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_doors WHERE id = %d", $door_id
        ));
    }
    
    public static function save_calendar($data) {
        global $wpdb;
        
        $defaults = array(
            'title' => 'Nowy Kalendarz',
            'settings' => array(),
            'created_at' => current_time('mysql')
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if (isset($data['id'])) {
            $result = $wpdb->update(
                $wpdb->prefix . 'advent_calendars',
                array(
                    'title' => $data['title'],
                    'settings' => json_encode($data['settings']),
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $data['id']),
                array('%s', '%s', '%s'),
                array('%d')
            );
            return $result !== false ? $data['id'] : false;
        } else {
            $result = $wpdb->insert(
                $wpdb->prefix . 'advent_calendars',
                array(
                    'title' => $data['title'],
                    'settings' => json_encode($data['settings']),
                    'created_at' => $data['created_at']
                ),
                array('%s', '%s', '%s')
            );
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    public static function save_door($data) {
        global $wpdb;
        
        $defaults = array(
            'calendar_id' => 0,
            'door_number' => 1,
            'title' => '',
            'content' => '',
            'image_url' => '',
            'link_url' => '',
            'door_type' => 'modal',
            'animation' => 'fade',
            'styles' => array(),
            'custom_css' => '',
            'unlock_date' => date('Y-m-d'),
            'is_open' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if (isset($data['id'])) {
            $result = $wpdb->update(
                $wpdb->prefix . 'advent_calendar_doors',
                array(
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'image_url' => $data['image_url'],
                    'link_url' => $data['link_url'],
                    'door_type' => $data['door_type'],
                    'animation' => $data['animation'],
                    'styles' => json_encode($data['styles']),
                    'custom_css' => $data['custom_css'],
                    'unlock_date' => $data['unlock_date']
                ),
                array('id' => $data['id']),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%d')
            );
            return $result !== false ? $data['id'] : false;
        } else {
            $result = $wpdb->insert(
                $wpdb->prefix . 'advent_calendar_doors',
                array(
                    'calendar_id' => $data['calendar_id'],
                    'door_number' => $data['door_number'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'image_url' => $data['image_url'],
                    'link_url' => $data['link_url'],
                    'door_type' => $data['door_type'],
                    'animation' => $data['animation'],
                    'styles' => json_encode($data['styles']),
                    'custom_css' => $data['custom_css'],
                    'unlock_date' => $data['unlock_date'],
                    'is_open' => $data['is_open']
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d')
            );
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    public static function can_unlock_door($door_number, $calendar_settings) {
        $current_date = current_time('Y-m-d');
        $start_date = $calendar_settings['start_date'] ?? date('Y-12-01');
        
        $door_date = date('Y-m-d', strtotime($start_date . ' + ' . ($door_number - 1) . ' days'));
        
        return $current_date >= $door_date;
    }
    
    public static function log_door_open($door_id, $calendar_id) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}advent_calendar_doors 
             SET open_count = open_count + 1, is_open = 1 
             WHERE id = %d",
            $door_id
        ));
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'advent_calendar_stats',
            array(
                'calendar_id' => $calendar_id,
                'door_id' => $door_id,
                'user_ip' => self::get_user_ip(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'opened_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    private static function get_user_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }
    
    public static function delete_calendar($id) {
        global $wpdb;
        
        $result = $wpdb->delete($wpdb->prefix . 'advent_calendars', array('id' => $id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'advent_calendar_doors', array('calendar_id' => $id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'advent_calendar_stats', array('calendar_id' => $id), array('%d'));
        $wpdb->delete($wpdb->prefix . 'advent_calendar_styles', array('calendar_id' => $id), array('%d'));
        
        return $result !== false;
    }
    
    public static function get_door_by_calendar_and_number($calendar_id, $door_number) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_doors 
             WHERE calendar_id = %d AND door_number = %d",
            $calendar_id, $door_number
        ));
    }
}
?>
