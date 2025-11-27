<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pobierz calendar_id z atrybutów shortcode
$calendar_id = intval($atts['id']);
$calendar = Advent_Calendar::get_calendar($calendar_id);
$doors = Advent_Calendar::get_calendar_doors($calendar_id);

if (!$calendar) {
    return 'Kalendarz nie znaleziony';
}

$settings = json_decode($calendar->settings, true);
$columns = $settings['columns'] ?? 6;
$rows = $settings['rows'] ?? 4;
$theme = $settings['theme'] ?? 'christmas';
$total_doors = $columns * $rows;
?>

<div class="advent-calendar advent-theme-<?php echo esc_attr($theme); ?>" 
     data-calendar-id="<?php echo $calendar_id; ?>" 
     data-settings="<?php echo esc_attr(json_encode($settings)); ?>"
     style="display: grid; grid-template-columns: repeat(<?php echo $columns; ?>, 1fr); gap: 15px; margin: 30px 0; padding: 30px;">
    
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
    ?>
        
        <div class="advent-calendar-door door <?php echo $door_class; ?>" 
             data-door-id="<?php echo $door ? $door->id : '0'; ?>"
             data-calendar-id="<?php echo $calendar_id; ?>"
             data-door-number="<?php echo $i; ?>">
    
            <span class="door-number"><?php echo $i; ?></span>
    
            <?php if ($door && $door->image_url): ?>
                <!-- Obrazek z bazy -->
                <div class="door-image-container <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                    <img src="<?php echo esc_url($door->image_url); ?>" alt="Door <?php echo $i; ?>" class="door-main-image">
                    <?php if (!$user_has_opened): ?>
                        <div class="door-overlay"></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Domyślny obrazek świąteczny -->
                <div class="door-default-content <?php echo $user_has_opened ? 'opened' : 'closed'; ?> 
                     <?php echo $settings['theme'] === 'christmas' ? 'christmas-default' : ''; ?>">
                     
                    <?php if ($user_has_opened): ?>
                        <span class="door-icon">🎁</span>
                    <?php else: ?>
                        <!-- Domyślny obrazek dla zamkniętych drzwi -->
                        <div class="default-christmas-image door-<?php echo $i; ?>"></div>
                        <span class="door-number-default"><?php echo $i; ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>

<style>
.advent-calendar {
    border-radius: 15px;
    position: relative;
    overflow: hidden;
}

.advent-calendar.theme-christmas {
    background: linear-gradient(135deg, #c41e3a 0%, #165b33 100%);
    border: 3px solid #ffd700;
}

.advent-calendar.theme-winter {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    border: 3px solid #dfe6e9;
}

.advent-calendar.theme-elegant {
    background: linear-gradient(135deg, #2d3436 0%, #636e72 100%);
    border: 3px solid #fd79a8;
}

.advent-calendar-door {
    position: relative;
    aspect-ratio: 1;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.5em;
    transition: all 0.4s ease;
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    overflow: hidden;
    background: rgba(255,255,255,0.2);
}

.advent-calendar-door.available {
    background: #c41e3a;
    cursor: pointer;
}

.advent-calendar-door.available:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 12px 20px rgba(0,0,0,0.2);
}

.advent-calendar-door.locked {
    background: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

.advent-calendar-door.open {
    background: #28a745;
    cursor: default;
}

.advent-calendar-door .door-number {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: 0.9em;
    background: rgba(0,0,0,0.3);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

/* Kontener obrazka */
.door-image-container {
    position: relative;
    width: 100%;
    height: 100%;
    border-radius: 8px;
    overflow: hidden;
}

.door-main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.3s ease;
}

/* Przyciemnienie dla zamkniętych drzwi */
.door-image-container.closed .door-main-image {
    filter: brightness(0.3) blur(1px);
}

.door-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
}

.door-image-container.opened .door-main-image {
    filter: brightness(1);
}

/* Domyślne obrazki świąteczne */
.default-christmas-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 0.8;
}

/* Obrazki z Twojego katalogu */
.door-1 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-1.jpg'); }
.door-2 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-2.jpg'); }
.door-3 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-3.jpg'); }
.door-4 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-4.jpg'); }
.door-5 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-5.jpg'); }
.door-6 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-6.jpg'); }
.door-7 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-7.jpg'); }
.door-8 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-8.jpg'); }
.door-9 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-9.jpg'); }
.door-10 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-10.jpg'); }
.door-11 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-11.jpg'); }
.door-12 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-12.jpg'); }
.door-13 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-13.jpg'); }
.door-14 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-14.jpg'); }
.door-15 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-15.jpg'); }
.door-16 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-16.jpg'); }
.door-17 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-17.jpg'); }
.door-18 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-18.jpg'); }
.door-19 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-19.jpg'); }
.door-20 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-20.jpg'); }
.door-21 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-21.jpg'); }
.door-22 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-22.jpg'); }
.door-23 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-23.jpg'); }
.door-24 { background-image: url('<?php echo ADVENT_CALENDAR_PLUGIN_URL; ?>assets/images/doors/door-24.jpg'); }

.door-number-default {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 1.5em;
    font-weight: bold;
    color: gold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    z-index: 2;
}

/* Domyślna zawartość bez obrazka */
.door-default-content {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.door-default-content.closed {
    background: linear-gradient(145deg, #d4af37, #ffd700);
}

.door-default-content.opened {
    background: linear-gradient(145deg, #90ee90, #32cd32);
}

.door-icon {
    font-size: 2em;
    z-index: 2;
}

.advent-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 99999;
    align-items: center;
    justify-content: center;
}

.advent-modal.active {
    display: flex;
}

.advent-modal-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    position: relative;
}

.advent-modal-close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.advent-modal-close:hover {
    color: #000;
}

.door-image {
    text-align: center;
    margin-bottom: 20px;
}

.door-image img {
    max-width: 100%;
    height: auto;
    border-radius: 10px;
}

.door-title {
    font-size: 2em;
    margin-bottom: 15px;
    color: #c41e3a;
    text-align: center;
}

.door-content-text {
    font-size: 1.1em;
    line-height: 1.6;
    color: #333;
}

@media (max-width: 768px) {
    .advent-calendar {
        gap: 10px;
        padding: 15px;
        grid-template-columns: repeat(3, 1fr) !important;
    }
    
    .advent-calendar-door {
        font-size: 1.2em;
    }
    
    .advent-modal-content {
        padding: 25px;
        margin: 20px;
    }
}

@media (max-width: 480px) {
    .advent-calendar {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}
</style>
