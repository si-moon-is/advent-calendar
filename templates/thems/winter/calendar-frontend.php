<?php
if (!defined('ABSPATH')) {
    exit;
}

$calendar_id = isset($atts['id']) ? intval($atts['id']) : 0;
$calendar = $calendar_id ? Advent_Calendar::get_calendar($calendar_id) : null;

if (!$calendar) {
    return '<p>Kalendarz nie znaleziony</p>';
}

$doors = Advent_Calendar::get_calendar_doors($calendar_id);
$settings = json_decode($calendar->settings, true);
$columns = isset($settings['columns']) ? intval($settings['columns']) : 6;
$rows = isset($settings['rows']) ? intval($settings['rows']) : 4;
$total_doors = $columns * $rows;
?>

<div class="advent-calendar advent-theme-winter" 
     data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     data-theme="winter">
    
    <div class="theme-winter-header">
        <h3 class="calendar-title">Zimowy Kalendarz Adwentowy</h3>
        <div class="snow-effect">❄ ❅ ❆</div>
    </div>
    
    <div class="doors-grid winter-grid">
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
        ?>
            
            <div class="advent-calendar-door winter-door <?php echo esc_attr($door_class); ?>" 
                 data-door-id="<?php echo esc_attr($door_id); ?>"
                 data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
                 data-door-number="<?php echo intval($i); ?>">
        
                <span class="door-number"><?php echo intval($i); ?></span>
        
                <?php if ($door && !empty($door->image_url)): ?>
                    <div class="door-image-container <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                        <img src="<?php echo esc_url($door->image_url); ?>" alt="Door <?php echo intval($i); ?>" class="door-main-image">
                        <?php if (!$user_has_opened): ?>
                            <div class="door-overlay">
                                <span class="ice-icon">🧊</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="door-default-content <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                        <?php if ($user_has_opened): ?>
                            <span class="door-icon">⛸️</span>
                        <?php else: ?>
                            <div class="winter-pattern door-<?php echo intval($i); ?>"></div>
                            <span class="door-number-default"><?php echo intval($i); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<style>
/* Winter Theme Styles */
.advent-theme-winter {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    border: 3px solid #dfe6e9;
    border-radius: 15px;
    padding: 30px;
    margin: 30px 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(116, 185, 255, 0.3);
}

.theme-winter-header {
    text-align: center;
    margin-bottom: 25px;
    color: white;
}

.theme-winter-header .calendar-title {
    color: #dfe6e9;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    margin: 10px 0;
    font-size: 1.8em;
}

.snow-effect {
    color: white;
    font-size: 1.2em;
    letter-spacing: 8px;
}

.winter-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);
    gap: 12px;
}

.winter-door {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #2d3436;
    font-weight: bold;
    font-size: 1.4em;
    transition: all 0.4s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    position: relative;
    border: 2px solid #636e72;
}

.winter-door.available {
    background: linear-gradient(145deg, #e6f3ff, #b3d9ff);
}

.winter-door.available:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    background: linear-gradient(145deg, #b3d9ff, #80bfff);
}

.winter-door.locked {
    background: linear-gradient(145deg, #b2bec3, #636e72);
    cursor: not-allowed;
    opacity: 0.7;
}

.winter-door.open {
    background: linear-gradient(145deg, #a5e6a5, #66bb6a);
    border-color: #388e3c;
    cursor: default;
}

.winter-door .door-number {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: 0.9em;
    background: rgba(45, 52, 54, 0.8);
    color: #dfe6e9;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border: 1px solid #b2bec3;
}

.winter-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        linear-gradient(45deg, transparent 49%, #dfe6e9 49%, #dfe6e9 51%, transparent 51%),
        linear-gradient(-45deg, transparent 49%, #dfe6e9 49%, #dfe6e9 51%, transparent 51%);
    background-size: 20px 20px;
    opacity: 0.2;
}

.ice-icon {
    font-size: 1.5em;
    color: #dfe6e9;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
}

/* Winter colors for each door */
.winter-door:nth-child(odd).available {
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: white;
}

.winter-door:nth-child(even).available {
    background: linear-gradient(135deg, #dfe6e9, #b2bec3);
    color: #2d3436;
}

@media (max-width: 768px) {
    .advent-theme-winter {
        padding: 20px;
    }
    
    .winter-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .theme-winter-header .calendar-title {
        font-size: 1.4em;
    }
}

@media (max-width: 480px) {
    .winter-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
