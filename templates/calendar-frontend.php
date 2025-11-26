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
         data-calendar-id="<?php echo $calendar_id; ?>">
        
        <span class="door-number"><?php echo $i; ?></span>
        
        <?php if ($user_has_opened && $door && $door->image_url): ?>
            <div class="door-content">
                <img src="<?php echo esc_url($door->image_url); ?>" alt="Door <?php echo $i; ?>">
            </div>
        <?php elseif ($user_has_opened && $door): ?>
            <div class="door-content">
                <div class="door-default-content">
                    <span class="door-icon">🎁</span>
                </div>
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
}

.advent-calendar-door .door-content {
    padding: 10px;
    text-align: center;
    width: 100%;
}

.advent-calendar-door .door-content img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
}

.door-default-content .door-icon {
    font-size: 2em;
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
