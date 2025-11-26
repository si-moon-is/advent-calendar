<?php

class Advent_Calendar_Styles {
    
    public static function get_calendar_styles($calendar_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}advent_calendar_styles WHERE calendar_id = %d",
            $calendar_id
        ));
    }
    
    public static function save_style($data) {
        global $wpdb;
        
        $defaults = array(
            'calendar_id' => 0,
            'style_name' => 'Nowy Styl',
            'styles_data' => array(),
            'custom_css' => '',
            'is_default' => 0
        );
        
        $data = wp_parse_args($data, $defaults);
        
        if (isset($data['id'])) {
            $wpdb->update(
                $wpdb->prefix . 'advent_calendar_styles',
                array(
                    'style_name' => $data['style_name'],
                    'styles_data' => json_encode($data['styles_data']),
                    'custom_css' => $data['custom_css'],
                    'is_default' => $data['is_default']
                ),
                array('id' => $data['id']),
                array('%s', '%s', '%s', '%d'),
                array('%d')
            );
            return $data['id'];
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'advent_calendar_styles',
                array(
                    'calendar_id' => $data['calendar_id'],
                    'style_name' => $data['style_name'],
                    'styles_data' => json_encode($data['styles_data']),
                    'custom_css' => $data['custom_css'],
                    'is_default' => $data['is_default']
                ),
                array('%d', '%s', '%s', '%s', '%d')
            );
            return $wpdb->insert_id;
        }
    }
    
    public static function get_style_presets() {
        return array(
            'christmas' => array(
                'name' => 'Świąteczny',
                'colors' => array(
                    'primary' => '#c41e3a',
                    'secondary' => '#165b33',
                    'accent' => '#ffd700'
                )
            ),
            'winter' => array(
                'name' => 'Zimowy',
                'colors' => array(
                    'primary' => '#74b9ff',
                    'secondary' => '#0984e3',
                    'accent' => '#dfe6e9'
                )
            ),
            'elegant' => array(
                'name' => 'Elegancki',
                'colors' => array(
                    'primary' => '#2d3436',
                    'secondary' => '#636e72',
                    'accent' => '#fd79a8'
                )
            )
        );
    }
}
?>
