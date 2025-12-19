<?php
if (!defined('ABSPATH')) {
    exit;
}

// Te zmienne są teraz przekazywane z calendar_shortcode()
// $calendar_id, $doors, $settings, $columns, $rows, $total_doors, $theme

// Poprawna ścieżka do obrazków motywu
$theme_images_path = ADVENT_CALENDAR_PLUGIN_URL . 'templates/thems/christmas/images/';

// Sprawdź czy ścieżka istnieje, jeśli nie - użyj domyślnej
if (!file_exists(ADVENT_CALENDAR_PLUGIN_PATH . 'templates/thems/christmas/images/door-1.png')) {
    // Alternatywna ścieżka lub użyj domyślnych gradientów
    $use_default_gradients = true;
} else {
    $use_default_gradients = false;
}
?>

<div class="advent-calendar advent-theme-christmas" 
     data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     data-theme="christmas">
    
    <div class="theme-christmas-header">
        <div class="snowflakes">❄ ❄ ❄</div>
        <h3 class="calendar-title">Kalendarz Adwentowy</h3>
        <div class="snowflakes">❅ ❅ ❅</div>
    </div>
    
    <div class="doors-grid christmas-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);">
        <?php 
        for ($i = 1; $i <= $total_doors; $i++): 
            $door = null;
            foreach ($doors as $d) {
                if ($d->door_number == $i) {
                    $door = $d;
                    break;
                }
            }
            
            $can_open = $door ? Advent_Calendar::can_unlock_door($door->door_number, $settings) : false;
            $user_has_opened = $door ? Advent_Calendar::has_user_opened_door($door->id) : false;
            $door_class = $user_has_opened ? 'open' : ($can_open ? 'available' : 'locked');
            $door_id = $door ? intval($door->id) : 0;
            
            // Użyj obrazka z motywu tylko jeśli istnieje, w przeciwnym razie użyj gradientu
            if (!$use_default_gradients) {
                $default_image_url = $theme_images_path . 'door-' . $i . '.png';
            }
        ?>
            
            <div class="advent-calendar-door christmas-door <?php echo esc_attr($door_class); ?>" 
                 data-door-id="<?php echo esc_attr($door_id); ?>"
                 data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
                 data-door-number="<?php echo intval($i); ?>"
                 <?php if (!$user_has_opened && !$use_default_gradients && !empty($default_image_url)): ?>
                    style="background-image: url('<?php echo esc_url($default_image_url); ?>'); background-size: cover; background-position: center;"
                 <?php endif; ?>>

                <span class="door-number"><?php echo intval($i); ?></span>

                <?php if ($user_has_opened && $door && !empty($door->image_url)): ?>
                    <!-- PO otwarciu - obrazek użytkownika jako tło -->
                    <div class="door-content" style="background-image: url('<?php echo esc_url($door->image_url); ?>');"></div>
                    <div class="door-image-overlay"></div>
                <?php elseif ($user_has_opened): ?>
                    <!-- PO otwarciu bez obrazka użytkownika -->
                    <div class="door-default-content opened">
                        <span class="door-icon">🎁</span>
                    </div>
                <?php elseif (!$use_default_gradients && !empty($default_image_url)): ?>
                    <!-- PRZED otwarciem - obrazek motywu jako tło z overlay -->
                    <div class="door-overlay">
                        <span class="snow-icon">❄</span>
                    </div>
                <?php else: ?>
                    <!-- PRZED otwarciem - gradient zamiast obrazka -->
                    <div class="door-default-content closed">
                        <span class="door-number-default"><?php echo intval($i); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<style>
/* Christmas Theme Styles - pozostałe style bez zmian */
/* ... */
</style>
