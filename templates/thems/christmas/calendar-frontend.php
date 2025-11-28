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

<div class="advent-calendar advent-theme-christmas" 
     data-calendar-id="<?php echo esc_attr($calendar_id); ?>" 
     data-columns="<?php echo esc_attr($columns); ?>"
     data-theme="christmas">
    
    <div class="theme-christmas-header">
        <div class="snowflakes">❄ ❄ ❄</div>
        <h3 class="calendar-title">Kalendarz Adwentowy</h3>
        <div class="snowflakes">❅ ❅ ❅</div>
    </div>
    
    <div class="doors-grid christmas-grid">
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
            
            <div class="advent-calendar-door christmas-door <?php echo esc_attr($door_class); ?>" 
                 data-door-id="<?php echo esc_attr($door_id); ?>"
                 data-calendar-id="<?php echo esc_attr($calendar_id); ?>"
                 data-door-number="<?php echo intval($i); ?>">
        
                <span class="door-number"><?php echo intval($i); ?></span>
        
                <?php if ($door && !empty($door->image_url)): ?>
                    <div class="door-image-container <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                        <img src="<?php echo esc_url($door->image_url); ?>" alt="Door <?php echo intval($i); ?>" class="door-main-image">
                        <?php if (!$user_has_opened): ?>
                            <div class="door-overlay">
                                <span class="snow-icon">❄</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="door-default-content <?php echo $user_has_opened ? 'opened' : 'closed'; ?>">
                        <?php if ($user_has_opened): ?>
                            <span class="door-icon">🎁</span>
                        <?php else: ?>
                            <div class="christmas-pattern door-<?php echo intval($i); ?>"></div>
                            <span class="door-number-default"><?php echo intval($i); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>

<style>
/* Christmas Theme Styles */
.advent-theme-christmas {
    background: linear-gradient(135deg, #c41e3a 0%, #165b33 100%);
    border: 3px solid #ffd700;
    border-radius: 15px;
    padding: 30px;
    margin: 30px 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(196, 30, 58, 0.3);
}

.theme-christmas-header {
    text-align: center;
    margin-bottom: 25px;
    color: white;
}

.theme-christmas-header .calendar-title {
    color: #ffd700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    margin: 10px 0;
    font-size: 1.8em;
}

.snowflakes {
    color: white;
    font-size: 1.2em;
    letter-spacing: 10px;
}

.christmas-grid {
    display: grid;
    grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr);
    gap: 12px;
}

.christmas-door {
    aspect-ratio: 1;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.4em;
    transition: all 0.4s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    position: relative;
    border: 2px solid #b8860b;
}

.christmas-door.available {
    background: linear-gradient(145deg, #d4af37, #ffd700);
}

.christmas-door.available:hover {
    transform: translateY(-5px) scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    background: linear-gradient(145deg, #ffd700, #ffec8b);
}

.christmas-door.locked {
    background: linear-gradient(145deg, #8b4513, #a0522d);
    cursor: not-allowed;
    opacity: 0.7;
}

.christmas-door.open {
    background: linear-gradient(145deg, #90ee90, #32cd32);
    border-color: #228b22;
    cursor: default;
}

.christmas-door .door-number {
    position: absolute;
    top: 8px;
    left: 8px;
    font-size: 0.9em;
    background: rgba(0, 0, 0, 0.7);
    color: #ffd700;
    width: 25px;
    height: 25px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border: 1px solid #ffd700;
}

.christmas-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 20%, #ffd700 2px, transparent 2px),
        radial-gradient(circle at 80% 80%, #c41e3a 2px, transparent 2px);
    background-size: 20px 20px;
    opacity: 0.3;
}

.snow-icon {
    font-size: 1.5em;
    color: white;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

/* Christmas colors for each door */
.christmas-door:nth-child(odd).available {
    background: linear-gradient(135deg, #c41e3a, #8b0000);
}

.christmas-door:nth-child(even).available {
    background: linear-gradient(135deg, #165b33, #0d3d21);
}

@media (max-width: 768px) {
    .advent-theme-christmas {
        padding: 20px;
    }
    
    .christmas-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .theme-christmas-header .calendar-title {
        font-size: 1.4em;
    }
}

@media (max-width: 480px) {
    .christmas-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
